<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Salestarget extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'sales_target_id' => [
				'type'           => 'INT',
				'constraint'     => 11,
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'username' => [
				'type'       => 'VARCHAR',
				'constraint' => 100,
				'null'       => false,
			],
			'users_organization_id' => [
				'type'       => 'INT',
				'constraint' => 11,
				'null'       => false,
			],
			'target' => [
				'type'       => 'DECIMAL',
				'constraint' => '15,2',
				'null'       => false,
			],
			'created_date' => [
				'type' => 'DATETIME',
				'null' => false,
			],
			'modified_date' => [
				'type' => 'DATETIME',
				'null' => false,
			],
			'created_by' => [
				'type'       => 'INT',
				'constraint' => 11,
				'null'       => false,
			],
			'modified_by' => [
				'type'       => 'INT',
				'constraint' => 11,
				'null'       => false,
			],
		]);

		$this->forge->addKey('sales_target_id', true);

		$this->forge->addForeignKey(
			'users_organization_id',
			'usersorganization',
			'users_organization_id',
		);

		$this->forge->createTable('salestarget');
	}

	public function down()
	{
		$this->forge->dropTable('salestarget');
	}
}
