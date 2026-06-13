<?php namespace App\Controllers\Api;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ActivityModel;
use App\Models\NotificationModel;
use App\Models\CustomModel;
use App\Models\TimelineCommentModel;
use App\Models\TimelineModel;
 
class Activity extends ResourceController
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

	public function getSchedule()
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
		
		$sql = "SELECT road_map_id, e.name AS organization, e.`address` AS address, `datetime` FROM Roadmap a
				JOIN users b ON a.users_id = b.users_id
				JOIN clientorganization c ON a.client_organization_id = c.client_organization_id
				JOIN organization e ON c.`organization_id` = e.`organization_id`
				JOIN program d ON d.client_id = c.client_id
				WHERE d.program_id =".$check['program_id']." AND b.users_id =".$check['users_id']." AND (datetime > ".date("Y-m-d")." )";

		$sql     = $this->db->query($sql);
        $roadmap = $sql->getResultArray();
		
		$sqls = "SELECT COUNT(*) AS counts FROM Roadmap a
				JOIN users b ON a.users_id = b.users_id
				JOIN clientorganization c ON a.client_organization_id = c.client_organization_id
				JOIN program d ON d.client_id = c.client_id
				WHERE d.program_id =".$check['program_id']." AND b.users_id =".$check['users_id']." AND (datetime > ".date("Y-m-d")." )";

		$sqls   = $this->db->query($sqls);
        $count = $sqls->getRow();
		$counts = $count->counts;
		// $countS = implode(" ", $count);
		// $pages = new CPagination($count);
		// $pages->pageSize = 10;
		// $pages->currentPage = $obj->page-1;
		// $pages->applyLimit($criteria);
		
        $data = $sql->getResultArray();
		
		$dataArr = array();
		foreach ($data as $models)
		{
			$dataArr = [
				'road_map_id' => $models['road_map_id'],
				'channel_name' => $models['organization'],
				'address' => $models['address'],
				'datetime' => $models['datetime'],
			];
		}
		
		$arrData = array(
			'total_page' => intval(round($counts / 10)),
			'roadmap_list' => $dataArr,
		);
		// echo var_dump($arrData);exit;
		$this->_sendResponse(200, '', $arrData);
		
	}

	public function postStatus()
	{

		header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);
		
		$token = "";
		$program_id = "";
		$type = "";
		$description = "";
		$error = "";
		
		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value;
			
			if($var == "program_id")
				$program_id = $value;

			if ($var == "picture")
				$picture = $value;

			if ($var == "type")
				$type = $value;

			if ($var == "description")
				$description = $value;
		}
		$check = $this->checkToken($token, $program_id);
		
		$model = new activityModel();
		$program = $this->db->table('program')->select('*')->where('program_id', $check['program_id'])->get()->getRow();
		
		$client_orgz;
		$users_orgz;
		$orgz = $this->db->table('organization')->select('*')->where('code', 'OTH0001')->get()->getRow();
		$cl_orgz = $this->db->table('clientorganization')->select('*')->where('organization_id', $orgz->organization_id)->get()->getRow();
		
		if(!$cl_orgz)
		{
			$cl_orgz_ = [
				'organization_id' => $orgz->organization_id,
				'code' => $orgz->code,
				'name' => $orgz->name,
				'client_id' => $program->client_id
			];
			$this->db->table('clientorganization')->insert($cl_orgz_);
			
			$cl_orgz_id   = $this->db->insertID();
			
			$client_orgz = $cl_orgz_id;
		}
		else
		{
			$client_orgz = $cl_orgz->client_organization_id;
		}

		$us_orgz = $this->db->table('usersorganization')->select('*')->where('client_organization_id', $client_orgz)->where('users_id', $check['users_id'])->get()->getRow();
		
		if(!$us_orgz)
		{
			$stat = $this->db->table('status')->select('*')->where('code', 'UORG_INACTIVE')->get()->getRow();
			
			$us_orgz_ = [
				'users_id'=> $check['users_id'],
				'client_organization_id' => $client_orgz,
				'status_id' => $stat->status_id
			];
			$this->db->table('usersorganization')->insert($us_orgz_);
			$us_orgz_id   = $this->db->insertID();
			
			$users_orgz = $us_orgz_->users_organization_id;
		}
		else
			$users_orgz = $us_orgz->users_organization_id;

		$path = APPPATH;
		
		$code_act =	$this->customModel->setCounterNumber('activity', 'code', 'ACT');
		$model->code = $this->customModel->setCounterNumber('activity', 'code', 'ACT');
		$model->users_organization_id = $users_orgz;
		$model->subject = "Visit";
		$model->type = "Share Status";
		$model->description = $description;
		$model->category = "None";
		$model->datetime = date("Y-m-d H:i:s");

		$code = $this->customModel->setCounterNumber('activity', 'code', 'ACT');
