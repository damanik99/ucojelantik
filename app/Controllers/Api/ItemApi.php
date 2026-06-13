<?php namespace App\Controllers\Api;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ActivityModel;
use App\Models\NotificationModel;
use App\Models\CustomModel;
 
class ItemApi extends ResourceController
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

	public function dataItem()
	{
		header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);

		foreach($obj as $var=>$value) 
		{
			if($var == "apikey")
				$apikey = $value;

			if($var == "token")
				$token = $value;
			
			if($var == "programCode")
				$programCode = $value;
			
		}

		$program_id = $program = $this->db->table('program')->select('program_id')->where('code', $programCode)->get()->getRow();

		$check = $this->checkToken($token, $program_id->program_id);

		if($apikey == "jMlMcPYpGYU2IZP4p8INZv1ypYjWY29jIF23yAyyh-yWZQZx9l")
		{
			$sql = "SELECT a.item_id, a.program_id, b.code AS codeItem, 
                b.`name` AS itemName, b.brand, b.model, b.picture, a.quantity, 
                a.`unit_point`, a.`point_redeem`, a.basic_price, a.`publish_price`, a.minimum_stock,
                a.created_date, a.created_by
                FROM programitem a
                JOIN item b ON a.`item_id` = b.`item_id`
                JOIN program c ON a.`program_id` = c.`program_id`
                WHERE a.program_id = '".$program_id->program_id."' ORDER BY b.name ASC";
			
			$query  = $this->db->query($sql);
			$result = $query->getResultArray();

			$sqlx = "SELECT COUNT(*) AS counts
                FROM programitem a
                JOIN item b ON a.`item_id` = b.`item_id`
                JOIN program c ON a.`program_id` = c.`program_id`
                WHERE a.program_id = '".$program_id->program_id."'";

			$sqlsx    = $this->db->query($sqlx);
			$count_page = $sqlsx->getRow();
			$counts = $count_page->counts;

			$dataArr = array();

			foreach ($result as $models)
			{
				$dataArr[] = [
					'item_id' => $models['item_id'],
					'program_id' => $models['program_id'],
					'codeItem' => $models['codeItem'],
					'itemName' => $models['itemName'],
					'brand' => $models['brand'],
					'model' => $models['model'],
					'picture' => $models['picture'],
					'quantity' => $models['quantity'],
					'basic_price' => $models['basic_price'],
					'publish_price' => $models['publish_price'],
					'minimum_stock' => $models['minimum_stock'],
					'unit_point' => $models['unit_point'],
					'point_redeem' => $models['point_redeem'],
					'created_date' => $models['created_date'],
					'created_by' => $models['created_by'],
					
				];
			}
            
			$this->_sendResponse(200, '', $dataArr);
		}
		else
		{
			$this->_sendResponse(500, 'apikey NULL', '');
		}
	}
}