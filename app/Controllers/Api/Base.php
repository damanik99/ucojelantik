<?php namespace App\Controllers\Api;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\LoginModel;
use App\Models\LoginLogModel;
use App\Models\UsersModel;
use App\Models\NotificationModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
// use App\Models\ProductModel;
 
class Base extends ResourceController
{
	protected $loginModel;
	protected $userModel;
	protected $notificationModel;
    use ResponseTrait;

	public function __construct()
    {
		$this->db = \Config\Database::connect();
	}

    public function index()
    {
        $data = $this->request->getPost();
        // echo var_dump($data);exit;
        return $this->respond($data, 200);
    }

	public function validatePassword($password, $username)
	{
		$this->userModel  = new LoginModel();
		$password = trim($password);
		$user = $this->userModel->getUser($username);
		// echo var_dump($user);exit;
		if(password_verify($password, $user['password2']))
		{
			return $user['password2'];
		}
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
        // echo var_dump($user);exit;
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
				// return $this->respond($result);
                return $result;
			}
			else
				$this->_sendResponse(400, $controller.'_id not found', '');
		}
		else
			$this->_sendResponse(400, $controller.'_id is empty', '');
	}

    public function checkPrivilege($users_id, $program_id, $page, $action)
	{
        $this->db = \Config\Database::connect();
        $query = 'SELECT e.name AS group_name FROM privilege a JOIN usersgroupprogram b ON a.group_id = b.group_id
                  JOIN `page` c ON a.page_id = c.page_id 
                  JOIN `action` d ON a.action_id = d.action_id
                  JOIN `group` e ON b.group_id = e.group_id
                  WHERE b.users_id = '.$users_id.' AND b.program_id = '.$program_id.' AND c.name = "'.$page.'" AND d.name = "'.$action.'"';
		
        $sql     = $this->db->query($query);
        $check = $sql->getResultArray();
        
        $validate = 0; 
		
		if(count($check) >= 1)
        {
			$validate = 1;
        }
		
        $querys = 'SELECT a.users_group_program_id FROM usersgroupprogram a
                   JOIN `group` b ON a.group_id = b.group_id 
                   WHERE a.users_id = '.$users_id.' AND b.name = "ADMINISTRATOR"';
        
        $sqls     = $this->db->query($querys);
        $checks = $sqls->getResultArray();
		// echo var_dump($checks);exit;
		if(count($checks) >= 1)
			$validate = 1;
			
		return $validate;
	}

    public function postLogin()
    {
		$this->db = \Config\Database::connect();
        header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);

		$apikey = "";
		$imei = ""; 
		$merk_device = "";
		$type_device = "";
		$token_key = "";
		$error = "";

		foreach($obj as $var=>$value) 
		{
			if($var == "apikey")
				$apikey = $value;
			
			if($var == "imei")
				$imei = $value;	
			
			if($var == "merk_device")
				$merk_device = $value;	
			
			if($var == "type_device")
				$type_device = $value;	
			
			if($var == "deviceToken")
				$token_key = $value;

			if($var == "username")
				$username = $value;

			if($var == "password")
				$password = $value;
		}

		$this->loginModel  = new LoginModel();
		$psw = $this->validatePassword($password, $username);
		// echo var_dump($psw);exit;
		$user = $this->db->table('users')->select('*')->where('LOWER(username)', strtolower($username))->get()->getRow();
		$base64 = base64_encode($password);
		
		if(!$user)
		{
			$user = $this->db->table('users')->select('*')->where('LOWER(email)', strtolower($username))->get()->getRow();
		}

		if($apikey == "") 
		{
			$this->_sendResponse(401, 'API KEY is empty', '');
		}
		else if($apikey !== "jMlMcPYpGYU2IZP4p8INZv1ypYjWY29jIF23yAyyh-yWZQZx9l")
		{
			$this->_sendResponse(401, 'API KEY is invalid', '');
		}
		else if($user===null) 
		{
			$this->_sendResponse(401, 'Username is invalid', ''); 
		} 
		else if($user->active == 0) 
		{
			$this->_sendResponse(401, 'Username is inactive', ''); 
		} 
		else if(!$this->validatePassword($password, $username)) 
		{
			$this->_sendResponse(401, 'Password is invalid', '');
		}
		else if($token_key == "")
		{
			$this->_sendResponse(401, "Token can't empty", '');
		}
		else
		{
			$token = $this->getToken($username);
			
			$users_id = [
				'token' => $token,
				'token_key' => $token_key
			];
			
			if($this->db->table('users')->where('users_id', $user->users_id)->update($users_id)) 
			{
				$log = new LoginLogModel;

				$loginLog = [
					'users_id' => $user->users_id,
					'browser' => $merk_device.' '.$type_device,
					'ip' => $imei,
					'created_date' => date("Y-m-d H:i:s"),
					'modified_date' => date("Y-m-d H:i:s"),
					'created_by' => $user->users_id,
					'modified_by' => $user->users_id,
				];
				
				if($this->db->table('loginlog')->insert($loginLog)) 
				{
					$usrGroup = $this->db->table('usersgroupprogram')->select('*')->where('users_id', $user->users_id)->orderBy('users_group_program_id', 'DESC')->get()->getRow();
					$group = $this->db->table('group')->select('*')->where('group_id', $usrGroup->group_id)->get()->getRow();
					$field = array();
					$value = array();
					$curr_program = array();

					$status = '';
					$statusModel = $this->db->table('status')->select('*')->where('code', 'PRGM_CLOSE')->get()->getRow();

					if($statusModel) 
					{
						$status = $statusModel->status_id;
					}
					
					if(strtolower($group->name) == 'administrator' || strtolower($group->name) == 'executive')
					{
						$sql    = "SELECT * FROM program WHERE `status_id`<>".$status." ORDER BY name ASC";
						$query  = $this->db->query($sql);
						$_model = $query->getResultArray();
						
						foreach($_model as $data)
						{
							$field[] = $data['program_id'];
							$values = [
								'program_id' => $data['program_id'],
								'name' => $data['name']
							];
						}
					}
					else 
					{
						$sql    = "SELECT * FROM usersgroupprogram WHERE users_id =".$user->users_id;
						$query  = $this->db->query($sql);
						$_models = $query->getResultArray();
						foreach($_models as $data)
						{
							if(is_null($data['program_id']))
							{
								$this->_sendResponse(204, '', '');
							}
							else
							{
								$program = $this->db->table('program')->select('*')->where('program_id', $data['program_id'])->get()->getRow();
								
								if($program->status_id != $status)
								{
									$field[] = $data['program_id'];
									$values[] = array(
										'program_id' => $data['program_id'],
										'name' => $program->name
									);
								}
							}
						}
					}
					
					if(count($field)<=0)
						$this->_sendResponse(204, '', '');
					else
					{	
						
						if(strtolower($group->name) == 'administrator')
						{
							$sql    = "SELECT * FROM program ORDER BY program_id DESC";
							$query  = $this->db->query($sql);
							$st_prog = $query->getRow();
							
							$curr_program = [
								'program_id' => $st_prog->program_id,
								'name' => $st_prog->name
							];
						}
						else
						{
							$usrprg = $this->db->table('usersgroupprogram')->select('*')->where('users_id', $user->users_id)->orderBy('users_group_program_id', 'DESC')->get()->getRow();
							$prgm = $this->db->table('program')->select('*')->where('program_id', $usrprg->program_id)->get()->getRow();
							
							$curr_program = [
								'program_id' => $usrprg->program_id,
								'name' => $prgm->name
							];
						}
					}
					
					$photo = "";
					
					if($user->picture != "" || $user->picture != NULL)
					{
						
						$photo = base_url().'/'.$user->picture;
					}

					$usr = array(
						'token' => $token,
						'full_name' => $user->first_name,
						'phone' => $user->phone,
						'email' => $user->email,
						'photo' => $photo,
						'level' => $usrGroup->data_level,
						'current_program' => $curr_program,
						'list_program'=> $values
					);
									
					$this->_sendResponse(200, '', $usr);
				}
			}
			else
			{
				$this->_sendResponse(408, '', '');
			}
			$this->_sendResponse(408, $ex->getMessage(), '');
		}
    }
 
	public function getConfig()
	{
		header("http_content-type: application/json");
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
		$check = $this->checkToken($token, $program_id);

		$program = $this->db->table('program')->select('*')->where('program_id', $check['program_id'])->get()->getRow();
		$client = $this->db->table('client')->select('*')->where('client_id', $program->client_id)->get()->getRow();
		
		$tracking_config[] = array(
			'time_start' => $program->time_start,
			'time_stop' => $program->time_stop,
			'frequency' => $program->frequency,
			'include_saturday' => $program->include_saturday,
			'include_sunday' => $program->include_sunday,
		);

		$level = "high";
		$usrGroup = $this->db->table('usersgroupprogram')->select('*')->where('users_id', $check['users_id'])->orderBy('users_group_program_id', 'DESC')->get()->getRow();
		$group = $this->db->table('group')->select('*')->where('group_id', $usrGroup->group_id)->get()->getRow();
		
		if($usrGroup)
		{
			if(strtolower($group->name) == 'administrator' || strtolower($group->name) == 'executive')
				$level = "high";
			else
				$level = $usrGroup->data_level;
		}

		$menuItem = array();
		if($this->checkPrivilege($check['users_id'], $check['program_id'], 'Attendance','admin') == 1) {
			$menuItem[] = "Attendance";
		}

		if($this->checkPrivilege($check['users_id'], $check['program_id'], 'Activity','admin') == 1) 
			$menuItem[] = "Share";
				
		if($this->checkPrivilege($check['users_id'], $check['program_id'], 'Delivery','admin') == 1) 
			$menuItem[] = "Receive";
			
		if($this->checkPrivilege($check['users_id'], $check['program_id'], 'Distribution','admin') == 1) 
			$menuItem[] = "Distribution";
			
		if($this->checkPrivilege($check['users_id'], $check['program_id'], 'Display','admin') == 1) 
			$menuItem[] = "Maintenance";
			
		if($this->checkPrivilege($check['users_id'], $check['program_id'], 'Selling','admin') == 1) 
			$menuItem[] = "Selling";
			
		if($this->checkPrivilege($check['users_id'], $check['program_id'], 'Training','admin') == 1) 
			$menuItem[] = "Training";
			
		if($this->checkPrivilege($check['users_id'], $check['program_id'], 'Order','admin') == 1) 
			$menuItem[] = "Order";
			
		if($this->checkPrivilege($check['users_id'], $check['program_id'], 'Stock','admin') == 1) 
			$menuItem[] = "Stock";
			
		if($this->checkPrivilege($check['users_id'], $check['program_id'], 'Roadmap','admin') == 1) 
			$menuItem[] = "Schedule";

		if($this->checkPrivilege($check['users_id'], $check['program_id'], 'Dynamic','admin') == 1) 
			$menuItem[] = "Dynamic";

		$link = "";
		
		if($program)
			$link = $client->picture;
		else
			$link = 'http://surfgold.co.id/loyalty/themes/adminLTE/dist/image/logo_tf.png';
		
		$arrData = [
			'tracking_config' => $tracking_config,
			'menus' => $menuItem,
			'logo' => $link,
			'level'=> $level
		];

		$this->_sendResponse(200, '', $arrData);

	}

	public function getNotification()
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

		$check = $this->checkToken($token, $program_id);

		$sql = "SELECT COUNT(*) AS count FROM notification a
				JOIN program b ON a.program_id = b.program_id
				JOIN users c ON a.users_id = c.users_id 
				ORDER BY a.created_date DESC";

		$query  = $this->db->query($sql);
		$criteria = $query->getRow();
		
		$notification = new NotificationModel();
		$page = $notification->paginate(10);

		$data = $notification->findAll();

		foreach ($data as $notification)
		{
			$arr_notification = [
				'notification_id' => $notification['notification_id'],
				'timeline_id' => $notification['timeline_id'],
				'description' => $notification['description'],
				'read' => $notification['read'],
				'active' => $notification['active'],
				'created_date' => $notification['created_date']
			];
		}
		
		$arrData = array(
			'total_page' => intval(round($criteria->count / 10)),
			'notification_list' => $arr_notification,
		);

		$this->_sendResponse(200, '', $arrData);
	}

	public function listChannel()
	{
		header("http_content-type: application/json");
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

		$check = $this->checkToken($token, $program_id);

		$program = $this->db->table('program')->select('*')->where('program_id', $check['program_id'])->get()->getRow();
		
		if($program->module_redemption == 1)
		{
			$sql = "SELECT * FROM redemption a 
					JOIN member b ON a.member_id = b.member_id
					JOIN `status` c ON a.status_id = c.status_id
					WHERE a.pod IS NULL AND a.delivery_order <> '' 
					AND a.delivery_order IS NOT NULL AND b.program_id =".$check['program_id']." 
					GROUP BY a.delivery_order";

			$query  = $this->db->query($sql);
			$redemption = $query->getResultArray();

			foreach ($redemption as $redemptions) 
			{
				$channel[] = array(
					'users_organization_id'=>$redemptions['delivery_order'],
					'name'=>$redemptions['delivery_order'],
				);
			}
		}
		else
		{
			$statusUserOrg = $this->db->table('status')->select('*')->where('code', 'UORG_ACTIVE')->get()->getRow();
			
			$sql = "SELECT * FROM usersorganization a
					LEFT JOIN clientorganization b ON a.`client_organization_id` = b.`client_organization_id`
					LEFT JOIN organization c ON b.`organization_id` = c.`organization_id`
					WHERE a.status_id = '".$statusUserOrg->status_id."' AND a.`users_id` = '".$check['users_id']."' ORDER BY c.name ASC";

			$query  = $this->db->query($sql);
			$resultChannel = $query->getResultArray();
			$org_id = $query->getRow();
			
			$channel = array();
			foreach($resultChannel as $resultChannels) 
			{
				// echo var_dump($resultChannels['name']);exit;
				if($resultChannels['client_id'] == $program->client_id)
				{
					$orgn = $this->db->table('organization')->select('*')->where('organization_id', $org_id->organization_id)->get()->getRow();
					
					$channel[] = array(
						'users_organization_id'=>$resultChannels['users_organization_id'],
						'name'=>$resultChannels['name']
					);
				}
			}
		}

		$attTypes = $this->db->table('attendancetype')->select('*')->where('program_id', $check['program_id'])->where('active', '1')->get()->getResultArray();
		
		foreach($attTypes as $attType)
		{
			$subject[] = array(
				'value' => $attType['attendance_type_id'],
				'name' => $attType['name']
			);
		}
		
		$arrData = array(
			'channel_list' => $channel,
			'subject_list' => $subject,
		);
		
		$this->_sendResponse(200, '', $arrData);
	}

	public function listSellingType()
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
		$check = $this->checkToken($token, $program_id);
		
		// $model = SellingType::model()->findAll('program_id=:program_id',array(':program_id'=>$check['program_id']));
		$model = $this->db->table('sellingtype')->select('*')->where('program_id', $check['program_id'])->get()->getResultArray();
		foreach($model as $data)
		{
			$values[] = array(
				'selling_type_id' => $data['selling_type_id'],
				'name' => $data['name']
			);
		}
			
		$this->_sendResponse(200, '', $values);
	}

	public function listProduct()
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
		$check = $this->checkToken($token, $program_id);
		
		$sql = "SELECT b.name AS item, a.program_item_id, c.name AS category FROM programitem a
				JOIN item b ON a.item_id = b.item_id 
				JOIN itemcategory c ON b.item_category_id = c.item_category_id
				WHERE c.name = 'Product' AND a.program_id = ".$check['program_id']." ORDER BY b.name ASC";

		$query  = $this->db->query($sql);
		$models = $query->getResultArray();
		$suggest = array();
		foreach($models as $model) 
		{
			$suggest[] = array(
				'program_product_id'=>$model['program_item_id'],
				'name'=>$model['item'], 
			);
		}
		$this->_sendResponse(200, '', $suggest);
	}

	public function listModule()
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
		$check = $this->checkToken($token, $program_id);
		
		$models = $this->db->table('trainingmodule')->select('*')->where('program_id', $check['program_id'])->orderBy('name', 'ASC')->get()->getResultArray();
		$suggest = array();
		foreach($models as $model) 
		{
			$suggest[] = array(
				'training_module_id'=>$model['training_module_id'],
				'name'=>$model['name'], 
			);
		}
		$this->_sendResponse(200, '', $suggest);
	}

	public function listSales()
	{
		header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);	
		
		$token = "";
		$program_id = "";
		$organization_id = "";
		$error = "";
		
		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value;
			
			if($var == "program_id")
				$program_id = $value;	
				
			if($var == "organization_id")
				$organization_id = $value;	
		}
		$check = $this->checkToken($token, $program_id);
		$checkId = $this->checkId($organization_id, "usersorganization");

		$sql = 'SELECT a.`users_id`, a.`client_organization_id`, c.`organization_id`, a.status_id, a.`created_date`, 
		b.`username`, d.name AS channel,a.`status_id`, e.`name` AS `status` 
		FROM usersorganization a
		JOIN users b ON a.`users_id` = b.`users_id`
		JOIN clientorganization c ON a.`client_organization_id` = c.`client_organization_id`
		JOIN organization d ON c.`organization_id` = d.`organization_id`
		JOIN `status` e ON a.`status_id` = e.`status_id`
		WHERE a.`users_organization_id` = "'.$checkId['id'].'"';

		$query  = $this->db->query($sql);
		$usersOrganization = $query->getRow();

		$sqls = 'SELECT sales_person_id, b.name AS organization, a.status_id, full_name, gender FROM salesperson a
		LEFT JOIN organization b ON a.organization_id = b.organization_id 
		WHERE a.organization_id ="'.$usersOrganization->organization_id.'" AND a.status_id = "46" ORDER BY full_name ASC';

		$query  = $this->db->query($sqls);
		$models = $query->getResultArray();
		$suggest = array();
		foreach($models as $model) 
		{
			$suggest[] = array(
				'sales_person_id'=>$model['sales_person_id'],
				'name'=>$model['organization']." - ".$model['full_name'],
			);
		}
		$this->_sendResponse(200, '', $suggest);
	}

	public function listCity()
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
		$check = $this->checkToken($token, $program_id);
		
		$sql = "SELECT a.city_id, a.name AS city, b.name AS state, c.name AS country FROM city a
				JOIN state b ON a.state_id = b.state_id
				JOIN country c ON b.country_id = c.country_id 
				ORDER BY a.name ASC";

		$query  = $this->db->query($sql);
		$models = $query->getResultArray();
				
		$suggest = array();
		foreach($models as $model) 
		{
			$suggest = [
				'city_id'=>$model['city_id'],
				'name'=>$model['city']." - ".$model['state']." [ ".$model['country']." ] ", 
			];
		}
		$this->_sendResponse(200, '', $suggest);
	}

	public function getLogo()
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
		
		$check = $this->checkToken($token, $program_id);
		
		$link = array();
		// echo var_dump($link);exit;
		if($program_id != "")
		{
			$program = $this->db->table('program')->select('*')->where('program_id', $check['program_id'])->get()->getRow();
			// echo var_dump($program);exit;
			$client = $this->db->table('client')->select('*')->where('client_id', $program->client_id)->get()->getRow();
			$link[] = $client->picture;
		}
		else
			$link[] = 'http://surfgold.co.id/loyalty/themes/adminLTE/dist/image/logo_tf.png';
		
		$this->_sendResponse(200, '', $link);
	}

	public function listTypeShare()
	{
		header("http_content-type: application/json");
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
		$check = $this->checkToken($token, $program_id);
		
		$program = $this->db->table('program')->select('*')->where('program_id', $check['program_id'])->get()->getRow();
		$typeItem = array();
		
		$typeItem[] = "Share Status";
		$typeItem[] = "Announcement";
		
		$arrData[] = array(
			'type'=> $typeItem
		);
		
		$this->_sendResponse(200, '', $arrData);
		
	}

	public function listProgram()
	{
		
	}

	public function datadetailpoint()
    {
		header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);
		// echo var_dump($obj);exit;
		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value;
			
			if($var == "programCode")
				$programCode = $value;
		}

		$program_id = $this->db->table('program')->select('program_id')->where('code', $programCode)->get()->getRow();

		$check = $this->checkToken($token, $program_id->program_id);

		$users_id = $this->db->table('users')->select('*')->where('users_id', $check['users_id'])->get()->getRow();
		
		try 
		{
			$sql = "SELECT * FROM detailpoint WHERE member_id = '".$users_id->member_id."'";
        
			$query = $this->db->query($sql);
			$result = $query->getResultArray();

			$dataArr = array();

			foreach ($result as $models)
			{
				$dataArr[] = [
					'member_id' => $models['member_id'],
					'point' => $models['point'],
					'datestart' => $models['datestart'],
					'dateend' => $models['dateend'],
					'note1' => $models['note1'],
					'note2' => $models['note2'],
					'created_date' => $models['created_date'],
					'modified_date' => $models['modified_date'],				
				];
			}

			$this->_sendResponse(200, '', $dataArr);
		} 
		catch (Exception $ex) 
		{  
			$transaction->rollback();
			$this->_sendResponse(408, $ex->getMessage(), '');
		}
    }

	public function totalpoint()
	{
		header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);
		// echo var_dump($obj);exit;
		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value;
			
			if($var == "programCode")
				$programCode = $value;
		}

		$program_id = $this->db->table('program')->select('program_id')->where('code', $programCode)->get()->getRow();

		$check = $this->checkToken($token, $program_id->program_id);

		$users_id = $this->db->table('users')->select('*')->where('users_id', $check['users_id'])->get()->getRow();
		
		try 
		{
			$sql  = "SELECT SUM(`point`) AS `point` FROM detailpoint a
			JOIN member b ON a.`member_id` = b.`member_id`
			JOIN users c ON b.`member_id` = c.`member_id`
			WHERE c.users_id = '".$check['users_id']."' AND a.note1='' AND dateend > CURDATE()";
			// echo var_dump($sql);exit;
			$query = $this->db->query($sql);
			$result = $query->getResultArray();
			// echo var_dump($result);exit;
			$dataArr = array();

			foreach ($result as $models)
			{
				$dataArr[] = [
					'point' => $models['point'],
				];
			}

			$this->_sendResponse(200, '', $dataArr);
		} 
		catch (Exception $ex)
		{
			$transaction->rollback();
			$this->_sendResponse(408, $ex->getMessage(), '');
		}
	}
}