<?php

namespace App\Models;

use CodeIgniter\Model;

class VehicleModel extends Model
{
    protected $table            = 'vehicle';
    protected $primaryKey       = 'vehicle_id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'company_program_id',
        'plate_number',
        'vehicle_type',
        'capacity_weight',
        'capacity_volume',
        'brand',
        'stnk_expiry_date',
        'kir_expiry_date',
        'status',
        'created_date',
        'modified_date',
        'created_by',
        'modified_by'
    ];

    protected $useTimestamps = false;
}