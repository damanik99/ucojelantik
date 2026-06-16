<?php

namespace App\Controllers;

use App\Models\QualityControlModel;
use App\Models\CompanyTypeModel;
use App\Models\ShipmentModel;

class QualityControl extends BaseController
{
    protected QualityControlModel $qcModel;
    protected CompanyTypeModel $companyType;
    protected ShipmentModel $shipment;

    public function __construct()
    {
        $session = \Config\Services::session();
        if ($session->get('masuk') != true) {
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Maaf! Anda tidak memiliki hak akses ke sini! </div>');
            header('Location: '.base_url('auth'));
            exit();
        }

        $this->qcModel = new QualityControlModel();
        $this->companyType = new CompanyTypeModel();
        $this->shipment = new ShipmentModel();
    }

    public function index() 
    {
        $data = [
            'title' => 'Quality Control'
        ];

        return view('qualitycontrol/index', $data);
    }

    public function create()
    {
        $companyType = $this->companyType->where('status', 'active')->findAll();

        $getDataShipment = $this->qcModel->getDataShipment();
        
        $data = [
            'shipments' => $getDataShipment,
            'companyType' => $companyType
        ];
        
        return view('qualitycontrol/create', $data);
    }

    public function save()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Invalid Request'
            ]);
        }

        // var_dump('test');exit;

        $rules = [
            'shipment_id' => 'required',
            'type_id'     => 'required',
            'result'      => 'required',
        ];

        if (!$this->validate($rules)) {

            return $this->response->setJSON([
                'status' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        try 
        {
            $type_id = $this->request->getPost('type_id');
            $shipmentId = $this->request->getPost('shipment_id');
            $photo = $this->request->getFile('photo');
            if ($photo->isValid()) {
                $extension = $photo->getExtension();
    
                $randomCode = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
                $photoName = 'BYR' . date('Ymd') . $randomCode . '.' . $extension;
                if (!is_dir(ROOTPATH . 'public/upload/image/qc')) {
                    mkdir(
                        ROOTPATH . 'public/upload/image/qc',
                        0775,
                        true
                    );
                }
    
                $photo->move(
                    ROOTPATH . 'public/upload/image/qc',
                    $photoName
                );
            }
            

            // $getTypeId = $this->db->table('shipment')->where('shipment_id', $shipmentId)->get()->getRowArray();
            $getTypeId = $this->shipment->getDetailShipment($shipmentId);
            // var_dump($getTypeId);exit;
            if ($getTypeId['supplier_id'] == '1') {
                $qc_type = $type_id;
            } else {
                $buyer = $getTypeId['supplier_id'];
            }

            $this->qcModel->insert([
                'shipment_id' => $this->request->getPost('shipment_id'),
                'company_name'=> $getTypeId['supplier'],
                'qc_type'     => $qc_type, 
                'result'      => $this->request->getPost('result'),
                'ffa'         => $this->request->getPost('ffa'),
                'mi'          => $this->request->getPost('mi'),
                'notes'       => $this->request->getPost('notes'),
                'photo'       => '/upload/image/qc/' . $photoName,
                'created_by'  => session()->get('users_id')
            ]);

            return $this->response->setJSON([
                'status' => true,
                'message' => 'QC berhasil disimpan'
            ]);

        } catch (\Exception $e) {

            return $this->response->setJSON([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function view($id)
    {
        $viewId = $this->qcModel->getData($id);

        $data = [
            'title' => 'View',
            'view' => $viewId
        ];

        return view('qualitycontrol/detail', $data);
    }

    public function datatables()
    {
        $request = service('request');

        $draw   = $request->getPost('draw');
        $start  = $request->getPost('start');
        $length = $request->getPost('length');
        $search = $request->getPost('search')['value'] ?? '';

        $baseQuery = "
            FROM quality_control a
            JOIN shipment b
                ON a.shipment_id = b.shipment_id
            JOIN company c
                ON b.supplier_id = c.company_id
            LEFT JOIN status d
                ON a.status_id = d.status_id
            WHERE 1=1
        ";

        $filter = "";
        $params = [];

        if (!empty($search)) {

            $filter .= "
                AND (
                    b.shipment_number LIKE ?
                    OR c.company_name LIKE ?
                    OR a.qc_type LIKE ?
                    OR a.result LIKE ?
                    OR d.status_name LIKE ?
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
            'b.shipment_number',
            'c.company_name',
            'a.qc_type',
            'a.result',
            'a.ffa',
            'a.mi',
            'd.status_name',
            'a.created_date'
        ];

        $orderDirection =
            $request->getPost('order')[0]['dir'] ?? 'DESC';

        $orderBy =
            $orderColumn[
                $request->getPost('order')[0]['column'] ?? 7
            ];

        $sql = "
            SELECT
                a.*,
                b.shipment_number,
                c.company_name,
                d.status_code,
                d.status_name
            {$baseQuery}
            {$filter}
            ORDER BY {$orderBy} {$orderDirection}
            LIMIT ?, ?";

        $params[] = (int)$start;
        $params[] = (int)$length;

        $query = $this->db->query($sql, $params);

        $data = [];

        foreach ($query->getResultArray() as $row) {

            if ($row['result'] == 'in_spec') {

                $row['result_badge'] =
                    '<span class="badge badge-success">
                        In Spec
                    </span>';

            } elseif ($row['result'] == 'out_spec') {

                $row['result_badge'] =
                    '<span class="badge badge-danger">
                        Out Spec
                    </span>';

            } else {

                $row['result_badge'] =
                    '<span class="badge badge-secondary">
                        '.$row['result'].'
                    </span>';
            }

            $row['action'] = '

                <a href="'.base_url('/qualitycontrol/view/'.$row['qc_id']).'"
                class="badge badge-pill badge-info">
                    <i class="fa fa-eye"></i>
                </a>

                <a href="'.base_url('/qualitycontrol/edit/'.$row['qc_id']).'"
                class="badge badge-pill badge-success">
                    <i class="fa fa-pencil"></i>
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