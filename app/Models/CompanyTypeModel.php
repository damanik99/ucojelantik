<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyTypeModel extends Model
{
    protected $table = 'companytype';
    protected $primaryKey = 'type_id';

    protected $allowedFields = [
        'type_name',
        'description',
        'status',
        'created_by',
        'modified_by'
    ];

    protected $useTimestamps = false;

    public function getCompanyByType($typeCode)
    {
        return $this->db->table('company a')
            ->select('a.*')
            ->join('companytype b', 'a.company_type_id = b.type_id')
            ->where('b.type_code', $typeCode)
            ->orderBy('a.company_name', 'ASC')
            ->get()
            ->getResultArray();
    }
}