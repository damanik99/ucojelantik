<?php namespace App\Controllers\Api;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ActivityModel;
use App\Models\OrganizationModel;
use App\Models\NotificationModel;
use App\Models\CustomModel;
use App\Models\MerchandiseDistributionModel;
 
class Distribution extends ResourceController
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

    public function postDistribution()
    {
        header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);	
		
		$token = "";
		$program_id = "";
		$outbound_id = "";
		$datetime = "";
		$error = "";
		
		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value;
			
			if($var == "program_id")
				$program_id = $value;

			if($var == "users_organization_id")
				$users_organization_id = $value;
			
			if($var == "item")
				$outbound_id = $value->outbound_id;
			
			if($var == "datetime")
				$datetime = $value;
		}
		
		$check = $this->checkToken($token, $program_id);
		$checkId = $this->checkId($outbound_id, "outbound");
		
        $outbound = $this->db->table('outbound')->select('*')->where('outbound_id', $checkId["id"])->get()->getRow();

		$model = new merchandiseDistributionModel;
		
		$mds = $this->db->table('merchandisedistribution')->select('*')->where('outbound_id', $checkId["id"])->get()->getRow();
		$usersOrganization = $this->db->table('usersorganization')->select('*')->where('users_organization_id', $mds->users_organization_id)->get()->getRow();
		$clientOrganization = $this->db->table('clientorganization')->select('*')->where('client_organization_id', $usersOrganization->client_organization_id)->get()->getRow();
		$organization = $this->db->table('organization')->select('*')->where('organization_id', $clientOrganization->organization_id)->get()->getRow();
		
		if($datetime == "" || $datetime == NULL)
			$date = date("Y-m-d H:i:s");
		else
			$date = $datetime;
		
		if($obj->item->quantity <= ($outbound->received_quantity - $outbound->distribution_quantity))
		{
			if($organization->name == "Other Location")
			{
				$arrExp = explode('*', $obj->item->remarks);
				
				if(count($arrExp) == 2)
				{
					$status = $this->db->table('status')->select('*')->where('code', "ORGZ_OPEN")->get()->getRow();
					$city = $this->db->table('city')->select('*')->where('name', "None")->get()->getRow();
					$program = $this->db->table('program')->select('*')->where('program_id', $check['program_id'])->get()->getRow();
					$codeOrgz = $this->customModel->setIdRandomString('Organization', 'code', 'ORGZ', 8, 'String');
					
					$saveOrganization = [
						'status_id' => $status->status_id,
						'city_id' => $city->city_id,
						'city' => $city->name,
						'code' => $codeOrgz,
						'name' => $arrExp[0],
						'address' => $arrExp[1],
						'building_name' => $codeOrgz,
						'created_date' => date("Y-m-d H:i:s"),
						'modified_date' => date("Y-m-d H:i:s"),
						'created_by' => $usersOrganization->users_id,
						'modified_by' => $usersOrganization->users_id
					];

					if($this->db->table('organization')->insert($saveOrganization))
					{
						$organization_id   = $this->db->insertID();
							
						$saveClientOrgz = [
							'client_id' => $program->client_id,
							'organization_id' => $organization_id,
							'code' => $saveOrganization['code'],
							'name' => $saveOrganization['name'],
							'created_date' => date("Y-m-d H:i:s"),
							'modified_date' => date("Y-m-d H:i:s"),
							'created_by' => $usersOrganization->users_id,
							'modified_by' => $usersOrganization->users_id
						];

						if($this->db->table('clientorganization')->insert($saveClientOrgz))
						{
							$clientOrgz_id   = $this->db->insertID();
							$stat = $this->db->table('status')->select('*')->where('code', 'UORG_ACTIVE')->get()->getRow();

							$usrOrgz = [
								'users_id' => $check['users_id'],
								'client_organization_id' => $clientOrgz_id,
								'status_id' => $stat->status_id,
								'created_date' => date("Y-m-d H:i:s"),
								'modified_date' => date("Y-m-d H:i:s"),
								'created_by' => $usersOrganization->users_id,
								'modified_by' => $usersOrganization->users_id
							];

							if($this->db->table('usersorganization')->insert($usrOrgz))
							{
								$usrOrgz_id   = $this->db->insertID();
								$model->users_organization_id = $usrOrgz_id;
							}
							else
							{
								$this->_sendResponse(500, $error, 'Save Users Organization Error');
							}
						}
						else
						{
							$this->_sendResponse(500, $error, 'Save Client Organization Error');
						}
					}
					else
					{
						$this->_sendResponse(500, $error, 'Save Organization Error');
					}
				
				}
			}

			$model->program_item_id = $outbound->program_item_id;
			$tick = round(microtime(true) * 1000);
			$name = $model->program_item_id.$tick;
			
			if($obj->picture_before != "")
			{
				$imgName = "afr_".$name.'.jpg';
				$imageData = base64_decode($obj->picture_before);
				$uploadPath = FCPATH .'upload/image/distribution/';
				
        		helper('filesystem');
				write_file($uploadPath . $imgName, $imageData);

				$urlimgBfr = base_url()."/upload/image/distribution/".$imgName;

			}
			else
				$this->_sendResponse(408, 'Picture Before Empty', '');
			
			if($obj->picture_after != "")
			{
				$imgName = "bfr_".$name.'.jpg';
				$imageData = base64_decode($obj->picture_after);

				$uploadPath = FCPATH .'upload/image/distribution/';
				
        		helper('filesystem');
				write_file($uploadPath . $imgName, $imageData);

				$urlimgAfr = base_url()."/upload/image/distribution/".$imgName;
				
			}
			else
				$this->_sendResponse(408, 'Picture Before Empty', '');
			
			$model->code = $this->customModel->setCounterNumber('merchandisedistribution', 'code', 'DST');
			
			$saveModel = [
				'outbound_id' => $outbound->outbound_id,
				'users_organization_id' => $usersOrganization->users_organization_id,
				'program_item_id' => $model->program_item_id,
				'code' => $model->code,
				'distribution_date' => $date,
				'quantity' => $obj->item->quantity,
				'active' => '1',
				'display' => '1',
				'picture_before' => $urlimgBfr,
				'picture_after' => $urlimgAfr,
				'description' => $obj->item->remarks,
				'created_date' => date("Y-m-d H:i:s"),
				'modified_date' => date("Y-m-d H:i:s"),
				'created_by' => $usersOrganization->users_id,
				'modified_by' => $usersOrganization->users_id
			];
			
			if($this->db->table('merchandisedistribution')->insert($saveModel)) 
			{
				$mds_id = $this->db->insertID();
				$timeline_code = $this->customModel->setCounterNumber('timeline', 'code', 'TML');
				$programitem = $this->db->table('programitem')->select('*')->where('program_item_id', $outbound->program_item_id)->get()->getRow();
				$item = $this->db->table('item')->select('*')->where('item_id', $programitem->item_id)->get()->getRow();
				
				$timeline = [
					'reference_id' => "$mds_id",
					'program_id' => $check['program_id'],
					'users_id' => $check['users_id'],
					'type' => "Distribution",
					'code' => $timeline_code,
					'datetime' => $date,
					'name' => "tets",
					'location' => $organization->name,
					'description' => "Distribution ".$obj->item->quantity." Unit ".$item->name,
					'photo' => $urlimgAfr,
					'created_date' => date("Y-m-d H:i:s"),
					'modified_date' => date("Y-m-d H:i:s"),
					'created_by' => $usersOrganization->users_id,
					'modified_by' => $usersOrganization->users_id
				];
				
				$saveOutbound = [
					'distribution_quantity' => $outbound->distribution_quantity += $obj->item->quantity
				];
				
				$this->db->table('outbound')->where('outbound_id', $outbound->outbound_id)->update($saveOutbound);
				
				if($this->db->table('timeline')->insert($timeline)) 
				{
					$this->_sendResponse(200, '');
				}
				else
				{
					$this->_sendResponse(500, 'Error');
				}
			}
			
		}
	}

	public function getDistribution()
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
		
		$sql = 'SELECT merchandise_distribution_id, a.`users_organization_id`, f.`first_name`, e.name AS channel, g.name AS item, a.quantity, a.picture_after, a.`distribution_date` 
				FROM merchandisedistribution a
				JOIN programitem b ON a.`program_item_id` = b.`program_item_id`
				JOIN usersorganization c ON a.`users_organization_id` = c.`users_organization_id`
				JOIN clientorganization d ON c.`client_organization_id` = d.`client_organization_id`
				JOIN organization e ON d.`organization_id` = e.`organization_id`
				JOIN users f ON c.`users_id` = f.`users_id`
				JOIN item g ON b.`item_id` = g.`item_id`
				WHERE b.`program_id` = "'.$check['program_id'].'" AND a.active = "1"';
		
		$autorize = $this->db->table('usersgroupprogram')->select('*')->where('users_id', $check['users_id'])->where('program_id', $check['program_id'])->get()->getRow();
		
		if($autorize)
		{
			if($autorize->data_level == "low")
				$sql .= ' AND a.created_by ="'.$check['users_id'].'"';
		}

		$sql .= ' ORDER BY a.`created_date` DESC, a.`merchandise_distribution_id` DESC';
		
		$sqls    = $this->db->query($sql);
		$criteria = $sqls->getResultArray();
		
		$sqlx = 'SELECT COUNT(*) AS counts FROM merchandisedistribution a
				JOIN programitem b ON a.`program_item_id` = b.`program_item_id`
				WHERE b.`program_id` = "'.$check['program_id'].'" AND a.active = "1"';

		$sqlsx    = $this->db->query($sqlx);
		$count_page = $sqlsx->getRow();
		$counts = $count_page->counts;
		
		$dataArr = array();
		foreach ($criteria as $models) 
		{
			$dataArr = [
				'merchandise_distribution_id' => $models['merchandise_distribution_id'],
				'users_organization_id' => $models['users_organization_id'],
				'username' => $models['first_name'],
				 'channel_name' => $models['channel'],
				'material_name' => $models['item'],
				'quantity' => $models['quantity'],
				'picture_after' => $models['picture_after'],
				'datetime' => $models['distribution_date'],
			];
		}

		$arrData = array(
			'total_page' => intval(round($counts / 10)),
			'distribution_list' => $dataArr,
		);
		
		$this->_sendResponse(200, '', $arrData);
	}

	public function listReceived()
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

		$status_rec = $this->db->table('status')->select('*')->where('code', 'OUTB_RECEIVED')->get()->getRow();
		
		$sql = 'SELECT a.outbound_id, c.name AS item, a.received_quantity, a.distribution_quantity FROM outbound a
				JOIN programitem b ON a.`program_item_id` = b.`program_item_id`
				JOIN item c ON b.`item_id` = c.`item_id`
				WHERE a.`status_id` = "'.$status_rec->status_id.'" AND users_id ="'.$check['users_id'].'"
				AND (a.received_quantity - a.distribution_quantity) > 0 AND b.`program_id` = "'.$check['program_id'].'"
				ORDER BY c.name ASC';

		$query  = $this->db->query($sql);
		$result_query = $query->getResultArray();

		foreach($result_query as $model)
		{
			$suggest[] = array(
				'outbound_id'=>$model['outbound_id'],
				'material_name'=>$model['item'].' - '.($model['received_quantity'] - $model['distribution_quantity']),
			);
			// echo var_dump($suggest);exit;
		}

		$arrData = array(
			'list_received' => $suggest
		);
		
		$this->_sendResponse(200, '', $arrData);
	}
}