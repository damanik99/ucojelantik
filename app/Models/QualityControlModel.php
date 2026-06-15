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
}