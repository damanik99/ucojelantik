<?php namespace App\Controllers\Api;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ActivityModel;
use App\Models\OrganizationModel;
use App\Models\NotificationModel;
use App\Models\CustomModel;
use App\Models\TrainingHeaderModel;
use App\Models\TrainingDetailModel;
use App\Models\SalesPersonModel;
 
class Training extends ResourceController
{
	protected $notificationModel;
    use ResponseTrait;

	public function __construct()
    {
        $this->customModel = new CustomModel();
		$this->db = \Config\Database::connect();
	}

    public function index()
    {
        $data = $this->request->getPost();
        // echo var_dump($data);exit;
        return $this->respond($data, 200);
    }

    private function _sendResponse($error_code, $body_message = '', $data = array())
    {
		if($body_message == "")
			$body_message = $this->_getStatusCodeMessage($error_code);
		else
			$body_message = $this->_getStatusCodeMessage($error_code)." - ".$body_message;
		
		if($data == "")
			$data = null;
			
		$array = array(
			'response' => $error_code, 
			'message' => $body_message, 
			'data' => $data
		);
		
		echo json_encode($array); exit;
    }

	private function _getStatusCodeMessage($status)
    {
        $codes = Array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
			308 => 'Permanent Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }

    public function _sendNotification($tokens)
	{
		$url = 'https://fcm.googleapis.com/fcm/send';
		$data = array
		(
			'type' => "COMMENT",
			'title' => "Sales Club",
			'message' => $message,
			'timeline_id' => $timeline_id
		);
		
		$fields = array
		(
			'data' => $data,
			'registration_ids' => $tokens,
		);
		
		
		$headers = array(
			'Authorization:key=AIzaSyCnGeW-lqHjvK7rsnULTyaUIghNVN_Myls',
			'Content-Type: application/json'
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);  
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$result = curl_exec($ch);     
		
		if($result === FALSE) 
		{
			$filename = 'public/'.date('Y-m-d',strtotime('now')).'.txt';
			$content = "API FCM Google ERROR : ".curl_error($ch)." \r\n";
			Yii::app()->custom->createLogFile($filename,$content);
		}
		curl_close($ch);
	}

    public function getToken($string)
    {
        $this->db = \Config\Database::connect();
        $user = $this->db->table('users')->select('*')->where('username', $string)->get()->getRow();
        if(!$user)
            $user = $this->db->table('users')->select('*')->where('email', $string)->get()->getRow();
		if($user)
		{
			if($user->token == "")
			{
				$token = md5(uniqid($string, true));
				
                $check = $this->db->table('users')->select('*')->where('token', $token)->get()->getRow();
				if($check)
					$this->getToken($string);
				else
					return $token;
			}
			else
				return $user->token;
		}
	}

    private function checkToken($token = "", $program_id = "")
    {
        $this->db = \Config\Database::connect();
        $user = $this->db->table('users')->select('*')->where('token', $token)->get()->getRow();
		$program = $this->db->table('program')->select('*')->where('program_id', $program_id)->get()->getRow();
        // echo var_dump($program);exit;
		if($token == "")
			$this->_sendResponse(401, 'Token empty', '');
		else if($program_id == "")
			$this->_sendResponse(401, 'Program ID empty', '');
		else if($user == NULL)
			$this->_sendResponse(401, 'Token invalid', '');
		else if($user->active == 0)
			$this->_sendResponse(401, 'Token expired', '');
		else if($program == NULL)
			$this->_sendResponse(401, 'Program ID invalid', '');
		else
		{
			$result = array(
				'users_id' => $user->users_id, 
				'program_id' => $program->program_id
			);
			return $result;
		}
	}

    private function checkId($id = "", $controller)
    {
		$this->db = \Config\Database::connect();
		if($id != "")
		{
			if ($controller == "usersorganization") 
			{
				$ctr = $this->db->table($controller)->select('*')->where('users_organization_id', $id)->get()->getRow();
			}
			else
			{
				$ctr = $this->db->table($controller)->select('*')->where($controller.'_id', $id)->get()->getRow();
			}
            
			if($ctr)
			{
                $result = [
                    'id' => $id, 
				];

                return $result;
			}
			else
				$this->_sendResponse(400, $controller.'_id not found', '');
		}
		else
			$this->_sendResponse(400, $controller.'_id is empty', '');
	}

    public function postSales()
    {
        header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);	
		
		$token = "";
		$program_id = "";
		$error = "";
		
		$model = new salesPersonModel();
		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value;
			
			if($var == "program_id")
				$program_id = $value;

			if($var == "mobile_phone")
				$mobile_phone = $value;

			if($var == "full_name")
				$full_name = $value;

			if($var == "organization_id")
				$organization_id = $value;
		}

		$check = $this->checkToken($token, $program_id);

		$checkId = $this->checkId($obj->organization_id, "usersorganization");

