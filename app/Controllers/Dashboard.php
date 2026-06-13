<?php namespace App\Controllers;

use CodeIgniter\I18n\Time;
use App\Models\UsersGroupProgramModel;
use App\Models\DashboardModel;

use DateTime;

class DashBoard extends BaseController
{
    protected UsersGroupProgramModel $usersGroupProgram;

    public function __construct()
    {
        helper('period');
        $session = \Config\Services::session();
        if($session->get('masuk') != TRUE ){
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Maaf! Anda tidak memiliki hak akses ke sini! </div>');
            header('Location: '.base_url('auth'));
            exit();
        }

        $this->usersGroupProgram = new UsersGroupProgramModel();

        helper(['url', 'form']);
    }

    public function index()
    {
        $programId = session()->get('program');
        $usersId = session()->get('users_id');
        $groupName = $this->usersGroupProgram->getUserProgram($usersId);
// var_dump($groupName);exit;
        if (strtolower($groupName['group_name']) == 'driver') {

            $data = [
                'title' => 'Home',
                'programId' => $programId,
            ];
            echo view('/driver/home', $data);

        } else {

            $data = [
                'title' => 'Home',
                'programId' => $programId,
            ];
            echo view('/dashboard/dashboard', $data);

        }

    }

    public function menuprivilage()
    {

        $programId      = $this->request->getPost('programid');
        $programName    = $this->request->getPost('programname');

        $is_programid = isset($programId) ? $programId : '';
        session()->set('program',$is_programid);
        session()->set('nameprogram', $programName);
        echo json_encode($programName);
	    return ;
    }

}
