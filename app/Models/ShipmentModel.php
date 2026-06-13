<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentModel extends Model
{
    protected $table            = 'shipment';
    protected $primaryKey       = 'shipment_id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'shipment_number',
        'purchase_order_id',
        'supplier_id',
        'buyer_id',
        'driver_id',
        'vehicle_id',
        'departure_at',
        'arrival_at',
        'status_id',
        'created_date',
        'modified_date',
        'created_by',
        'modified_by'
    ];

    public function generateShipmentNumber()
    {
        $monthRoman = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII'
        ];

        $month = date('n');
        $year  = date('y');

        $prefix = '/SJ/' . $monthRoman[$month] . '/' . $year;

        $last = $this->select('shipment_number')
            ->like('shipment_number', $prefix, 'before')
            ->orderBy('shipment_id', 'DESC')
            ->first();

        if ($last) {

            $parts = explode('/', $last['shipment_number']);
            $number = (int)$parts[0] + 1;

        } else {

            $number = 1;

        }

        return sprintf('%03d', $number) . $prefix;
    }
}