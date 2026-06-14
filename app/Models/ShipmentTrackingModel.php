<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentTrackingModel extends Model
{
    protected $table            = 'shipment_tracking';
    protected $primaryKey       = 'tracking_id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'shipment_id',
        'photo',
        'latitude',
        'longitude',
        'notes',
        'location',
        'status_id',
        'created_date',
        'created_by'
    ];

    protected $useTimestamps = false;

    public function getByShipment($shipmentId)
    {
        return $this->db->table('shipment_tracking a')
            ->select("
                a.*,
                b.name AS status_name,
                c.fullname AS created_name
            ")
            ->join('status b', 'a.status_id = b.status_id', 'left')
            ->join('users c', 'a.created_by = c.users_id', 'left')
            ->where('a.shipment_id', $shipmentId)
            ->orderBy('a.created_date', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getLastTracking($shipmentId)
    {
        return $this->db->table('shipment_tracking')
            ->where('shipment_id', $shipmentId)
            ->orderBy('tracking_id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();
    }
}