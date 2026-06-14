<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\CompanyTypeModel;
use App\Models\CompanyProgramModel;
use App\Models\StatusModel;

class Company extends BaseController
{
    protected CompanyModel $company;
    protected CompanyTypeModel $companyType;
    protected CompanyProgramModel $companyProgram;
    protected StatusModel $status;

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
        $this->companyProgram = new CompanyProgramModel();
        $this->status = new StatusModel();
    }

    public function index()
    {
        $data['title'] = 'Company';
        $data['companytypes'] = $this->companyType->findAll();

        return view('company/index', $data);
    }

    public function create()
    {
        $companyType = $this->companyType
                ->where('status', 'active')
                ->findAll();
        $status = $this->status->getByModule('COMPANY');

        $data = [
            'title' => 'Create Company',
            'companyTypes' => $companyType,
            'status' => $status
        ];

        return view('company/create', $data);
    }

    public function store()
    {
        $program_id = session()->get('program');
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

        $status = $this->request->getPost('status_id');
        // dd($status);

        $this->db->transStart();
        $this->company->insert([
            'company_name' => $this->request->getPost('company_name'),
            'pic_name'     => $this->request->getPost('pic_name'),
            'phone'        => $this->request->getPost('phone'),
            'email'        => $this->request->getPost('email'),
            'address'      => $this->request->getPost('address'),
            'latitude'     => $this->request->getPost('latitude'),
            'longitude'    => $this->request->getPost('longitude'),
            'status_id'    => $this->request->getPost('status_id'),
            'created_by'   => session()->get('user_id')
        ]);

        $companyId = $this->company->getInsertID();

        $this->companyProgram->insert([
            'company_id'      => $companyId,
            'program_id'      => $program_id,
            'company_type_id' => $this->request->getPost('company_type_id'),
            'status_id'       => $this->request->getPost('status_id'),
            'created_by'      => session()->get('user_id')
        ]);

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Gagal menyimpan data'
            ]);
        }

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
            JOIN company_program b
                ON a.company_id = b.company_id
            JOIN companytype c
                ON b.company_type_id = c.type_id
            JOIN status d
                ON b.status_id = d.status_id
            WHERE 1=1
        ";

        $filter = "";
        $params = [];

        if (!empty($search)) {

            $filter .= "
                AND (
                    a.company_name LIKE ?
                    OR c.type_name LIKE ?
                    OR a.pic_name LIKE ?
                    OR a.phone LIKE ?
                    OR a.email LIKE ?
                    OR d.status_name LIKE ?
                )
            ";

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
            'a.company_name',
            'c.type_name',
            'a.pic_name',
            'a.phone',
            'a.email',
            'd.status_name',
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
                b.company_program_id,
                c.type_name AS company_type,
                d.status_code,
                d.status_name
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

            if ($row['status_code'] == 'ACTV') {

                $row['status_badge'] =
                    '<span class="badge badge-success">'
                    .$row['status_name'].
                    '</span>';

            } elseif ($row['status_code'] == 'INAC') {

                $row['status_badge'] =
                    '<span class="badge badge-danger">'
                    .$row['status_name'].
                    '</span>';

            } else {

                $row['status_badge'] =
                    '<span class="badge badge-secondary">'
                    .$row['status_name'].
                    '</span>';
            }

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