// 125848
		$urlimgName = "";
		if($picture != "")
		{
			$imgName = $code.'SHS'.'.jpg';
			$imageData = base64_decode($picture);
			
			$uploadPath = FCPATH .'upload/image/activity/';
			
			helper('filesystem');
			write_file($uploadPath . $imgName, $imageData);

			$urlimgName = base_url()."/upload/image/activity/".$imgName;
		}
		
		$savemodel = [
			'program_id' => $check['program_id'],
			'code' => $model->code,
			'users_organization_id' => $model->users_organization_id,
			'subject' => $model->subject,
			'type' => $model->type,
			'category' => $model->category,
			'datetime' => $model->datetime,
			'description' => $model->description,
			'picture' => $urlimgName,
			'created_date' => date("Y-m-d H:i:s"),
			'modified_date' => date("Y-m-d H:i:s"),
			'created_by' => $check['users_id'],
			'modified_by' => $check['users_id']
		];

		if($this->db->table('activity')->insert($savemodel))
		{
			$activity_id = $this->db->insertID();
			// $usr_orgz = UsersOrganization::model()->findByPk($model->users_organization_id);
			
			$savetimeline = [
				'reference_id' => $activity_id,
				'program_id' => $check['program_id'],
				'users_id' => $check['users_id'],
				'type' => $type,
				'code' => $code,
				'datetime' => $model->datetime,
				'location' => "somewhere",
				'description' => $description,
				'photo' => $picture,
				'created_date' => date("Y-m-d H:i:s"),
				'created_by' => $check['users_id']
			];
			// echo var_dump('test');exit;
			if($this->db->table('timeline')->insert($savetimeline))
			{
				$this->_sendResponse(200, '', '');
			}
			else
			{					
				$timeline->validate();
				if(count($timeline->getErrors()) > 0)
				{
					foreach($timeline->getErrors() as $err)
					{
						for($x = 0; $x <= count($err)-1; $x++)
						{
							$error .= $err[$x];
						}
					}
					$this->_sendResponse(500, $error, '');
				}
				else
					$this->_sendResponse(408, '', '');
			}
		}
		else
		{
			$this->_sendResponse(500, $error, '');
		}
		
	}

	private function hashAttribute($var)
    {
        // Hash the attribute using SHA-256 algorithm (you can choose a different algorithm)
        return hash('sha256', $var);
    }

	public function postComment()
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

			if($var == "timeline_id")
				$timeline_id = $value;

			if ($var == "comment")
				$comment = $value;
		}
		$check = $this->checkToken($token, $program_id);
		
		$model = new TimelineCommentModel();
		// foreach($obj as $var=>$value) 
		// {
		// 	if($model->hasAttribute($var)) 
        //         $model->$var = $value;
		// }
		
		$checkId = $this->checkId($timeline_id, "timeline");
		$model->timeline_id = $checkId["id"];
		$model->users_id = $check['users_id'];
		
		$last_comment = $this->db->table('timelinecomment')->select('*')->where('timeline_comment_id', $model->timeline_id)->get()->getRow();
		
		if($last_comment)
			$model->sequence = $last_comment->sequence + 1;
		else	
			$model->sequence = '1';
		
		$savecomment = [
			'timeline_id' => $model->timeline_id,
			'users_id' => $model->users_id,
			'comment' => $comment,
			'sequence' => $model->sequence,
			'created_date'=> date("Y-m-d H:i:s"),
			'modified_date'=> date("Y-m-d H:i:s"),
			'created_by' => $check['users_id'],
			'modified_by' => $check['users_id']
		];

		if($this->db->table('timelinecomment')->insert($savecomment)) 
		{
			$timeline = $this->db->table('timeline')->select('*')->where('timeline_id', $model->timeline_id)->get()->getRow();
			
			$token_key = array();
			$sql = '
				SELECT users_id 
				FROM (
					SELECT a.users_id FROM timeline a JOIN users b ON a.users_id = b.users_id
					WHERE a.timeline_id = '.$model->timeline_id.'
					UNION
					SELECT a.users_id FROM timelinecomment a JOIN users b ON a.users_id = b.users_id
					WHERE a.timeline_id = '.$model->timeline_id.'
				) X
				GROUP BY users_id
			';	
			$query  = $this->db->query($sql);
			$dataProvider = $query->getResultArray();
			
			foreach($dataProvider as $i=>$ii)
			{
				$users = $this->db->table('users')->select('*')->where('users_id', $model->users_id)->get()->getRow();
				$timelineUsers = $this->db->table('users')->select('*')->where('users_id', $timeline->users_id)->get()->getRow();
				
				$text = $users->first_name.' commented on '.$timelineUsers->first_name.' status';
				
				$savenotification = [
					'program_id' => $check['program_id'],
					'timeline_id' => $model->timeline_id,
					'users_id' => $ii['users_id'],
					'type' => "COMMENT",
					'name' => "COMMENT",
					'description' => $text,
					'created_date'=> date("Y-m-d H:i:s"),
					'modified_date'=> date("Y-m-d H:i:s"),
					'created_by' => $check['users_id'],
					'modified_by' => $check['users_id']
				];

				$this->db->table('notification')->insert($savenotification);
			}
			$this->_sendResponse(200, '', '');
		}
		$this->_sendResponse(408, '', '');
	}

	public function getTimeline()
	{
		$this->db = \Config\Database::connect();
		header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);
		
		$token = "";
		$program_id = "";
		$search_by = "";
		$search_value = "";
		$error = "";
		
		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value;
			
			if($var == "program_id")
				$program_id = $value;	
				
			if($var == "search_by")
				$search_by = $value;	
				
			if($var == "search_value")
				$search_value = $value;	
		}

		$page = $obj->page ?? 1;
		$perPage = 10; // Pastikan ini diinisialisasi

		$check = $this->checkToken($token, $program_id);

		$search_by_arr = [
			['key' => "username", 'name' => "Username"],
			['key' => "type", 'name' => "Type"]
		];
		
		$program = $this->db->table('program')->select('*')->where('program_id', $program_id)->get()->getRow();

		$arr_users = $this->customModel->getClusterUsers($check['users_id'], $program_id);

		$sql = ' SELECT timeline_id, a.`users_id`, b.`username`, program_id, reference_id, `type`, 
		`code`, `datetime`, latitude, longitude, location, description, photo, a.created_date, a.modified_date 
		FROM timeline a 
		JOIN users b ON a.users_id = b.users_id 
		WHERE program_id = "'.$check['program_id'].'"';

		if (count($arr_users) > 0) {
			$sql .= ' AND a.users_id IN ('.implode(',', $arr_users).')';
		}

		if ($search_by == "username") {
			$sql .= ' AND b.username LIKE "%'.$search_value.'%"';
		} elseif ($search_by == "type") {
			$sql .= ' AND a.type ="'.$search_value.'"';
		}

		$sql .= ' AND a.type NOT IN ("Announcement")';

		$type = [];
		if ($program->module_activity == 1) $type[] = "Share Status";
		if ($program->module_attendance == 1) $type[] = "Checked in";
		if ($program->module_selling == 1) $type[] = "Selling";
		if ($program->module_distribution == 1) $type[] = "Distribution";
		if ($program->module_redemption == 1) $type[] = "Upload POD";
		
		if (!empty($type)) {
			$quotedValues = array_map(function($type) { return "'$type'"; }, $type);
			$placeholders = implode(', ', $quotedValues);
			$sql .= ' AND a.`type` IN ('.$placeholders.')';
		}

		$sql .= ' ORDER BY a.`created_date` DESC ';
		$sql .= ' LIMIT '.$perPage.' OFFSET '.(($page - 1) * $perPage);

		$query  = $this->db->query($sql);
		$dataProvider = $query->getResultArray();
		
		$timeline_sql = 'SELECT COUNT(*) AS count FROM timeline WHERE program_id = "'.$program_id.'"';
		$timeline_query  = $this->db->query($timeline_sql);
		$timelineCount = $timeline_query->getRow()->count;

		$totalPages = ceil($timelineCount / $perPage);

		$sql_announs = 'SELECT * FROM timeline a
		WHERE `type` = "Announcement" AND program_id = "'.$program_id.'" AND created_date >= CURDATE() - INTERVAL 3 DAY AND created_date <= CURDATE()+1';

		$announs_query  = $this->db->query($sql_announs);
		$announs = $announs_query->getResultArray();
		
		foreach ($announs as $announ)
		{
			$users = $this->db->table('users')->select('*')->where('users_id', $announ['users_id'])->get()->getRow();
			if($users->picture != "" || $users->picture != NULL)
				$pic_profile = $users->picture;
			else	
				$pic_profile = "";
				
			if($users->city == "" || $users->city == NULL)
				$city = $users->title." at Indonesia";
			else
				$city = $users->title." at ".$users->city;

			$arr_comment = array();
			$arr_timeline[] = array(
				'timeline_id' => $announ->timeline_id,
				'username' => $announ->users->first_name,
				'photo_profile' => $pic_profile,
				'city' => $city,
				'type' => $announ->type,
				'location' => $announ->location,
				'datetime' => $announ->datetime,
				'remark' => $announ->description,
				'photo' => "",
				'total_comment' => "0",
				'comment' => $arr_comment
			);
		}

		foreach ($dataProvider as $timelines)
		{
			$sql_comment = 'SELECT timeline_comment_id, a.timeline_id, a.users_id, comment, `sequence`, a.created_date FROM timelinecomment a 
						JOIN timeline b ON a.timeline_id = b.timeline_id 
						WHERE a.timeline_id = '.$timelines['timeline_id'];
			$sql_comment .= ' ORDER BY a.`created_date` DESC ';
			$sql_comment .= ' LIMIT '.$perPage.' OFFSET '.(($page - 1) * $perPage);

			$query  = $this->db->query($sql_comment);
			$comment_criteria = $query->getResultArray();
			
			$sql_comment_count = 'SELECT COUNT(*) AS count_comment FROM timelinecomment';

			$query_comment_count  = $this->db->query($sql_comment_count);
			$page_comment = $query_comment_count->getRow()->count_comment;
			
			$totalPages_cmnt = ceil($page_comment / $perPage);

			$arr_comment = array();

			foreach ($comment_criteria as $comments)
			{
				$users = $this->db->table('users')->select('*')->where('users_id', $comments['users_id'])->get()->getRow();
			
				if($users->picture != "" || $users->picture != NULL)
					$pic_profiles = $users->picture;
				else
					$pic_profiles = "";

				$arr_comment[] = array(
					'comment_id' => $comments['timeline_comment_id'],
					'username' => $users->first_name,
					'photo_profile' => $pic_profiles,
					'text' => $comments['comment'],
					'datetime' => $comments['created_date']
				);
			}

			$users = $this->db->table('users')->select('*')->where('users_id', $timelines['users_id'])->get()->getRow();
			// echo var_dump($users);exit;
			if($users->picture != "" || $users->picture != NULL)
				$pic_profile = $users->picture;
			else	
				$pic_profile = "";
			
			if($users->city == "" || $users->city == NULL)
				$city = $users->title." at Indonesia";
			else
				$city = $users->title." at ".$users->city;
			// echo var_dump($timelines['location']);exit;
			$arr_timeline[] = array(
				'timeline_id' => $timelines['timeline_id'],
				'username' => $users->first_name,
				'photo_profile' => $pic_profile,
				'city' => $city,
				'type' => $timelines['type'],
				'location' => $timelines['location'],
				'datetime' => $timelines['datetime'],
				'remark' => $timelines['description'],
				'photo' => $timelines['photo'],
				'total_comment' => $totalPages_cmnt,
				'comment' => $arr_comment
			);
		}

		$arrData = array(
			'total_page' => $totalPages,
			'timeline_list' => $arr_timeline,
			'search_by_list' => $search_by_arr
		);

		$this->_sendResponse(200, '', $arrData);
		// return $this->respond($response);
	}

	public function timelineDetail()
	{
		header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);	
		
		$timeline_id = "";
		
		foreach($obj as $var=>$value) 
		{	
			if($var == "timeline_id")
				$timeline_id = $value;	
		}
		$checkId = $this->checkId($timeline_id, "timeline");
		
		$timeline = $this->db->table('timeline')->select('*')->where('timeline_id', $timeline_id)->get()->getRow();
		// echo var_dump($timeline);exit;
		$sql_comment = 'SELECT timeline_comment_id, a.timeline_id, a.users_id, comment, `sequence`, a.created_date FROM timelinecomment a 
							JOIN timeline b ON a.timeline_id = b.timeline_id 
							WHERE a.timeline_id = '.$timeline_id.' ORDER BY a.created_date ASC';
		
		$query  = $this->db->query($sql_comment);
		$comment_criteria = $query->getResultArray();

		$sql_comment_count = 'SELECT COUNT(*) AS count_comment FROM timelinecomment';

		$query_comment_count  = $this->db->query($sql_comment_count);
		$count_comment = $query_comment_count->getRow();

		$comment = new timelinecommentModel();
		
		$perPage = 1000;
		$comment->perPage = $perPage;
		$page_comment = [
			'count' => $comment->paginate($count_comment->count_comment),
			'pager' => $comment->pager,
			'setPerPage' => $comment->perPage

		];
		
		foreach ($comment_criteria as $comments) 
		{
			$users = $this->db->table('users')->select('*')->where('users_id', $comments['users_id'])->get()->getRow();
			
			if($users->picture != "" || $users->picture != NULL)
				$pic_profiles = $users->picture;
			else
				$pic_profiles = "";
			
			$arr_comment = [
				'comment_id' => $comments['timeline_comment_id'],
				'username' => $users->first_name,
				'photo_profile' => $pic_profiles,
				'text' => $comments['comment'],
				'datetime' => $comments['created_date']
			];
			// echo var_dump($arr_comment);exit;
		}

		if($users->picture != "" || $users->picture != NULL)
			$pic_profile = $users->picture;
		else	
			$pic_profile = "";

		$arr_timeline = [
			'timeline_id' => $timeline->timeline_id,
			'username' => $users->first_name,
			'photo_profile' => $pic_profile,
			'type' => $timeline->type,
			'location' => $timeline->location,
			'datetime' => $timeline->created_date,
			'remark' => $timeline->description,
			'photo' => $timeline->photo,
			'total_comment' => $count_comment->count_comment,
			'comment' => $arr_comment
		]; 
		// echo var_dump($arr_timeline['comment']);exit;
		$this->_sendResponse(200, '', $arr_timeline);
	}
}