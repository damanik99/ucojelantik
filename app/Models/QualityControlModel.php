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
        'company_name',
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
                sp.company_name AS supplier,
                by.company_name AS buyer,
                sct.type_name AS type_supplier,
                bct.type_name AS type_buyer,
                a.departure_at,
                a.supplier_company_program_id,
                a.buyer_company_program_id
            ")

            ->join('company_program scp', 'a.supplier_company_program_id = scp.company_program_id')
            ->join('company_program bcp', 'a.buyer_company_program_id = bcp.company_program_id')
            ->join('company sp', 'scp.company_id = sp.company_id')
            ->join('company by', 'bcp.company_id = by.company_id')
            ->join('companytype sct', 'scp.company_type_id = sct.type_id')
            ->join('companytype bct', 'bcp.company_type_id = bct.type_id')
            ->join('status s', 'a.status_id = s.status_id')
            ->where('s.status_code', 'RTDT')
            ->where('sct.type_id', '1')
            ->orderBy('a.shipment_id', 'DESC')
            ->get()
            ->getResultArray();

        return $result;
    }

}