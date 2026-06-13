<?php namespace App\Controllers\Api;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\AttendanceModel;
use App\Models\NotificationModel;
use App\Models\CustomModel;
use App\Models\TimelineCommentModel;
use App\Models\TimelineModel;
 
class Attendance extends ResourceController
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
				// return $this->respond($result);
                return $result;
			}
			else
				$this->_sendResponse(400, $controller.'_id not found', '');
		}
		else
			$this->_sendResponse(400, $controller.'_id is empty', '');
	}

    public function postAttendance()
    {
        header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);	
		
		$token = "";
		$program_id = "";
		$error = "";
		$subject = "";
		
		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value;
			
			if($var == "program_id")
				$program_id = $value;	
				
			if($var == "subject")
				$subject = $value;

		}
		
		$check = $this->checkToken($token, $program_id);

        $model = new attendanceModel;
		foreach($obj as $var=>$value)
		{
			if($model->hasAttribute($var))
			{
				$model->$var = $value;
			}
		}

		$program = $this->db->table('program')->select('*')->where('program_id', $check['program_id'])->get()->getRow();

        if($program->module_redemption == 1)
        {
            $models = $this->db->table('redemption')->select('*')->where('delivery_order', $model->users_organization_id)->get()->getResultArray();
            if($models) 
            {
            	$urlimgName = "";
	            if($model->picture != "")
	            {
	            	$imgName = $model->users_organization_id.'.jpg';
					$imageData = base64_decode($model->picture);
					
					$uploadPath = FCPATH .'upload/image/redemption/';
					
	        		helper('filesystem');
					write_file($uploadPath . $imgName, $imageData);

					$urlimgName = base_url()."/upload/image/redemption/".$imgName;
	            }
	            $allOk = true; 
	            foreach($models as $mdl)
	            {
	                $redemption = $this->db->table('redemption')->select('*')->where('redemption_id', $mdl['redemption_id'])->get()->getRow();
					
					if ($redemption)
					{
						$redemption->pod = $urlimgName;

						$saveRdm = [
							'pod'=> $redemption->pod,
						];
						// echo var_dump($saveRdm);exit;
						$updateSuccess = $this->db->table('redemption')->where('redemption_id', $redemption->redemption_id)->update($saveRdm);
						if (!$updateSuccess) 
						{
							$allOk = false;
							break;
						}
						
						$saveTml = [
							'reference_id' => $redemption->redemption_id,
							'program_id' => $check['program_id'],
							'users_id' => $check['users_id'],
							'type' => "Upload POD",
							'code' => $redemption->tracking_number,
							'datetime' => $model->datetime,
							'name' => "",
							'location' => $redemption->delivery_order,
							'description' => $model->notes,
							'photo' => $redemption->pod,
							'created_date' => date("Y-m-d H:i:s"),
							'modified_date' => date("Y-m-d H:i:s"),
							'created_by' => $check["users_id"],
							'modified_by' => $check["users_id"]
						];

						$insertTimeline = $this->db->table('timeline')->insert($saveTml);
						if (!$insertTimeline) 
						{
							$allOk = false;
							break;
						}
					}
	            }

				if ($allOk) 
				{
					$this->_sendResponse(200, '', '');
				} 
				else 
				{
					$this->_sendResponse(500, 'error', 'Failed to update all redemption / timeline');
				}
            }
            else 
			{
				$this->_sendResponse(400, 'delivery_order', '');
			}

        }
		else
		{
			$checkId = $this->checkId($obj->users_organization_id, "usersorganization");

			$model->users_organization_id = $checkId["id"];
			
			$distance_info = '';
			
			$model->users_id = $check['users_id'];
			
			$model->code = $this->customModel->setCounterNumber('attendance', 'code', 'CHA');
			
			$model->datetime = $model->datetime;

			if($model->datetime == "" || $model->datetime == NULL)
				$model->datetime = date("Y-m-d H:i:s");
			$attendancetype = $this->db->table('attendancetype')->select('*')->where('attendance_type_id', $subject)->get()->getRow();
			
			if($attendancetype)
			{
				if(strtolower($attendancetype->name) != "expansion")
				{
					$usr_orgz = $this->db->table('usersorganization')->select('*')->where('users_organization_id', $model->users_organization_id)->get()->getRow();
					
					if($usr_orgz->users_organization_id != "" && $usr_orgz->users_organization_id != NULL)
					{
						$cl_orgz = $this->db->table('clientorganization')->select('*')->where('client_organization_id', $usr_orgz->client_organization_id)->get()->getRow();
						$orgz =  $this->db->table('organization')->select('*')->where('organization_id', $cl_orgz->organization_id)->get()->getRow();

						if(($orgz->latitude == "" && $orgz->longitude == "") || ($orgz->latitude == NULL && $orgz->longitude == NULL))
						{

							$saveOrgz = [
								'latitude' => $obj->latitude,
								'longitude' =>$obj->longitude
							];
							
							$this->db->table('organization')->where('organization_id', $orgz->organization_id)->update($saveOrgz);
						}
						else
						{
							$theta = $obj->longitude - $orgz->longitude; 
							$distance = (sin(deg2rad($model->latitude)) * sin(deg2rad($orgz->latitude)))  + (cos(deg2rad($model->latitude)) * cos(deg2rad($orgz->latitude)) * cos(deg2rad($theta))); 
							$distance = acos($distance); 
							$distance = rad2deg($distance); 
							$distance = $distance * 60 * 1.1515; 
							$distance = $distance * 1.609344; 
							$result = round($distance, 2) * 1000;
							
							if($result >= 500)
								$distance_info = 'Warning : You are too far away from the channel ('.number_format($result, 0, ',', '.').' meter)';
							
							if($obj->location == "")
							{
								
								$geolocation = $obj->latitude.','.$obj->longitude;
								
								$url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.$geolocation.'&sensor=false';
								$handle = curl_init($url);
								curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
								curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
								curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
								$response = curl_exec($handle);
								
								curl_close($handle);
								$model->location = json_decode($response)->results[0]->formatted_address;
							}
						}
						
						$model->users_organization_id = $usr_orgz->users_organization_id;
					}
				}
				else
				{
					$arrExp = explode('*', $model->notes);
					
					if(count($arrExp) == 2)
					{
						$status = $this->db->table('status')->select('*')->where('code', 'ORGZ_OPEN')->get()->getRow();
						$city = $this->db->table('city')->select('*')->where('name', 'None')->get()->getRow();
						$codeOrgz = $this->customModel->setIdRandomString('Organization', 'code', 'ORGZ', 8, 'String');

						$saveOrganization = [
							'status_id' => $status->status_id,
							'city_id' => $city->city_id,
							'city' => $city->name,
							'code' => $codeOrgz,
							'name' => $arrExp[0],
							'address' => $arrExp['1'],
							'building_name' => $codeOrgz,
							'created_date' => date("Y-m-d H:i:s"),
							'modified_date' => date("Y-m-d H:i:s"),
							'created_by' => $model->users_id,
							'modified_by' => $model->users_id
						];
						
						if ($this->db->table('organization')->insert($saveOrganization)) 
						{
							$organization_id   = $this->db->insertID();
							
							$saveClientOrgz = [
								'client_id' => $program->client_id,
								'organization_id' => $organization_id,
								'code' => $saveOrganization['code'],
								'name' => $saveOrganization['name'],
								'created_date' => date("Y-m-d H:i:s"),
								'modified_date' => date("Y-m-d H:i:s"),
								'created_by' => $model->users_id,
								'modified_by' => $model->users_id
							];

							if ($this->db->table('clientorganization')->insert($saveClientOrgz))
							{
								$clientOrgz_id   = $this->db->insertID();
								$stat = $this->db->table('status')->select('*')->where('code', 'UORG_ACTIVE')->get()->getRow();

								$usrOrgz = [
									'users_id' => $check['users_id'],
									'client_organization_id' => $clientOrgz_id,
									'status_id' => $stat->status_id,
									'created_date' => date("Y-m-d H:i:s"),
									'modified_date' => date("Y-m-d H:i:s"),
									'created_by' => $model->users_id,
									'modified_by' => $model->users_id
								];

								if($this->db->table('usersorganization')->insert($usrOrgz))
								{
									$usrOrgz_id   = $this->db->insertID();
									$model->users_organization_id = $usrOrgz_id;
								}
								else
								{
									$this->_sendResponse(500, $error, '');
								}
							}
							else
							{
								$this->_sendResponse(500, $error, '');
							}
						}
						else
						{
							$this->_sendResponse(500, $error, '');
						}
						
					}
					else
					{
						$this->_sendResponse(500, "Format Expansion : channelName*ChannelAddress", '');
					}
				}
			}

			$codeTimeline = $this->customModel->setCounterNumber('timeline', 'code', 'TML');
			$urlimgName = "";
			
			if($model->picture != "")
			{
				$imgName = $codeTimeline.'.jpg';
				$imageData = base64_decode($model->picture);

				$uploadPath = FCPATH .'upload/image/attendance/';
				
        		helper('filesystem');
				write_file($uploadPath . $imgName, $imageData);

				$urlimgName = base_url()."/upload/image/attendance/".$imgName;
			}
			// echo var_dump($urlimgName);exit;
			$saveModel = [
				'program_id' => $program->program_id,
				'attendance_type_id' => $subject,
				'users_id' => $model->users_id,
				'users_organization_id' => $obj->users_organization_id,
				'code' => $model->code,
				'datetime' => $model->datetime,
				'latitude' => $obj->latitude,
				'longitude' => $obj->longitude,
				'location' => $obj->location,
				'notes' => $model->notes,
				'picture' => $urlimgName,
				'created_date' => date("Y-m-d H:i:s"),
				'created_by' => $model->users_id,
				'modified_date' => date("Y-m-d H:i:s"),
				'modified_by' => $model->users_id
			];
			
			if($this->db->table('attendance')->insert($saveModel)) 
			{
				$attendance_id = $this->db->insertID();
				$usr_orgz = $this->db->table('usersorganization')->select('*')->where('users_organization_id', $model->users_organization_id)->get()->getRow();
				
				$sql_c = "SELECT a.client_organization_id, b.`organization_id`, b.`name` AS channel FROM clientorganization a 
						JOIN organization b ON a.`organization_id` = b.`organization_id`
						WHERE client_organization_id ='".$usr_orgz->client_organization_id."'";

				$sql_cn     = $this->db->query($sql_c);
				$channel = $sql_cn->getRow();

				$saveTimeline = [
					'reference_id' => $attendance_id,
					'program_id' => $check['program_id'],
					'users_id' => $check['users_id'],
					'type' => "Checked in",
					'code' => $codeTimeline,
					'datetime' => $model->datetime,
					'location' => $channel->channel,
					'description' => $model->notes,
					'photo' => $urlimgName,
					'created_date' => date("Y-m-d H:i:s"),
					'modified_date' => date("Y-m-d H:i:s"),
					'created_by' => $check['users_id'],
					'modified_by' => $check['users_id']
				];
				
				if($this->db->table('timeline')->insert($saveTimeline)) 
				{
					$this->_sendResponse(200, $distance_info, '');
				}
				else 
				{
					$this->_sendResponse(500, $error, '');
				}
			}
		}
	}

	public function getAttendance()
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
		
		$sql = "SELECT a.`attendance_id`, b.`first_name`, c.name AS type_name, f.name AS channel_name, a.`location`, a.notes, a.`datetime` FROM attendance a
		JOIN users b ON a.`users_id` = b.`users_id`
		JOIN attendancetype c ON a.`attendance_type_id` = c.`attendance_type_id`
		JOIN usersorganization d ON a.`users_organization_id` = d.`users_organization_id`
		JOIN clientorganization e ON d.`client_organization_id` = e.`client_organization_id`
		JOIN organization f ON e.`organization_id` = f.`organization_id`
		WHERE a.program_id = '".$program_id."' ";

		$autorize = $this->db->table('usersgroupprogram')->select('*')->where('users_id', $check['users_id'])->where('program_id', $check['program_id'])->get()->getRow();
		
		if($autorize)
		{
			if($autorize->data_level == "low")
				$sql .= "AND a.users_id ='".$check['users_id']."'";
		}
		$sql .= " ORDER BY a.created_date DESC, a.attendance_id DESC";
		$sql .= ' LIMIT '.$perPage.' OFFSET '.(($page - 1) * $perPage);
		
		$sql    = $this->db->query($sql);
		$criteria = $sql->getResultArray();
		
		$sqls = "SELECT COUNT(*) AS counts FROM attendance a
		JOIN users b ON a.`users_id` = b.`users_id`
		WHERE a.program_id = '62' ORDER BY a.created_date DESC, a.attendance_id DESC";

		$sqls    = $this->db->query($sqls);
		$count = $sqls->getRow()->counts;

		$totalPages = ceil($count / $perPage);

		$dataArr = array();
		foreach ($criteria as $models)
		{
			$dataArr[] = array(
				'attendance_id' => $models['attendance_id'],
				'fullname' => $models['first_name'],
				'subject' => $models['type_name'],
				'channel_name' => $models['channel_name'],
				'location' => $models['location'],
				'notes' => $models['notes'],
				'datetime' => $models['datetime']

			);
		}

		$arrData = array(
			'total_page' => $totalPages,
			'attendance_list' => $dataArr,
		);
		
		$this->_sendResponse(200, '', $arrData);
    }

	public function attendanceDetail()
	{
		$this->db = \Config\Database::connect();
		
		header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);	
		
		$token = "";
		$program_id = "";
		$attendance_id = "";
		$error = "";
		
		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value;
			
			if($var == "program_id")
				$program_id = $value;	
			
			if($var == "attendance_id")
				$attendance_id = $value;	
		}

		$check = $this->checkToken($token, $program_id);
		$checkId = $this->checkId($attendance_id, "attendance");
		
		$sql = "SELECT a.`attendance_id`, b.`first_name`, c.name AS type_name, f.name AS channel_name, a.`location`, a.notes, a.`datetime`, 
		a.latitude, a.longitude
		FROM attendance a
		JOIN users b ON a.`users_id` = b.`users_id`
		JOIN attendancetype c ON a.`attendance_type_id` = c.`attendance_type_id`
		JOIN usersorganization d ON a.`users_organization_id` = d.`users_organization_id`
		JOIN clientorganization e ON d.`client_organization_id` = e.`client_organization_id`
		JOIN organization f ON e.`organization_id` = f.`organization_id`
		WHERE a.attendance_id = '".$checkId["id"]."'";

		$sql    = $this->db->query($sql);
		$attendance = $sql->getRow();

		$arr = [
			'attendance_id' => $attendance->attendance_id,
			'channel_name' => $attendance->channel_name,
			'activity' => $attendance->type_name,
			'datetime' => $attendance->datetime,
			'remark' => $attendance->notes,
			'location' => $attendance->location,
			'latitude' => $attendance->latitude,
			'longitude' => $attendance->longitude
		];
		
		$this->_sendResponse(200, '', $arr);
	}
}