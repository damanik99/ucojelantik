<?php

namespace App\Controllers;

use App\Models\QualityControlModel;

class QualityControl extends BaseController
{
    protected $qcModel;

    public function __construct()
    {
        $this->qcModel = new QualityControlModel();
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
        // var_dump($data);exit;
        
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

        try {

            $photo = $this->request->getFile('photo');

            $fileName = null;

            if ($photo->isValid()) {

                $fileName = time() . '_' . $photo->getRandomName();

                $photo->move(
                    ROOTPATH . 'public/upload/image/qc',
                    $fileName
                );
            }

            $this->qcModel->insert([
                'shipment_id' => $this->request->getPost('shipment_id'),
                // 'qc_type'     => $this->request->getPost('qc_type'),
                'result'      => $this->request->getPost('result'),
                'ffa'         => $this->request->getPost('ffa'),
                'mi'          => $this->request->getPost('mi'),
                'notes'       => $this->request->getPost('notes'),
                'photo'       => $fileName,
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
}