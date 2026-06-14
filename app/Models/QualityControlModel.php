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
}