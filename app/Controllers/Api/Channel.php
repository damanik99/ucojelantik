<?php namespace App\Controllers\Api;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ActivityModel;
use App\Models\OrganizationModel;
use App\Models\NotificationModel;
use App\Models\CustomModel;
 
class Channel extends ResourceController
{
	protected $notificationModel;
    use ResponseTrait;

	public function __construct()
    {
        $this->customModel = new CustomModel();
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

                return $result;
			}
			else
				$this->_sendResponse(400, $controller.'_id not found', '');
		}
		else
			$this->_sendResponse(400, $controller.'_id is empty', '');
	}

	private function hashAttribute($var)
    {
        // Hash the attribute using SHA-256 algorithm (you can choose a different algorithm)
        return hash('sha256', $var);
    }

    public function postChannel()
    {
        header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);	
		
		$token = "";
		$program_id = "";
		$error = "";

		$model = new organizationModel; 

		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value;
			
			if($var == "program_id")
				$program_id = $value;	
		}
		$check = $this->checkToken($token, $program_id);
		$program = $this->db->table('program')->select('*')->where('program_id', $check['program_id'])->get()->getRow();
		
		$status = $this->db->table('status')->select('*')->where('code', 'ORGZ_OPEN')->get()->getRow();
		$model->code = $this->customModel->setIdRandomString('organization', 'code', 'ORGZ', 8, 'String');
		
		$model->status_id = $status->status_id;
		$city_id = $this->db->table('city')->select('*')->where('name', $obj->city)->get()->getRow();
		$city = $this->db->table('city')->select('*')->where('name', 'None')->get()->getRow();

		if($city_id == "")
			$model->city_id = $city->city_id;
		else
			$model->city_id = $city_id->city_id;

		if($obj->picture != "")
		{
			/** server **/
			$url_segment = explode("/", Yii::app()->basePath);
			$patd = $url_segment[0]."/".$url_segment[1]."/".$url_segment[2]."/".$url_segment[3].Yii::app()->baseUrl;
			
			/** localhost **/
			// $url_segment = explode("\\", Yii::app()->basePath);
			// $patd = $url_segment[0]."/".$url_segment[1]."/".$url_segment[2].Yii::app()->baseUrl; 

			$uploads_dir = '../script_apps/public/upload/channel/';
			$ifp = fopen($uploads_dir.$model->code.".jpg", "wb"); 
			fwrite($ifp, base64_decode($model->picture)); 
			fclose($ifp); 
			
			if(is_readable($patd."/".$uploads_dir.$model->code.".jpg") == true)
				$model->picture = "http://www.surfgold.co.id".Yii::app()->baseUrl."/".$uploads_dir.$model->code.".jpg";
			else
				$this->_sendResponse(408, 'Failed save photo', '');
		}

		$saveChannel = [
			'status_id' => $status->status_id,
			'city_id' => $city_id->city_id,
			'code' => $model->code,
			'name' => $obj->name,
			'managed' => '1',
			'type' => 'Toko',
			'address' => $obj->address,
			'created_date' => date("Y-m-d H:i:s"),
			'modified_date' => date("Y-m-d H:i:s"),
			'created_by' => $check["users_id"],
			'modified_date' => $check["users_id"]
		];

		if($this->db->table('organization')->insert($saveChannel)) 
		{
			
			$channel_id   = $this->db->insertID();
			$saveClnt = [
				'client_id' => $program->client_id,
				'organization_id' => $channel_id,
				'code' => $model->code,
				'name' => $obj->name,
				'created_date' => date("Y-m-d H:i:s"),
				'modified_date' => date("Y-m-d H:i:s"),
				'created_by' => $check["users_id"],
				'modified_by' => $check["users_id"]
			];
			
			$client_organization_id   = $this->db->insertID();

			if($this->db->table('clientorganization')->insert($saveClnt)) 
			{
				$statusUserOrg = $this->db->table('status')->select('*')->where('code', 'UORG_ACTIVE')->get()->getRow();
				
				$saveUsrOrg = [
					'users_id' => $check['users_id'],
					'client_organization_id' => $client_organization_id,
					'status_id' => $statusUserOrg->status_id,
					'created_date' => date("Y-m-d H:i:s"),
					'modified_date' => date("Y-m-d H:i:s"),
					'created_by' => $check["users_id"],
					'modified_by' => $check["users_id"]
				];

				if($this->db->table('usersorganization')->insert($saveUsrOrg))
				{
					$this->_sendResponse(200, '', '');
				}
			}
			else
			{
				$this->_sendResponse(500, 'ClientOrganization Not Save', '');
			}
		}
		else
		{
			$this->_sendResponse(500, 'Channel Not Save', '');
		}
    }

}