<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\VehicleModel;
use App\Models\CompanyModel;

class Vehicle extends BaseController
{
    protected VehicleModel $vehicle;
    protected CompanyModel $company;

    public function __construct()
    {
        $session = \Config\Services::session();
        if ($session->get('masuk') != true) {
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Maaf! Anda tidak memiliki hak akses ke sini! </div>');
            header('Location: '.base_url('auth'));
            exit();
        }

        $this->vehicle = new VehicleModel();
        $this->company = new CompanyModel();
    }

    public function index()
    {
        $data['title'] = 'Vehicle';

        return view('vehicle/index', $data);
    }

    public function create()
    {
        if ($this->request->getMethod() === 'post') {

            $rules = [
                'company_id' => [
                    'rules'  => 'required|integer',
                    'errors' => [
                        'required' => 'Company must be selected'
                    ]
                ],
                'plate_number' => [
                    'rules'  => 'required|is_unique[vehicle.plate_number]',
                    'errors' => [
                        'required'  => 'License plate number is required',
                        'is_unique' => 'Police number is already in use'
                    ]
                ],
                'vehicle_type' => [
                    'rules'  => 'required',
                    'errors' => [
                        'required' => 'Vehicle type is required'
                    ]
                ],
                'status' => [
                    'rules'  => 'required',
                    'errors' => [
                        'required' => 'Status is required to be selected'
                    ]
                ]
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $this->vehicle->insert([
                'company_id'        => $this->request->getPost('company_id'),
                'plate_number'      => strtoupper($this->request->getPost('plate_number')),
                'vehicle_type'      => $this->request->getPost('vehicle_type'),
                'capacity_weight'   => $this->request->getPost('capacity_weight'),
                'capacity_volume'   => $this->request->getPost('capacity_volume'),
                'brand'             => $this->request->getPost('brand'),
                'stnk_expiry_date'  => $this->request->getPost('stnk_expiry_date'),
                'kir_expiry_date'   => $this->request->getPost('kir_expiry_date'),
                'status'            => $this->request->getPost('status'),
                'created_date'      => date('Y-m-d H:i:s'),
                'modified_date'     => date('Y-m-d H:i:s'),
                'created_by'        => session()->get('user_id')
            ]);

            return $this->response->setJSON([
                'status'   => true,
                'message'  => 'Data kendaraan berhasil disimpan',
                'redirect' => base_url('/vehicle')
            ]);
        }

        $dataCompany = $this->company
            ->where('is_deleted', 0)
            ->findAll();

       $data = [
            'title' => 'Vehicle',
            'company' => $dataCompany
        ];

        return view('vehicle/create', $data);
    }

    public function datatables()
    {
        $request = service('request');

        $draw   = $request->getPost('draw');
        $start  = $request->getPost('start');
        $length = $request->getPost('length');
        $search = $request->getPost('search')['value'] ?? '';

        $baseQuery = "
            FROM vehicle a
            JOIN company b
                ON a.company_id = b.company_id
            WHERE 1=1
        ";

        $filter = "";
        $params = [];

        if (!empty($search)) {

            $filter .= "
                AND (
                    b.company_name LIKE ?
                    OR a.plate_number LIKE ?
                    OR a.vehicle_type LIKE ?
                    OR a.brand LIKE ?
                    OR a.status LIKE ?
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
            'b.company_name',
            'a.plate_number',
            'a.vehicle_type',
            'a.brand',
            'a.capacity_weight',
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
                b.company_name
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

            if ($row['status'] == 'available') {

                $row['status_badge'] =
                    '<span class="badge badge-success">Available</span>';

            } elseif ($row['status'] == 'on_delivery') {

                $row['status_badge'] =
                    '<span class="badge badge-primary">On Delivery</span>';

            } else {

                $row['status_badge'] =
                    '<span class="badge badge-danger">Maintenance</span>';
            }

            $row['action'] = '
                <a href="'.base_url().'/vehicle/edit/'.$row['vehicle_id'].'"
                class="badge badge-pill badge-success">
                    <i class="fa fa-pencil"></i>
                </a>

                <a href="javascript:void(0)"
                onclick="deleteData('.$row['vehicle_id'].')"
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