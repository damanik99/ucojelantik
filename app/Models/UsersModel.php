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
        'title',
        'active',
        'created_date',
        'modified_date',
        'created_by',
        'modified_by'
    ];

    public function getgroupProgram()
    {
        $program_id = session()->get('program');
        $result = $this->db->table('groupprogram a')
            ->select("
                a.group_program_id AS group_id,
                CONCAT(b.name, ' - ', c.name) AS title, b.name AS group
            ")
            ->join('group b', 'a.group_id = b.group_id')
            ->join('program c', 'a.program_id = c.program_id')
            ->where('a.program_id', $program_id)
            ->orderBy('b.name', 'ASC')
            ->get()
            ->getResultArray();

        return $result;
    }
}