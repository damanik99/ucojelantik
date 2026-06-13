<?php

namespace App\Models;

use CodeIgniter\Model;

class UsersModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'users_id';

    protected $allowedFields = [
        'username',
        'password',
        'fullname',
        'phone',
        'email',
        'city_id',
        'district_id',
        'village_id',
        'address',
        'active',
        'created_date',
        'modified_date',
        'created_by',
        'modified_by'
    ];
}