		// $users_organization_id = $this->db->table('usersorganization')->select('*')->where('users_organization_id', $checkId['id'])->get()->getRow();
		$sql = 'SELECT a.users_organization_id, a.`users_id`, a.`client_organization_id`, c.`organization_id`, a.status_id, a.`created_date`, 
		b.`username`, d.name AS channel,a.`status_id`, e.`name` AS `status` 
		FROM usersorganization a
		JOIN users b ON a.`users_id` = b.`users_id`
		JOIN clientorganization c ON a.`client_organization_id` = c.`client_organization_id`
		JOIN organization d ON c.`organization_id` = d.`organization_id`
		JOIN `status` e ON a.`status_id` = e.`status_id`
		WHERE a.`users_organization_id` = "'.$checkId['id'].'"';

		$query  = $this->db->query($sql);
		$users_organization_id = $query->getRow();

		$model->organization_id = $users_organization_id->organization_id;

		$existingSales = $this->db->table('salesperson')->select('*')->where('mobile_phone', $mobile_phone)->get()->getResultArray();
		// echo var_dump($users_organization_id->users_organization_id);exit;
		if (!empty($existingSales)) 
		{
			foreach ($existingSales as $sales)
			{
				// echo var_dump($sales['organization_id'] == $model->organization_id);exit;
				if($sales['full_name'] == $full_name && $sales['organization_id'] == $model->organization_id)
				{
					$this->_sendResponse(500, 'Data sales sudah ada', '');
					// Jika nomor telepon, nama lengkap, dan nama toko sama, artinya gagal
				} 
				elseif ($sales['full_name'] != $full_name && $sales['organization_id'] != $model->organization_id) 
				{
					$this->_sendResponse(500, 'No telepon sudah ada', '');
					// Jika nomor telepon sama, tetapi nama lengkap dan nama toko berbeda
				}
				elseif ($sales['full_name'] == $full_name && $sales['organization_id'] != $model->organization_id) 
				{
					$model->update($sales['sales_person_id'], ['organization_id' => $model->organization_id]);
					// Jika nama lengkap dan nomor telepon sama, tapi nama toko berbeda, update nama toko
					$this->_sendResponse(200, '', '');
				}
			}
		}

		$status = $this->db->table('status')->select('*')->where('code', "SALS_ACTIVE")->get()->getRow();
		$model->status_id = $status->status_id;
		$model->gender = "Other";

		$saveSls = [
			'organization_id' => $model->organization_id,
			'status_id' => $model->status_id,
			'full_name' => $obj->full_name,
			'gender' => $model->gender,
			'email' => $obj->email,
			'mobile_phone' => $obj->mobile_phone,
			'created_date' => date("Y-m-d H:i:s"),
			'modified_date' => date("Y-m-d H:i:s"),
			'created_by' => $check["users_id"],
			'modified_by' => $check["users_id"]
		];

