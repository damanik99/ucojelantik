<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentModel extends Model
{
    protected $table            = 'shipment';
    protected $primaryKey       = 'shipment_id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'shipment_number',
        'purchase_order_id',
        'supplier_id',
        'buyer_id',
        'driver_id',
        'vehicle_id',
        'departure_at',
        'arrival_at',
        'status_id',
        'qty_checkin',
        'qty_checkout',
        'unit',
        'created_date',
        'modified_date',
        'created_by',
        'modified_by'
    ];

    public function generateShipmentNumber()
    {
        $monthRoman = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII'
        ];

        $month = date('n');
        $year  = date('y');

        $prefix = '/SJ/' . $monthRoman[$month] . '/' . $year;

        $last = $this->select('shipment_number')
            ->like('shipment_number', $prefix, 'before')
            ->orderBy('shipment_id', 'DESC')
            ->first();

        if ($last) {

            $parts = explode('/', $last['shipment_number']);
            $number = (int)$parts[0] + 1;

        } else {

            $number = 1;

        }

        return sprintf('%03d', $number) . $prefix;
    }

    public function getActiveShipmentDriver($driverId)
    {
        $data = $this->db->table('shipment a')
            ->select("
                a.shipment_id,
                a.shipment_number,
                a.departure_at,
                b.company_name AS supplier,
                c.company_name AS buyer,
                d.status_name AS status,
                d.status_code
            ")

            ->join('company b', 'a.supplier_id = b.company_id')
            ->join('company c', 'a.buyer_id = c.company_id')
            ->join('status d', 'a.status_id = d.status_id')
            ->where('a.driver_id', $driverId)
            ->where('LOWER(d.status_code) !=', 'SCMPL')
            ->groupStart()
                ->where('a.departure_at IS NULL')
                ->orWhere('DATE(a.departure_at) <=', date('Y-m-d'))
            ->groupEnd()
            ->orderBy('a.shipment_id', 'DESC')
            ->get()
            ->getResultArray();
            // var_dump($data);exit;
        return $data;
    }

    public function getDetailShipmentDriver($shipmentId, $driverId)
    {
        return $this->db->table('shipment a')
            ->select("
                a.*,
                b.company_name AS supplier,
                c.company_name AS buyer,
                d.status_name AS status
            ")
            
            ->join('company b', 'a.supplier_id = b.company_id')
            ->join('company c', 'a.buyer_id = c.company_id')
            ->join('status d', 'a.status_id = d.status_id')
            ->where('a.shipment_id', $shipmentId)
            ->where('a.driver_id', $driverId)
            ->get()
            ->getRowArray();
    }

    public function getDetailShipment($shipmentId)
    {
        return $this->db->table('shipment a')
            ->select("
                a.*,
                b.company_name AS supplier,
                c.company_name AS buyer,
                d.status_name AS status
            ")
            
            ->join('company b', 'a.supplier_id = b.company_id')
            ->join('company c', 'a.buyer_id = c.company_id')
            ->join('status d', 'a.status_id = d.status_id')
            ->where('a.shipment_id', $shipmentId)
            ->get()
            ->getRowArray();
    }

    public function getShipmentTracking($shipmentTrackId)
    {
        return $this->db->table('shipment_tracking a')
            ->select("
                a.*,
                c.company_name AS supplier,
                d.company_name AS buyer,
                e.status_name AS status,
                b.shipment_number,
                b.shipment_id
            ")
            ->join('shipment b', 'a.shipment_id = b.shipment_id')
            ->join('company c', 'b.supplier_id = c.company_id')
            ->join('company d', 'b.buyer_id = d.company_id')
            ->join('status e', 'a.status_id = e.status_id')
            ->where('a.tracking_id', $shipmentTrackId)
            ->get()
            ->getRowArray();
    }

    public function getShipmentId($shipmentId)
    {
        return $this->db->table('shipment_tracking a')
            ->select("
                a.*,
                c.company_name AS supplier,
                d.company_name AS buyer,
                e.status_name AS status,
                b.shipment_number,
                b.shipment_id
            ")
            ->join('shipment b', 'a.shipment_id = b.shipment_id')
            ->join('company c', 'b.supplier_id = c.company_id')
            ->join('company d', 'b.buyer_id = d.company_id')
            ->join('status e', 'a.status_id = e.status_id')
            ->where('a.shipment_id', $shipmentId)
            ->get()
            ->getRowArray();
    }
}