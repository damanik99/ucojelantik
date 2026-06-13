<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\CompanyTypeModel;

class Company extends BaseController
{
    protected CompanyModel $company;
    protected CompanyTypeModel $companyType;

    public function __construct()
    {
        $session = \Config\Services::session();
        if ($session->get('masuk') != true) {
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Maaf! Anda tidak memiliki hak akses ke sini! </div>');
            header('Location: '.base_url('auth'));
            exit();
        }

        $this->company = new CompanyModel();
        $this->companyType = new CompanyTypeModel();
    }

    public function index()
    {
        $data['title'] = 'Company';
        $data['companytypes'] = $this->companyType->findAll();

        return view('company/index', $data);
    }

    public function create()
    {
        return view('company/create', [
            'title' => 'Create Company',
            'companyTypes' => $this->companyType
                ->where('status', 'active')
                ->findAll()
        ]);
    }

    public function store()
    {
        $rules = [
            'company_type_id' => 'required',
            'company_name' => 'required',
            'email' => 'permit_empty|valid_email',
            'latitude' => 'permit_empty|decimal',
            'longitude' => 'permit_empty|decimal'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => $this->validator->getErrors()
            ]);
        }

        $this->company->insert([
            'company_type_id' => $this->request->getPost('company_type_id'),
            'company_name' => $this->request->getPost('company_name'),
            'pic_name' => $this->request->getPost('pic_name'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'address' => $this->request->getPost('address'),
            'latitude' => $this->request->getPost('latitude'),
            'longitude' => $this->request->getPost('longitude'),
            'status' => $this->request->getPost('status'),
            'created_by' => session()->get('user_id')
        ]);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Company berhasil disimpan'
        ]);
    }

    public function datatables()
    {
        $request = service('request');

        $draw   = $request->getPost('draw');
        $start  = $request->getPost('start');
        $length = $request->getPost('length');
        $search = $request->getPost('search')['value'] ?? '';

        $baseQuery = "
            FROM company a
            JOIN companytype b
                ON a.company_type_id = b.type_id
            WHERE 1=1
        ";

        $filter = "";
        $params = [];

        if (!empty($search)) {

            $filter .= "
                AND (
                    a.company_name LIKE ?
                    OR b.type_name LIKE ?
                    OR a.pic_name LIKE ?
                    OR a.phone LIKE ?
                    OR a.email LIKE ?
                )
            ";

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
            'a.company_name',
            'b.type_name',
            'a.pic_name',
            'a.phone',
            'a.email',
            'a.status',
            'a.created_date'
        ];

        $orderDirection =
            $request->getPost('order')[0]['dir'] ?? 'DESC';

        $orderBy =
            $orderColumn[
                $request->getPost('order')[0]['column'] ?? 6
            ];

        $sql = "
            SELECT
                a.*,
                b.type_name AS company_type
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

            $row['action'] = '
                <a href="'.base_url().'/company/edit/'.$row['company_id'].'"
                class="badge badge-pill badge-success">
                    <i class="fa fa-pencil"></i>
                </a>

                <a href="javascript:void(0)"
                onclick="deleteData('.$row['company_id'].')"
                class="badge badge-pill badge-danger">
                    <i class="fa fa-trash"></i>
                </a>
            ';

            $data[] = $row;
        }

        return $this->response->setJSON([
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalFiltered,
            "data" => $data
        ]);
    }
}