		if ($this->db->table('salesperson')->insert($saveSls)) 
		{
			$this->_sendResponse(200, '', '');
		}
		else
		{
			$this->_sendResponse(408, '', '');
		}
    }

    public function getTraining()
    {
        header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);	
		
		$token = "";
		$program_id = "";
		$error = "";
		
		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value;
			
			if($var == "program_id")
				$program_id = $value;	
		}

		$page = $obj->page ?? 1;
		$perPage = 10;
        
		$check = $this->checkToken($token, $program_id);

        $sql = 'SELECT a.`training_detail_id`, g.name AS channel, d.mobile_phone, e.name, a.`created_date`, d.`full_name` AS sales_person FROM trainingdetail a
				JOIN trainingheader b ON a.`training_header_id` = b.`training_header_id`
				LEFT JOIN usersorganization c ON a.`users_organization_id` = c.`users_organization_id`
				JOIN salesperson d ON a.`sales_person_id` = d.`sales_person_id`
				JOIN trainingmodule e ON a.`training_module_id` = e.`training_module_id`
				LEFT JOIN clientorganization f ON c.`client_organization_id` = f.`client_organization_id`
				LEFT JOIN organization g ON f.`organization_id` = g.`organization_id`
				WHERE b.program_id = "'.$check['program_id'].'" ';
		
		$autorize = $this->db->table('usersgroupprogram')->select('*')->where('users_id', $check['users_id'])->where('program_id', $check['program_id'])->get()->getRow();
				
		if($autorize)
		{
			if($autorize->data_level == "low")
				$sql .= ' AND a.created_by ="'.$check['users_id'].'"';
		}
		
		$sql .= ' ORDER BY a.created_date DESC, a.`training_detail_id` DESC';
		$sql .= ' LIMIT '.$perPage.' OFFSET '.(($page - 1) * $perPage);
		// var_dump(($page - 1) * $perPage);exit;
		
		$query  = $this->db->query($sql);
		$result_query = $query->getResultArray();
		
		$sqlx = 'SELECT COUNT(*) AS counts FROM trainingdetail a
				JOIN trainingheader b ON a.`training_detail_id` = b.`training_header_id`
				LEFT JOIN usersorganization c ON a.`users_organization_id` = c.`users_organization_id`
				JOIN salesperson d ON a.`sales_person_id` = d.`sales_person_id`
				JOIN trainingmodule e ON a.`training_module_id` = e.`training_module_id`
				LEFT JOIN clientorganization f ON c.`client_organization_id` = f.`client_organization_id`
				LEFT JOIN organization g ON f.`organization_id` = g.`organization_id`
				WHERE b.program_id = "'.$check['program_id'].'" ';

		$sqlsx    = $this->db->query($sqlx);
		$count_page = $sqlsx->getRow()->counts;
		// $counts = $count_page->counts;

		$totalPages = ceil($count_page / $perPage);

		$dataArr = array();
		foreach ($result_query as $models)
		{
			$dataArr[] = array(
				'training_detail_id' => $models['training_detail_id'],
				'channel_name' => $models['channel'],
				'mobile_phone' => $models['mobile_phone'],
				'module_name' => $models['name'],
				'training_date' => $models['created_date'],
				'sales_name' => $models['sales_person']
			);
		}

		$arrData = array(
			'total_page' => $totalPages,
			'training_list' => $dataArr,
		);
		
		$this->_sendResponse(200, '', $arrData);
    }

	public function postTraining()
	{
		header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);	
		
		$token = "";
		$program_id = "";
		$modules = "";
		$audiences = "";
		$error = "";
		
		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value;
			
			if($var == "program_id")
				$program_id = $value;	
				
			if($var == "module_name")
				$modules = $value;
			
			if($var == "audience_name")
				$audiences = $value;	
		}
		
		$check = $this->checkToken($token, $program_id);
		
		$model = new TrainingHeaderModel;

		foreach($obj as $var=>$value) 
		{
			if($model->hasAttribute($var)) 
                $model->$var = $value;
				
			if($var == "remark")
				$model->description = $value;	
		}

		$users = $this->db->table('users')->select('*')->where('users_id', $check['users_id'])->get()->getRow();
		$program = $this->db->table('program')->select('*')->where('program_id', $check['program_id'])->get()->getRow();
		
		$model->code = $this->customModel->setCounterNumber('timeline', 'code', 'TML');
		$model->start = date("Y-m-d H:i:s");
		$model->finish = date("Y-m-d H:i:s");
		$model->note5 = $users->username;
		$model->total_trainer = 1;
		$note1 = '';

		if(count((array)$modules) > 0)
		{
			for($i=0; $i<count((array)$modules); $i++)
			{
				$mod  = $this->db->table('trainingmodule')->select('*')->where('training_module_id', $modules[$i])->get()->getRow();
				
				if($modules[$i] == "")
				{
					$note1 .= 'Module Name : '.$mod->name;
				}
				else
				{
					$note1 .= ', '.$mod->name;
				}
			}
			
			$model->note1 = $note1;
		}
		else
			$this->_sendResponse(401, "Module Name can't empty", '');
		
		$urlimgName = "";
		if($model->picture != "")
		{
			$imgName = $model->code.'.jpg';
			$imageData = base64_decode($obj->picture);
			
			$uploadPath = FCPATH .'upload/image/training/';
			
			helper('filesystem');
			write_file($uploadPath . $imgName, $imageData);

			$urlimgName = base_url()."/upload/image/training/".$imgName;
		}

		$saveTrn = [
			'program_id' => $model->program_id,
			'training_type' => $model->training_type,
			'venue' => $model->venue,
			'code' => $model->code,
			'start' => $model->start,
			'finish' => $model->finish,
			'picture' => $urlimgName,
			'total_audience' => $model->total_audience,
			'total_trainer' => $model->module_name[0],
			'description' => $model->description,
			'created_date' => date("Y-m-d H:i:s"),
			'modified_date' => date("Y-m-d H:i:s"),
			'created_by' => $check["users_id"],
			'modified_by' => $check["users_id"]
		];

		if($this->db->table('trainingheader')->insert($saveTrn)) 
		{
			$training_id   = $this->db->insertID();
			
			if($audiences[0]->sales_person_id > 0)
			{
				for($i=0; $i<count((array)$modules); $i++)
				{
					foreach($audiences as $audience)
					{
						$trainingDetail = new TrainingDetailModel;

						$refreshCheck = $this->db->table('trainingdetail')
						->select('*')
						->where('sales_person_id', $audience->sales_person_id)
						->where('training_module_id', $modules[$i])
						->get()->getRow();
						
						if($refreshCheck)
						{
							$trainingDetail->refresh = 1;
						}
						else
						{
							$trainingDetail->refresh = 0;
						}
						
						$code = 'TRD'.date("Ymd"). rand(100, 999);
						
						$saveTnd = [
							'training_header_id' => $training_id,
							'code' => $code,
							'training_module_id' => $modules[$i],
							'sales_person_id' => $audience->sales_person_id,
							'refresh' => $trainingDetail->refresh,
							'created_date' => date("Y-m-d H:i:s"),
							'modified_date' => date("Y-m-d H:i:s"),
							'created_by' => $check["users_id"],
							'modified_by' => $check["users_id"]
						];
						// echo var_dump($saveTnd);exit;
						if($this->db->table('trainingdetail')->insert($saveTnd))
						{
							$this->_sendResponse(200, 'Berhasil', '');
						}
					}
				}
			}
			else
				$this->_sendResponse(401, "Audience Name can't empty", '');
			
		}
		else
		{
			$this->_sendResponse(500, 'error', '');
		}
	}
}