<?php

namespace App\Controllers;

use App\Models\ShipmentTrackingModel;
use App\Models\ShipmentModel;
use App\Models\StatusModel;

class ShipmentTracking extends BaseController
{
    protected ShipmentTrackingModel $shipmentTracking;
    protected StatusModel $status;
    protected ShipmentModel $shipment;

    public function __construct()
    {
        $session = \Config\Services::session();
        if ($session->get('masuk') != true) {
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Maaf! Anda tidak memiliki hak akses ke sini! </div>');
            header('Location: '.base_url('auth'));
            exit();
        }

        $this->shipmentTracking = new ShipmentTrackingModel();
        $this->shipment = new ShipmentModel();
        $this->status = new StatusModel();
    }

    public function index()
    {
        $data['title'] = 'Shipment';

        return view('ShipmentTracking/index', $data);
    }

    public function create($shipmentId = null)
    {
        
        if ($this->request->getMethod() === 'post') {

            try {

                $status = $this->status
                    ->where('module', 'SHIPMENT_TRACKING')
                    ->where('status_code', 'DLPN')
                    ->first();
                // var_dump($status);exit;
                if (!$status) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Status DLPN tidak ditemukan.'
                    ]);
                }
                // var_dump($shipmentId);die;
                $photo = $this->request->getFile('photo');

                if (!$photo->isValid()) {
                    throw new \Exception('Photo wajib diupload.');
                }

                if (!$this->request->getPost('latitude')) {
                    throw new \Exception('Lokasi GPS belum diperoleh.');
                }

                $fileName = $photo->getRandomName();

                if (!is_dir(ROOTPATH . 'public/upload/image/shipmenttracking')) {
                    mkdir(
                        ROOTPATH . 'public/upload/image/shipmenttracking',
                        0775,
                        true
                    );
                }

                $photo->move(
                    ROOTPATH . 'public/image/shipmenttracking',
                    $fileName
                );

                $shipmentId = $this->request->getPost('shipment_id');

                $insertData = [
                    'shipment_id' => $shipmentId,
                    'photo'       => '/image/shipmenttracking/' . $fileName,
                    'latitude'    => $this->request->getPost('latitude'),
                    'longitude'   => $this->request->getPost('longitude'),
                    'notes'       => $this->request->getPost('address'),
                    'status_id'   => $status['status_id'],
                    'created_by'  => session()->get('users_id')
                ];

                $this->shipmentTracking->insert($insertData);

                $statusShipment = $this->status
                    ->where('module', 'SHIPMENT')
                    ->where('status_code', 'SDLPN')
                    ->first();

                if (!$statusShipment) {
                    throw new \Exception('Status SDLPN tidak ditemukan.');
                }

                $this->shipment->update($shipmentId, [
                    'status_id'     => $statusShipment['status_id'],
                    'modified_by'   => session()->get('users_id'),
                    'modified_date' => date('Y-m-d H:i:s')
                ]);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Check-In berhasil disimpan.'
                ]);

            } catch (\Exception $e) {

                return $this->response->setJSON([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        }

        $shipmentNumber = $this->shipment->getDetailShipment($shipmentId);

        $data = [
            'title'          => 'Check-In',
            'shipmentDetail' => $shipmentNumber
        ];

        return view('ShipmentTracking/create', $data);
    }

    public function arrived($shipmentId)
    {
        
        $shipmentTrack = $this->shipment->getShipmentId($shipmentId);
        
        $data = [
            'title'          => 'Check-In',
            'shipmentTrack' => $shipmentTrack
        ];

        return view('ShipmentTracking/arrived', $data);
    }

    public function arrivedsave($shipmentId = null)
    {
        if ($this->request->getMethod() === 'post') {
            // var_dump('test');exit;
            try {

                $status = $this->status
                    ->where('module', 'SHIPMENT_TRACKING')
                    ->where('status_code', 'SMBR')
                    ->first();
                
                if (!$status) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Status SMBR tidak ditemukan.'
                    ]);
                }
                // var_dump($shipmentId);die;
                $photo = $this->request->getFile('photo');

                if (!$photo->isValid()) {
                    throw new \Exception('Photo wajib diupload.');
                }

                if (!$this->request->getPost('latitude')) {
                    throw new \Exception('Lokasi GPS belum diperoleh.');
                }

                $extension = $photo->getExtension();

                $randomCode = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

                $photoName = 'BYR' . date('Ymd') . $randomCode . '.' . $extension;

                $photo->move(
                    ROOTPATH . 'public/upload/image/shipmenttracking',
                    $photoName
                );

                $shipmentId = $this->request->getPost('shipment_id');

                $insertData = [
                    'shipment_id' => $shipmentId,
                    'photo'       => '/image/shipmenttracking/' . $photoName,
                    'latitude'    => $this->request->getPost('latitude'),
                    'longitude'   => $this->request->getPost('longitude'),
                    'location'    => $this->request->getPost('location'),
                    'notes'       => $this->request->getPost('notes'),
                    'status_id'   => $status['status_id'],
                    'created_by'  => session()->get('users_id')
                ];

                $this->shipmentTracking->insert($insertData);

                $statusShipment = $this->status
                    ->where('module', 'SHIPMENT')
                    ->where('status_code', 'DLVD')
                    ->first();

                if (!$statusShipment) {
                    throw new \Exception('Status DLVD tidak ditemukan.');
                }

                $this->shipment->update($shipmentId, [
                    'status_id'     => $statusShipment['status_id'],
                    'modified_by'   => session()->get('users_id'),
                    'modified_date' => date('Y-m-d H:i:s')
                ]);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Check-In berhasil disimpan.'
                ]);

            } catch (\Exception $e) {

                return $this->response->setJSON([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
}