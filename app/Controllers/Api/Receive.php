<?php namespace App\Controllers\Api;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\OrganizationModel;
use App\Models\NotificationModel;
use App\Models\CustomModel;
use App\Models\TrainingHeaderModel;
use App\Models\OutboundModel;
 
class Receive extends ResourceController
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
				// return $this->respond($result);
                return $result;
			}
			else
				$this->_sendResponse(400, $controller.'_id not found', '');
		}
		else
			$this->_sendResponse(400, $controller.'_id is empty', '');
	}

    public function getOutbound()
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
		
        $status_dev = $this->db->table('status')->select('*')->where('code', 'OUTB_DELIVERY')->get()->getRow();
        $status_rec = $this->db->table('status')->select('*')->where('code', 'OUTB_RECEIVED')->get()->getRow();
        
        $sql = 'SELECT * FROM outbound a
        JOIN programitem b ON a.`program_item_id` = b.`program_item_id`
        JOIN users c ON a.`users_id` = c.`users_id`
        WHERE a.users_id = "'.$check['users_id'].'" AND b.program_id = "'.$check['program_id'].'" AND status_id IN ("'.$status_dev->status_id.'", "'.$status_rec->status_id.'")
        ORDER BY a.created_date DESC, a.outbound_id DESC';
        
        $sqls    = $this->db->query($sql);
		$criteria = $sqls->getResultArray();
		
		$sqlx = 'SELECT COUNT(*) counts FROM outbound a
                JOIN programitem b ON a.`program_item_id` = b.`program_item_id`
                JOIN users c ON a.`users_id` = c.`users_id`
                WHERE a.users_id = "'.$check['users_id'].'" AND b.program_id = "'.$check['program_id'].'" AND status_id IN ("'.$status_dev->status_id.'", "'.$status_rec->status_id.'")
                ORDER BY a.created_date DESC, a.outbound_id DESC';

		$sqlsx    = $this->db->query($sqlx);
		$count_page = $sqlsx->getRow();
		$counts = $count_page->counts;

        foreach ($criteria as $models) 
		{
            $statusx = $this->db->table('status')->select('*')->where('status_id', $models['status_id'])->get()->getRow();

            $querys = 'SELECT * FROM programitem a
            JOIN program b ON a.`program_id` = b.`program_id`
            JOIN item c ON a.`item_id` = c.`item_id`
            WHERE a.program_item_id = "'.$models['program_item_id'].'"';

            $queryx    = $this->db->query($querys);
            $prgitm = $queryx->getRow();
            
            if($statusx->code == "OUTB_DELIVERY")
            {
                $date = $models['outbound_date'];
                $qty = $models['outbound_quantity'];
                $status = "Delivery";
            }
                
            if($statusx->code == "OUTB_RECEIVED")
            {
                $date = $models['received_date'];
                $qty = $models['received_quantity'] - $models['distribution_quantity'];
                $status = "Received";
            }
            
            if($qty > 0)
            {
                $dataArr[] = array(
                    'outbound_id' => $models['outbound_id'],
                    'material_name' => $prgitm->name,
                    'quantity' => $qty,
                    'date' => $date,
                    'status' => $status,
                    'description' => $models['description'],
                );
            }
		}
        
        $arrData = array(
            'total_page' => intval(round($counts / 10)),
            'outbound_list' => $dataArr,
        );
        
        $this->_sendResponse(200, '', $arrData);
	}

    public function postReceived()
	{
		header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);	
		
		$token = "";
		$program_id = "";
		$outbound_id = "";
		$error = "";
		
		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value;
			
			if($var == "program_id")
				$program_id = $value;	
			
			if($var == "outbound_id")
				$outbound_id = $value;
		}
		$check = $this->checkToken($token, $program_id);
		$checkId = $this->checkId($outbound_id, "outbound");
        
        $model = $this->db->table('outbound')->select('*')->where('outbound_id', $checkId["id"])->get()->getRow();
		// $model = new outboundModel;
		
		foreach($obj as $var=>$value) 
		{
			if($var == "received_quantity")
				$model->received_quantity = $value;
				
			if($var == "return_quantity")
				$model->return_quantity = $value;
				
			if($var == "remark")
				$model->description = $model->description.", ".$value;
				
			if($var == "picture")
				$model->picture = $value;
		}
        $status = $this->db->table('status')->select('*')->where('status_id', $model->status_id)->get()->getRow();
        
        if($status->code == "OUTB_RECEIVED")
			$this->_sendResponse(304, "Data already had Received", '');
        else
        {
			if($model->received_quantity) 
			{
				if ($model->return_quantity)
				{
					if($model->received_quantity <= $model->outbound_quantity)
					{
						$statusx = $this->db->table('status')->select('*')->where('code', 'OUTB_RECEIVED')->get()->getRow();
						$model->status_id = $statusx->status_id;
						$model->received_date = date("Y-m-d H:i:s");
		
						if($model->picture != "")
						{
							$imgName = $model->outbound_id.'.jpg';
							$imageData = base64_decode($obj->picture);
							
							$uploadPath = FCPATH .'upload/image/receive/';
							
							helper('filesystem');
							write_file($uploadPath . $imgName, $imageData);
		
							$urlimgName = base_url()."/upload/image/redemption/".$imgName;
						}
						
						$save = [
							'received_quantity' => $model->received_quantity,
							'outbound_quantity' => $model->outbound_quantity,
							'status_id' => $model->status_id,
							'description' => $model->description,
							'modified_date' => date("Y-m-d H:i:s"),
							'modified_by' => $check["users_id"]
						];
		
						if($this->db->table('outbound')->where('outbound_id', $model->outbound_id)->update($save))
						{
							$this->_sendResponse(200, '', '');
						}
						else
						{
							$this->_sendResponse(408, $ex->getMessage(), '');
						}
					}
				}
				else
					$this->_sendResponse(500, 'Return Quantity Cannot be blank', '');
			}
			else
				$this->_sendResponse(500, 'Received Quantity Cannot be blank', '');
        }
		
	}
}