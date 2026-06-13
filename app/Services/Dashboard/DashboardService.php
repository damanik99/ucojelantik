<?php

namespace App\Services\Dashboard;

use App\Models\DashboardModel;

class DashboardService
{
    protected DashboardModel $dashboardModel;

    public function __construct()
    {
        $session = \Config\Services::session();
        if ($session->get('masuk') != true) {
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Maaf! Anda tidak memiliki hak akses ke sini! </div>');
            header('Location: '.base_url('auth'));
            exit();
        }

        $this->dashboardModel = new DashboardModel();
    }

    public function getTotalusers(): array
    {
        return [
            'totalUsers' => $this->dashboardModel->getTotalUsers(),
        ];
    }

    public function getChartData(): array
    {
        return $this->dashboardModel->getMonthlySales();
    }
}
