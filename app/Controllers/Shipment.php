<?php

namespace App\Controllers;

use App\Models\ShipmentModel;
use App\Models\CompanyModel;
use App\Models\CompanyTypeModel;
use App\Models\DriverModel;
use App\Models\VehicleModel;
use App\Models\PurchaseOrderModel;
use App\Models\StatusModel;

class Shipment extends BaseController
{
    protected ShipmentModel $shipment;
    protected CompanyModel $company;
    protected CompanyTypeModel $companyType;

    public function __construct()
    {
        $session = \Config\Services::session();
		if($session->get('masuk') != TRUE ){
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Maaf! Anda tidak memiliki hak akses ke sini! </div>');
            header('Location: '.base_url('auth'));
            exit();
        }

        $this->shipment = new ShipmentModel();
        $this->company = new CompanyModel();
        $this->companyType = new CompanyTYpeModel();
    }

    public function index()
    {
        $data['title'] = 'Shipment';

        return view('shipment/index', $data);
    }

    public function datatables()
    {
        $request = service('request');

        $draw   = $request->getPost('draw');
        $start  = $request->getPost('start');
        $length = $request->getPost('length');
        $search = $request->getPost('search')['value'] ?? '';

        $baseQuery = "
            FROM shipment a
            LEFT JOIN company_program b
                ON a.supplier_company_program_id = b.company_program_id
            LEFT JOIN company_program c
                ON a.buyer_company_program_id = c.company_program_id
            LEFT JOIN company cp
                ON b.company_id = cp.company_id
            LEFT JOIN company cpb
                ON c.company_id = cpb.company_id
            LEFT JOIN driver d
                ON a.driver_id = d.driver_id
            LEFT JOIN vehicle e
                ON a.vehicle_id = e.vehicle_id
            LEFT JOIN status f
                ON a.status_id = f.status_id
            WHERE 1=1
        ";

        $filter = "";
        $params = [];

        if (!empty($search)) {

            $filter .= "
                AND (
                    a.shipment_number LIKE ?
                    OR cp.company_name LIKE ?
                    OR cpb.company_name LIKE ?
                    OR d.driver_name LIKE ?
                    OR e.plate_number LIKE ?
                    OR f.status_name LIKE ?
                )
            ";

            for ($i = 0; $i < 6; $i++) {
                $params[] = "%{$search}%";
            }
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
            'a.shipment_number',
            'cp.company_name',
            'cpb.company_name',
            'd.driver_name',
            'e.plate_number',
            'f.status_name',
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
                cp.company_name AS supplier_name,
                cpb.company_name AS buyer_name,
                d.driver_name,
                e.plate_number,
                f.status_name
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

            $statusBadge = '';

            switch (strtoupper($row['status_name'])) {

                case 'PENDING':
                    $statusBadge = '<span class="badge badge-warning">'.$row['status_name'].'</span>';
                    break;

                case 'DEPARTED':
                    $statusBadge = '<span class="badge badge-info">'.$row['status_name'].'</span>';
                    break;

                case 'ARRIVED':
                    $statusBadge = '<span class="badge badge-primary">'.$row['status_name'].'</span>';
                    break;

                case 'DELIVERED':
                    $statusBadge = '<span class="badge badge-success">'.$row['status_name'].'</span>';
                    break;

                default:
                    $statusBadge = '<span class="badge badge-secondary">'.$row['status_name'].'</span>';
                    break;
            }

            $row['status_badge'] = $statusBadge;

            $row['action'] = '
                <a href="'.base_url('/shipment/view/'.$row['shipment_id']).'"
                class="badge badge-pill badge-info">
                    <i class="fa fa-eye"></i>
                </a>

                <a href="'.base_url('/shipment/edit/'.$row['shipment_id']).'"
                class="badge badge-pill badge-success">
                    <i class="fa fa-pencil"></i>
                </a>

                <a href="javascript:void(0)"
                onclick="deleteData('.$row['shipment_id'].')"
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

    public function create()
    {
        $program_id = session()->get('program');                
        if ($this->request->getMethod() == 'post') {
            
            $validation = \Config\Services::validation();

            $rules = [
                'shipment_number' => [
                    'label' => 'Shipment Number',
                    'rules' => 'required|is_unique[shipment.shipment_number]'
                ],
                'supplier_company_program_id' => [
                    'label' => 'supplier_company_program_id',
                    'rules' => 'required'
                ],
                'buyer_company_program_id' => [
                    'label' => 'buyer_company_program_id',
                    'rules' => 'required'
                ],
                'driver_id' => [
                    'label' => 'Driver',
                    'rules' => 'required'
                ],
                'vehicle_id' => [
                    'label' => 'Vehicle',
                    'rules' => 'required'
                ],
                'status_id' => [
                    'label' => 'Status',
                    'rules' => 'required'
                ]
            ];

            if (!$validation->setRules($rules)->run($this->request->getPost())) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $validation->getErrors()
                ]);
            }

            $shipmentNumber = $this->shipment->generateShipmentNumber();
            // var_dump($shipmentNumber);exit;
            $this->shipment->insert([
                'shipment_number'               => $shipmentNumber,
                'purchase_order_id'             => $this->request->getPost('purchase_order_id'),
                'supplier_company_program_id'   => $this->request->getPost('supplier_company_program_id'),
                'buyer_company_program_id'      => $this->request->getPost('buyer_company_program_id'),
                'driver_id'                     => $this->request->getPost('driver_id'),
                'vehicle_id'                    => $this->request->getPost('vehicle_id'),
                'departure_at'                  => $this->request->getPost('departure_at'),
                'arrival_at'                    => $this->request->getPost('arrival_at'),
                'status_id'                     => $this->request->getPost('status_id'),
                'created_by'                    => session()->get('users_id')
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Shipment successfully created'
            ]);
        }

