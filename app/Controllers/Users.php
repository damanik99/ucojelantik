<?php

namespace App\Controllers;

use App\Models\UsersModel;
use App\Models\DriverModel;
use App\Models\CompanyModel;
use App\Models\GroupModel;
use App\Models\UsersGroupProgramModel;

use App\Models\ProvinceModel;
use App\Models\KabkotModel;
use App\Models\KecamatanModel;
use App\Models\KelurahanModel;

class Users extends BaseController
{
    protected UsersModel $users;
    protected $driver;
    protected $db;
    protected CompanyModel $company;
    protected GroupModel $group;
    protected UsersGroupProgramModel $usersGroupProgram;

    protected ProvinceModel $provinceModel;
    protected KabkotModel $kabkotModel;
    protected KecamatanModel $kecamatanModel;
    protected KelurahanModel $kelurahanModel;

    public function __construct()
    {
        $session = \Config\Services::session();
        if ($session->get('masuk') != true) {
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Maaf! Anda tidak memiliki hak akses ke sini! </div>');
            header('Location: '.base_url('auth'));
            exit();
        }

        $this->users = new UsersModel();
        $this->driver = new DriverModel();
        $this->company = new CompanyModel();
        $this->group = new GroupModel();
        $this->usersGroupProgram = new UsersGroupProgramModel();

        $this->provinceModel  = new ProvinceModel();
        $this->kabkotModel    = new KabkotModel();
        $this->kecamatanModel = new KecamatanModel();
        $this->kelurahanModel = new KelurahanModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Users',
        ];

