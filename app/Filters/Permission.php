<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Services;

/**
 * Permission Filter (single-file)
 * --------------------------------
 * Validasi akses berdasarkan relasi:
 *   users -> usersgroupprogram (group + program) -> privilege (page + action)
 *
 * Asumsi tabel (sesuai SQL Anda):
 *   - `users` (users_id)
 *   - `group` (group_id)                 ← nama tabel ini reserved word MySQL: gunakan backtick saat query manual
 *   - `program` (program_id)
 *   - `usersgroupprogram` (users_id, group_id, program_id) UNIQUE (users_id, group_id, program_id)
 *   - `page` (page_id, kolom identifikasi halaman; lihat $pageCandidateColumns)
 *   - `action` (action_id, name: index|view|create|update|delete|...)
 *   - `privilege` (group_id, page_id, action_id)
 *
 * Cara pakai:
 * 1) Simpan file ini sebagai: app/Filters/Permission.php
 * 2) Daftarkan alias di app/Config/Filters.php:
 *      public array $aliases = ['permission' => \App\Filters\Permission::class,];
 * 3) Pastikan session berisi users_id (hasil login). Untuk multi-program,
 *    bisa set program_id aktif di session; kalau tidak ada, filter ambil program pertama user.
 * 4) AutoRoutes/Routes Anda sudah menambahkan ['filter' => 'permission'] → siap dipakai.
 */
class Permission implements FilterInterface
{
    /**
     * Kolom kandidat untuk mencari `page` berdasarkan route.
     * Urutan dicoba dari atas ke bawah. Sesuaikan dengan struktur tabel `page` Anda.
     * Jika `page` Anda memakai kolom 'controller' (isi nama controller), biarkan urutan default ini.
     * Jika pakai 'url' atau 'slug', tinggal pindahkan paling atas.
     */
    private array $pageCandidateColumns = [
        'controller', // contoh: 'Users', 'Dashboard'
        'url',        // contoh: 'admin/users'
        'slug',       // contoh: 'users'
        'name',       // contoh: 'Users'
        'path',       // contoh: '/users/index'
    ];

    /** Pemetaan method controller → nama action di tabel `action` */
    private array $methodToActionMap = [
        'index' => 'index',
        'list' => 'index',
        'show' => 'view',
        'view' => 'view',
        'create' => 'create',
        'store' => 'create',
        'edit' => 'update',
        'update' => 'update',
        'delete' => 'delete',
        'destroy' => 'delete',
        'download' => 'download',
        'upload'  => 'upload',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        $session  = session();
        $response = Services::response();
        $db       = \Config\Database::connect();
        $router   = service('router');

        // 1) Wajib login
        $userId = (int) ($session->get('users_id') ?? 0);
        if ($userId <= 0) {
            return redirect()->to('/login'); // ubah sesuai rute login Anda
        }

        // 2) Info route saat ini
        $controllerFQN = $router->controllerName();                 // ex: App\Controllers\Users
        $methodName    = strtolower($router->methodName() ?? 'index');
        $controller    = $this->basename($controllerFQN);           // ex: 'Users'
        $uriPath       = trim(service('uri')->getPath(), '/');      // ex: 'admin/users'

        // 3) Tentukan nama action final (cocokkan ke tabel `action`.`name`)
        $actionName = $this->methodToActionMap[$methodName] ?? $methodName;

        // 4) Ambil action_id
        $actionId = $this->findActionId($db, $actionName);
        if ($actionId === null) {
            return $this->deny($response, "Aksi '$actionName' belum terdaftar di tabel `action`.");
        }

        // 5) Cari page_id (berdasarkan controller/uri) via daftar kolom kandidat
        $pageId = $this->findPageId($db, $controller, $uriPath);
        if ($pageId === null) {
            return $this->deny($response, "Halaman untuk controller '$controller' / uri '$uriPath' belum terdaftar di tabel `page`.");
        }

        // 6) Tentukan program aktif (dari session), atau ambil program pertama milik user
        $programId = $session->get('program_id');
        
        if (empty($programId)) {
            $programId = $this->findAnyProgramIdForUser($db, $userId);
            // var_dump($programId);exit;
            if ($programId === null) {
                return $this->deny($response, "User belum terdaftar pada program manapun.");
            }
            $session->set('program_id', $programId);
        }

        // 7) Ambil semua group_id user pada program tsb
        $groupIds = $this->findGroupIdsForUserProgram($db, $userId, (int)$programId);
        if (empty($groupIds)) {
            return $this->deny($response, "User tidak memiliki group pada program yang aktif.");
        }

        // 8) Cek privilege: group ∈ groupIds, page_id, action_id
        $builder = $db->table('`privilege`');
        $builder->select('COUNT(1) AS cnt')
                ->whereIn('group_id', $groupIds)
                ->where('page_id', (int)$pageId)
                ->where('action_id', (int)$actionId);

        $cnt = (int) ($builder->get()->getRow('cnt') ?? 0);
        if ($cnt <= 0) {
            return $this->deny($response, "Akses ditolak. Group Anda tidak memiliki privilege untuk $controller::$methodName.");
        }

        // Lolos
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak digunakan
    }