        $data['supplier'] = $this->companyType->getCompanyByType('SUPPLIER', $program_id);
        $data['buyer']    = $this->companyType->getCompanyByType('BUYER', $program_id);
        $data['driver']   = (new DriverModel())->findAll();
        $data['vehicle']  = (new VehicleModel())->findAll();
        // $data['po']       = (new PurchaseOrderModel())->findAll();
        
        $data['status'] = (new StatusModel())
            ->where('module', 'SHIPMENT')
            ->findAll();

        return view('shipment/create', $data);
    }
    
    public function driver()
    {
        $shipmentModel = new \App\Models\ShipmentModel();
        $driverId = session()->get('driver_id');
        $data['shipment'] = $shipmentModel->getActiveShipmentDriver($driverId);
        
        return view('driver/home', $data);
    }

    public function detail($shipmentId)
    {
        $shipmentModel = new \App\Models\ShipmentModel();

        $driverId = session()->get('driver_id');
        $shipment = $shipmentModel->getDetailShipmentDriver($shipmentId, $driverId);

        if (!$shipment) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $data['shipment'] = $shipment;
        return view('shipment/driver/detail', $data);
    }

    // Dropdown driver
    public function get_driver($company_program_id)
    {
        $driver = $this->db->table('driver d')
            ->select('d.driver_id, d.driver_name')
            ->join('company_program cp', 'cp.company_program_id = d.company_program_id')
            ->where('cp.company_program_id', $company_program_id)
            ->get()->getResultArray();

        return $this->response->setJSON($driver);
    }

    // Dropdown vehicle
    public function get_vehicle($company_program_id)
    {
        $vehicle = $this->db->table('vehicle v')
            ->select('v.vehicle_id, v.plate_number, v.brand')
            ->join('company_program cp', 'cp.company_program_id = v.company_program_id')
            ->where('cp.company_program_id', $company_program_id)
            ->get()->getResultArray();
        
            return $this->response->setJSON($vehicle);
    }
    
    
}