        echo view('users/index', $data);
    }

    public function create()
    {
        $data['groupProgram'] = $this->users->getgroupProgram();

        $data['groups'] = $this->group
            ->findAll();
        
        $data['companies'] = $this->company
            ->select('company.*')
            ->join('company_program', 'company.company_id = company_program.company_id')
            ->join('companytype', 'company_program.company_type_id = companytype.type_id')
            ->where('company.status_id', '15')
            ->where('companytype.type_name', 'Supplier')
            ->findAll();
        
        $data['provinces'] = $this->provinceModel
            ->orderBy('provinsi', 'ASC')
            ->findAll();
            
        return view('users/create', $data);
    }

    public function store()
    {

        $validation = \Config\Services::validation();

        $rules = [
            'username'   => 'required|is_unique[users.username]',
            'password'   => 'required|min_length[6]',
            'fullname'   => 'required',
            'phone'      => 'required',
            'email'      => 'required|valid_email',
            'province_id' => 'required',
            'city_id'     => 'required',
            'district_id' => 'required',
            'village_id'  => 'required',
            'title'       => 'required',
            'address'     => 'required',
            'group_id'   => 'required',
            'data_level' => 'required'
        ];

        if (!$validation->setRules($rules)->run($this->request->getPost()))
        {
            return $this->response->setJSON([
                'status' => false,
                'message' => $validation->getErrors()
            ]);
        }

        $groupId = $this->request->getPost('group_id');

        $group = $this->group->find($groupId);

        $groupName = $group['name'] ?? '';
        $program_id = session()->get('program');
        
        if(strtolower($groupName) == 'driver')
        {
            $driverRules = [
                'driver_type' => 'required',
                'driver_name' => 'required',
                'license_number' => 'required'
            ];

            if (!$validation->setRules($driverRules)->run($this->request->getPost()))
            {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => $validation->getErrors()
                ]);
            }

            if (
                $this->request->getPost('driver_type') == 'SUPPLIER'
                &&
                empty($this->request->getPost('supplier_id'))
            )
            {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => [
                        'supplier_id' => 'Supplier wajib dipilih'
                    ]
                ]);
            }
        }

        $this->db->transBegin();

        try {

            $this->users->insert([
                'username'      => $this->request->getPost('username'),
                'password'      => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'fullname'      => $this->request->getPost('fullname'),
                'phone'         => $this->request->getPost('phone'),
                'email'         => $this->request->getPost('email'),
                'province_id'   => $this->request->getPost('province_id'),
                'city_id'       => $this->request->getPost('city_id'),
                'district_id'   => $this->request->getPost('district_id'),
                'village_id'    => $this->request->getPost('village_id'),
                'address'       => $this->request->getPost('address'),
                'title'         => $this->request->getPost('title'),
                'active'        => 1,
                'created_date'  => date('Y-m-d H:i:s'),
                'modified_date' => date('Y-m-d H:i:s'),
                'created_by'    => session()->get('users_id')
            ]);

            $usersId = $this->users->getInsertID();

            $this->usersGroupProgram->insert([
                'users_id'      => $usersId,
                'group_id'      => $this->request->getPost('group_id'),
                'program_id'    => $program_id,
                'data_level'    => $this->request->getPost('data_level'),
                'created_date'  => date('Y-m-d H:i:s'),
                'modified_date' => date('Y-m-d H:i:s'),
                'created_by'    => session()->get('users_id'),
                'modified_by'   => null
            ]);
            
            if (strtolower($groupName) == 'driver')
            {
                $this->driver->insert([
                    'users_id'            => $usersId,
                    'supplier_id'         => $this->request->getPost('supplier_id'),
                    'driver_type'         => $this->request->getPost('driver_type'),
                    'driver_name'         => $this->request->getPost('driver_name'),
                    'license_number'      => $this->request->getPost('license_number'),
                    'license_type'        => $this->request->getPost('license_type'),
                    'license_expiry_date' => $this->request->getPost('license_expiry_date'),
                    'active'              => 1,
                    'created_date'        => date('Y-m-d H:i:s'),
                    'modified_date'       => date('Y-m-d H:i:s'),
                    'created_by'          => session()->get('users_id')
                ]);
            }

            if ($this->db->transStatus() === false)
            {
                throw new \Exception('Transaction Error');
            }

            $this->db->transCommit();

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data berhasil disimpan'
            ]);

        } catch (\Exception $e) {

            $this->db->transRollback();

            return $this->response->setJSON([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function datatables()
    {
        $request = service('request');

        $draw   = $request->getPost('draw');
        $start  = $request->getPost('start');
        $length = $request->getPost('length');
        $search = $request->getPost('search')['value'] ?? '';

        $baseQuery = "
            FROM users a

            LEFT JOIN tbl_provinsi p
                ON a.province_id = p.id

            LEFT JOIN tbl_kabkot c
                ON a.city_id = c.id

            LEFT JOIN tbl_kecamatan d
                ON a.district_id = d.id

            LEFT JOIN tbl_kelurahan v
                ON a.village_id = v.id

            WHERE 1=1
        ";

        $filter = "";
        $params = [];

        if (!empty($search)) {

            $filter .= "
                AND (
                    a.username LIKE ?
                    OR a.fullname LIKE ?
                    OR a.phone LIKE ?
                    OR a.email LIKE ?
                    OR p.provinsi LIKE ?
                    OR c.kabupaten_kota LIKE ?
                    OR d.kecamatan LIKE ?
                    OR v.kelurahan LIKE ?
                )
            ";

            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $totalRecords = $this->db
            ->query("SELECT COUNT(*) cnt {$baseQuery}")
            ->getRow()
            ->cnt;

        $totalFiltered = $totalRecords;

        if (!empty($search)) {

            $totalFiltered = $this->db
                ->query(
                    "SELECT COUNT(*) cnt {$baseQuery} {$filter}",
                    $params
                )
                ->getRow()
                ->cnt;
        }

        $orderColumn = [
            'a.username',
            'a.fullname',
            'a.phone',
            'a.address',
            'a.active',
            'a.created_date'
        ];

        $orderDirection =
            $request->getPost('order')[0]['dir'] ?? 'DESC';

        $orderBy =
            $orderColumn[
                $request->getPost('order')[0]['column'] ?? 5
            ];

        $sql = "
            SELECT
                a.*,

                p.provinsi,
                c.kabupaten_kota,
                d.kecamatan,
                v.kelurahan

            {$baseQuery}
            {$filter}

            ORDER BY {$orderBy} {$orderDirection}
            LIMIT ?, ?
        ";

        $params[] = (int)$start;
        $params[] = (int)$length;

        $query = $this->db->query($sql, $params);

        $data = [];

        foreach ($query->getResultArray() as $row) {

            $address = implode(', ', array_filter([
                $row['address'],
                $row['kelurahan'],
                $row['kecamatan'],
                $row['kabupaten_kota'],
                $row['provinsi']
            ]));

            $status = $row['active']
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-danger">Inactive</span>';

            $row['address_full'] = $address;
            $row['status_badge'] = $status;

            $row['action'] = '
                <a href="'.base_url('/users/edit/'.$row['users_id']).'"
                class="badge badge-pill badge-success">
                    <i class="fa fa-pencil"></i>
                </a>

                <a href="javascript:void(0)"
                onclick="deleteData('.$row['users_id'].')"
                class="badge badge-pill badge-danger">
                    <i class="fa fa-trash"></i>
                </a>
            ';

            $data[] = [
                'username'      => $row['username'],
                'fullname'      => $row['fullname'],
                'phone'         => $row['phone'],
                'address'       => $row['address_full'],
                'status'        => $row['status_badge'],
                'action'        => $row['action']
            ];
        }

        return $this->response->setJSON([
            "draw"            => intval($draw),
            "recordsTotal"    => $totalRecords,
            "recordsFiltered" => $totalFiltered,
            "data"            => $data
        ]);
    }

    //Get data region
    public function getCity($provinceId)
    {
        $data = $this->kabkotModel
            ->where('provinsi_id', $provinceId)
            ->orderBy('kabupaten_kota', 'ASC')
            ->findAll();

        return $this->response->setJSON($data);
    }

    public function getDistrict($cityId)
    {
        $data = $this->kecamatanModel
            ->where('kabkot_id', $cityId)
            ->orderBy('kecamatan', 'ASC')
            ->findAll();

        return $this->response->setJSON($data);
    }

    public function getVillage($districtId)
    {
        $data = $this->kelurahanModel
            ->where('kecamatan_id', $districtId)
            ->orderBy('kelurahan', 'ASC')
            ->findAll();

        return $this->response->setJSON($data);
    }
    // end get region
}