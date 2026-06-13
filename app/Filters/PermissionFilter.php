<?php


namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $uri = service('uri')->getSegment(1); // segment URL pertama, misal: product-code
        $userId = session()->get('users_id');
        $program = session()->get('program');

        $db = \Config\Database::connect();

        // Cek apakah user punya akses ke halaman ini
        $check = $db->query("SELECT f.page_id FROM usersgroupprogram a
            JOIN privilege e ON a.group_id = e.group_id
            JOIN page f ON e.page_id = f.page_id
            WHERE a.users_id = '$userId' AND a.program_id = '$program' AND f.name = '$uri'
        ");

        if ($check->getNumRows() == 0) {
            return redirect()->to('/index.html'); // arahkan ke halaman error
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // kosongkan jika tidak perlu
    }
}