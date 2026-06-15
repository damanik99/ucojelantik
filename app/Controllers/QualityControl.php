<?php

namespace App\Controllers;

use App\Models\QualityControlModel;

class QualityControl extends BaseController
{
    protected QualityControlModel $qcModel;

    public function __construct()
    {
        $this->qcModel = new QualityControlModel();
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
        $data['shipments'] = $this->db->table('shipment a')
            ->select("
                a.shipment_id,
                a.shipment_number,
                b.company_name
            ")

            ->join('company b', 'a.supplier_id = b.company_id')
            ->join('company_program cp', 'b.company_id = cp.company_id')
            ->join('status s', 'a.status_id = s.status_id')
            ->where('s.status_code', 'RTDT')
            ->where('cp.company_type_id', '1')
            ->orderBy('a.shipment_id', 'DESC')
            ->get()
            ->getResultArray();
        
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

        $rules = [
            'shipment_id' => 'required',
            // 'qc_type'     => 'required',
            'result'      => 'required',
            'photo'       => 'uploaded[photo]|max_size[photo,4096]|is_image[photo]'
        ];

        if (!$this->validate($rules)) {

            return $this->response->setJSON([
                'status' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        try 
        {
            $photo = $this->request->getFile('photo');

            if (!$photo->isValid()) {
                throw new \Exception('Photo wajib diupload.');
            }

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

            $this->qcModel->insert([
                'shipment_id' => $this->request->getPost('shipment_id'),
                // 'qc_type'     => $this->request->getPost('qc_type'),
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

            if ($row['result'] == 'PASSED') {

                $row['result_badge'] =
                    '<span class="badge badge-success">
                        PASSED
                    </span>';

            } elseif ($row['result'] == 'FAILED') {

                $row['result_badge'] =
                    '<span class="badge badge-danger">
                        FAILED
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