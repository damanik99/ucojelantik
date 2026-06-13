<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Salestargetmonthly extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'targetmonthly_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'sales_target_period_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'monthly' => [
                'type' => 'VARCHAR',
                'constraint' => 5,
                'null'       => false,
            ],
            'start_date' => [
                'type' => 'DATE',
            ],
            'end_date' => [
                'type' => 'DATE',
            ],
            'target_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'created_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'modified_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'modified_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
        ]);

        // Menentukan Primary Key
        $this->forge->addKey('targetmonthly_id', true);

        // Menambahkan Index untuk Foreign Key (Opsional, tapi bagus untuk performa)
        $this->forge->addKey('sales_target_period_id');

        // Membuat Tabel
        $this->forge->createTable('salestargetmonthly');
    }

    public function down()
    {
        // Menghapus Tabel jika migrasi di-rollback
        $this->forge->dropTable('salestargetmonthly');
    }
}
