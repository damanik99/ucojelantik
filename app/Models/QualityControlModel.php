<?php

namespace App\Models;

use CodeIgniter\Model;

class QualityControlModel extends Model
{
    protected $table            = 'quality_control';
    protected $primaryKey       = 'qc_id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'shipment_id',
        'qc_type',
        'result',
        'ffa',
        'mi',
        'photo',
        'notes',
        'status_id',
        'created_by',
        'modified_by'
    ];

    protected $useTimestamps = false;

    protected $createdField  = 'created_date';
    protected $updatedField  = 'modified_date';

    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = true;

    function getData($shipmentId)
    {
        return $this->db->table('quality_control a')
            ->select("
                *
            ")
            ->join('shipment b', 'a.shipment_id = b.shipment_id')
            ->join('company c', 'b.supplier_id = c.company_id', 'left')
            ->where('a.shipment_id', $shipmentId)
            ->orderBy('a.created_date', 'ASC')
            ->get()
            ->getResultArray();
    }

    function getDataShipment()
    {
        $result = $this->db->table('shipment a')
            ->select("
                a.shipment_id,
                a.shipment_number,
                b.company_name,
                a.departure_at
            ")

            ->join('company b', 'a.supplier_id = b.company_id')
            ->join('company_program cp', 'b.company_id = cp.company_id')
            ->join('status s', 'a.status_id = s.status_id')
            ->where('s.status_code', 'RTDT')
            ->where('cp.company_type_id', '1')
            ->orderBy('a.shipment_id', 'DESC')
            ->get()
            ->getResultArray();

        return $result;
    }

}