    // ===== Helper =====
    private function basename($fqn): string
    {
        // Jika yang diterima bukan string, coba ambil nama class jika objek
        if (is_object($fqn)) {
            $fqn = get_class($fqn);  // Ambil nama class jika objek
        }

        // Jika fqn tetap bukan string (misal NULL), kembalikan string kosong
        if (!is_string($fqn) || empty($fqn)) {
            return '';
        }

        // Ambil bagian terakhir setelah tanda '\\' (untuk nama class)
        $pos = strrpos($fqn, '\\');
        return $pos === false ? $fqn : substr($fqn, $pos + 1);
    }


    private function findActionId($db, string $actionName): ?int
    {
        $builder = $db->table('`action`');
        $builder->select('action_id')
                ->where('LOWER(name)', strtolower($actionName))
                ->limit(1);
        $row = $builder->get()->getFirstRow();
        return ($row && isset($row->action_id)) ? (int)$row->action_id : null;
    }

    /** Coba cari page_id pada kolom tertentu (abaikan jika kolom tidak ada) */
    private function tryFindPageIdByColumn($db, string $column, string $needle): ?int
    {
        try {
            $builder = $db->table('`page`');
            $builder->select('page_id')
                    ->where("LOWER($column)", strtolower($needle))
                    ->limit(1);
            $row = $builder->get()->getFirstRow();
            if ($row && isset($row->page_id)) {
                return (int)$row->page_id;
            }
        } catch (\Throwable $e) {
            // Kolom tidak ada → skip & lanjut kandidat lain
        }
        return null;
    }

    private function findPageId($db, string $controller, string $uriPath): ?int
    {
        // Jika controller kosong (seperti pada Closure), langsung lanjut ke pengecekan URI
        if ($controller === '') {
            return $this->tryFindPageIdByColumn($db, 'path', $uriPath);
        }

        // 1) coba match berdasarkan controller (jika ada)
        foreach ($this->pageCandidateColumns as $col) {
            $id = $this->tryFindPageIdByColumn($db, $col, $controller);
            if ($id !== null) return $id;
        }

        // 2) fallback: coba match berdasarkan uri path
        foreach ($this->pageCandidateColumns as $col) {
            $id = $this->tryFindPageIdByColumn($db, $col, $uriPath);
            if ($id !== null) return $id;
        }

        return null;
    }

    private function findAnyProgramIdForUser($db, int $userId): ?int
    {
        $builder = $db->table('`usersgroupprogram`');
        $builder->select('program_id')
                ->where('users_id', $userId)
                ->limit(1);
        $row = $builder->get()->getFirstRow();
        // var_dump((int)$row->program_id);exit;
        return ($row && isset($row->program_id)) ? (int)$row->program_id : null;
    }

    /** Ambil semua group_id untuk user pada program tertentu */
    private function findGroupIdsForUserProgram($db, int $userId, int $programId): array
    {
        $builder = $db->table('`usersgroupprogram`');
        $builder->select('group_id')
                ->where('users_id', $userId)
                ->where('program_id', $programId);
        $rows = $builder->get()->getResult();
        $ids = [];
        foreach ($rows as $r) {
            if (isset($r->group_id)) $ids[] = (int)$r->group_id;
        }
        return array_values(array_unique($ids));
    }

    private function deny(ResponseInterface $response, string $message)
    {
        $req = service('request');
        if ($req->isAJAX() || strpos((string)$req->getHeaderLine('Accept'), 'application/json') !== false) {
            return $response->setStatusCode(403)->setJSON([
                'status'  => 403,
                'error'   => 'forbidden',
                'message' => $message,
            ]);
        }
        $html = "<h1 style='font-family:Arial;margin-top:40px;text-align:center'>403 Forbidden</h1>"
              . "<p style='font-family:Arial;text-align:center;color:#666'>" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "</p>";
        return $response->setStatusCode(403)->setBody($html);
    }
    
    private function ensureAclInSession($db, array $groupIds, int $programId): void
    {
        $session = session();
        if ($session->get('acl_program_id') === $programId && $session->has('acl')) {
            return; // sudah ada dan masih untuk program ini
        }

        $rows = $db->table('`privilege` pr')
            ->select('p.controller, a.name AS action')
            ->join('`page` p', 'p.page_id = pr.page_id')
            ->join('`action` a', 'a.action_id = pr.action_id')
            ->whereIn('pr.group_id', $groupIds)
            ->get()->getResult();

        $acl = [];
        foreach ($rows as $r) {
            $pageKey = strtolower((string)$r->controller);   // pakai kolom identitas page yg Anda gunakan
            $action  = strtolower((string)$r->action);
            $acl[$pageKey][$action] = true;
        }

        $session->set([
            'acl'             => $acl,
            'acl_program_id'  => $programId,
        ]);
    }

}