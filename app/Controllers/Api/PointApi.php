<?php namespace App\Controllers\Api;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\NotificationModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
 
class PointApi extends ResourceController
{
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

	public function detailPoint()
	{
		header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);
		
		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value;
			
			if($var == "programCode")
				$programCode = $value;
			
		}
		
		$program_id = $program = $this->db->table('program')->select('program_id')->where('code', $programCode)->get()->getRow();

		$check = $this->checkToken($token, $program_id->program_id);
		
		try
		{
			
		}
		catch (Exception $ex) 
		{  
			$transaction->rollback();
			$this->_sendResponse(408, $ex->getMessage(), '');
		}
	}

	function _getKodeOrderID()
	{
        $no_rand            = (rand(10, 999999));
        $code        = 'ID'.$no_rand;
        return $code;
    }

	function generateSKU($productName) {
		// Ambil dua huruf pertama dari nama produk dan kategori
		$productInitials = strtoupper(substr($productName, 0, 6));

		// Karakter yang akan digunakan untuk SKU
		$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

		// Membuat string acak sepanjang 8 karakter
		$randomString = '';
		for ($i = 0; $i < 4; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}
	
		// Tambahkan timestamp sebagai bagian unik
		$timestamp = time();
	
		// Gabungkan semuanya untuk membuat SKU
		$sku = 'ID'.$productInitials . $randomString . $timestamp;
	
		return $sku;
	}

	function codeOrderID()
	{
        
        $no_rand            = (rand(10, 999999));
        $code_difile        = 'ID'.$no_rand;
        return $code_difile;
    }

	function code()
	{
		$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

		// Membuat string acak sepanjang 8 karakter
		$randomString = '';
		for ($i = 0; $i < 10; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}

		$code = 'TH'.$randomString;

		return $code;
	}

	public function saveorderpoint()
	{
		header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);
		// var_dump($obj);exit;
		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value->token;
			
			if($var == "programCode")
				$programCode[] = $value->programCode;

			if($var == "nameItem")
				$nameItem = $value->nameItem;

			if($var == "point")
				$point = $value->point;
		}
		// var_dump($point);exit;
		// $sku = generateSKU($nameItem);

		$program_id = $this->db->table('program')->select('program_id')->where('code', $programCode)->get()->getRow();
		$sql = 'SELECT a.users_id, username, a.`member_id`, b.first_name, b.mobile_phone, a.email, a.city, domicile_address, 
					point_awarded, point_redeem, point_expire, point_balance, b.`note1`, b.`note2`, b.`note3`
					FROM users a 
					JOIN member b ON a.`member_id` = b.member_id
					WHERE a.`token` ="'.$token.'"';
					
		$query = $this->db->query($sql);
		$result = $query->getRow();

		$idsku = $this->generateSKU($nameItem);
		$idorder = $this->codeOrderID();
		$code = $this->code();
		// echo var_dump($code);exit;
		$check = $this->checkToken($token, $program_id->program_id);
		$order_id = $this->_getKodeOrderID();
		$orderdate = date("Y-m-d H:i:s");
		// echo var_dump($programCode);exit;
		try
		{
			for($a=0;$a<count($programCode); $a++)
			{
				$saveOrder = [
					'orderID' => $order_id,
					'OrderDate' => $orderdate,
					'MobilePhone' => $result->mobile_phone,
					'Addressship' => $result->domicile_address,
					'UserID' => $result->users_id,
					'Program_id' => $program_id->program_id,
					'UpdateDate' => $orderdate
				];
	
				if($this->db->table('orders')->insert($saveOrder)) 
				{
					$saveOrderDetail = [
						'OrderID' => $idorder,
						'IDSku' => $idsku,
						'qty' => "1",
						'NameProduk' => $nameItem,
						'PricePoint' => $point,
					];
					
					if($this->db->table('orders_detail')->insert($saveOrderDetail)) 
					{
						$savetHistory = [
							'code' => $code,
							'member_id' => $result->member_id,
							'item' => $nameItem,
							'type' => "Claim",
							'point' => $point,
							'created_date' => date("Y-m-d H:i:s"),
							'modified_date' => date("Y-m-d H:i:s"),
							'created_by' => $result->users_id,
							'modified_by' => $result->users_id
						];
	
						if($this->db->table('transactionhistory')->insert($savetHistory)) 
						{
							$jmlPoint = $result->point_balance - $point;
							$data = [
								'point_balance' => $jmlPoint
							];
	
							$this->db->table('member')->update($data);
							// echo var_dump($jmlPoint);exit;
						}
					}
					$this->_sendResponse(200, '', '');
				}
				else
				{
					$this->_sendResponse(500, 'Orders Not Save', '');
				}
			}
		}
		catch (Exception $ex)
		{
			$transaction->rollback();
			$this->_sendResponse(408, $ex->getMessage(), '');
		}
	}
}