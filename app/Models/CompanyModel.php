<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyModel extends Model
{
    protected $table = 'company';
    protected $primaryKey = 'company_id';

    protected $allowedFields = [
        'company_type_id',
        'company_name',
        'pic_name',
        'phone',
        'email',
        'address',
        'latitude',
        'longitude',
        'status',
        'created_by',
        'modified_by'
    ];

    protected $useTimestamps = false;
}