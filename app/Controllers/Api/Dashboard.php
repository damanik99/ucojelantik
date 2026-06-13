<?php namespace App\Controllers\Api;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ActivityModel;
use App\Models\OrganizationModel;
use App\Models\NotificationModel;
use App\Models\CustomModel;
 
class Dashboard extends ResourceController
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

    public function listUsers()
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

		$autorize = $this->db->table('usersgroupprogram')->select('*')->where('users_id', $check['users_id'])->get()->getRow();
		
		if($autorize)
		{
			if($autorize->data_level == "low")
				$condition = 'a.users_id = "'.$check['users_id'].'" AND program_id = "'.$check['program_id'].'"';
			else	
				$condition = 'program_id = "'.$check['program_id'].'"';

			// echo var_dump($condition);exit;
		}
		else
		{
			$condition = 'program_id ="'.$check['program_id'].'"'; 
		}

		$query = 'SELECT * FROM usersgroupprogram a WHERE '.$condition;

		$sql    = $this->db->query($query);
		$runSql = $sql->getResultArray();
		
		$suggest = array();
		foreach($runSql as $model)
		{
			$users = $this->db->table('users')->select('*')->where('users_id', $model['users_id'])->get()->getRow();
			
			$suggest = [
				'users_id'=>$users->users_id,
				'name'=>$users->first_name." [ ".$users->title." ] ", 
			];
		}
		$this->_sendResponse(200, '', $suggest);
    }

	public function getTabAttendance()
	{
		header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);	
		
		$token = "";
		$program_id = "";
		$user_select = "";
		$view_by = "";
		$error = "";
		
		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value;
			
			if($var == "program_id")
				$program_id = $value;	
			
			if($var == "user_select")
				$user_select = $value;
			
			if($var == "view_by")
				$view_by = $value;
		}

		$check = $this->checkToken($token, $program_id);

		$dataArr[] = array();
		$label = array();
		$nilai = array();
		
		$query = "SELECT COUNT(a.users_id) AS counts FROM users a JOIN usersgroupprogram b ON a.users_id = b.users_id ";
		$query .= "WHERE active = 1 AND b.program_id = ".$check['program_id'] ;
		
		if($user_select != "")
			$query .= " AND a.users_id = ".$user_select;
		
		$sql    = $this->db->query($query);
		$total_ae = $sql->getRow();
		
		$title = 'Actual Attendance ('.$total_ae->counts.' users active)';

		if($view_by == "weekly")
		{
			$query = 'SELECT WEEK(dte) AS Weeks FROM ( ';
			$query .= 'SELECT CONCAT(YEAR(CURDATE()),"-",MONTH(CURDATE()),"-01") + INTERVAL a + b DAY AS dte ';
			$query .= 'FROM (SELECT 0 a UNION SELECT 1 a UNION SELECT 2 UNION SELECT 3 ';
			$query .= 'UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 ';
			$query .= 'UNION SELECT 8 UNION SELECT 9 ) d, ( ';
			$query .= 'SELECT 0 b UNION SELECT 10 UNION SELECT 20 UNION SELECT 30 UNION SELECT 40  UNION SELECT 50) m ';
			$query .= 'WHERE CONCAT(YEAR(CURDATE()),"-",MONTH(CURDATE()),"-01") + INTERVAL a + b DAY  <=  DATE(NOW()) ';
			$query .= 'ORDER BY dte) alias ';
			$query .= 'GROUP BY WEEK(dte) ';
			
			$values    = $this->db->query($query);
			$value_x = $values->getResultArray();
			// echo var_dump($value_x);exit;
			foreach ($value_x as $i => $ii) 
			{
				$label[$i] = "Week ".$ii['Weeks'];
			}
			
			$query_y = 'SELECT total FROM (';
			$query_y .= $query;
			$query_y .= ') a LEFT JOIN (';
			$query_y .= 'SELECT WEEK(dte) AS weeks, COUNT(users_id) AS total FROM ( ';
			$query_y .= 'SELECT DATE(a.datetime) AS dte, a.users_id ';
			$query_y .= 'FROM attendance a JOIN users b ON a.users_id = b.users_id ';
			$query_y .= 'WHERE program_id = '.$check['program_id'].' AND b.active = 1 '; 
			$query_y .= 'AND MONTH(a.datetime) = (MONTH(CURDATE())) AND YEAR(a.datetime) = YEAR(CURDATE()) ';
			
			if($user_select != "")
				$query_y .= 'AND a.users_id = '.$user_select;
				
			$query_y .= ' GROUP BY DATE(a.datetime), a.users_id ';
			$query_y .= ') a ';
			$query_y .= 'GROUP by WEEK(dte) ';
			$query_y .= ') b ON a.weeks = b.weeks ';
			
			$values_y    = $this->db->query($query_y);
			$value_y = $values_y->getResultArray();
			
			foreach ($value_y as $i => $ii) 
			{
				$perc = ((int)$ii['total'] / ($total_ae->counts * 7)) * 100;
				$nilai[$i] = round($perc);
			}
		}

		if($view_by == "monthly")
		{
			$value_x = 'SELECT MONTH(CONCAT(YEAR(CURRENT_DATE),"-",m,"-01")) AS monthss, ';
			$value_x .= 'MONTHNAME(CONCAT(YEAR(CURRENT_DATE),"-",m,"-01")) AS months ';
			$value_x .= 'FROM ';
			$value_x .= '(SELECT 1 m '; 
			$value_x .= 'UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 ';
			$value_x .= 'UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 ';
			$value_x .= 'UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12) months ';
			$value_x .= 'WHERE m <= MONTH(CURRENT_DATE) ';

			$value_xs    = $this->db->query($value_x);
			$value_m = $value_xs->getResultArray();

			foreach($value_m as $i=>$ii)
			{
				$label[$i] = $ii['months'];
			}
			
			$value_y = 'SELECT a.monthss, a.months, IFNULL(b.total, 0) as total FROM ( ';
			$value_y .= $value_m;
			$value_y .= ') a LEFT JOIN ( ';

			$value_y .= 'SELECT MONTH(DATE) AS monthss, MONTHNAME(DATE) AS months, COUNT(users_id) AS total FROM ( ';
			$value_y .= 'SELECT DATE(a.datetime) AS DATE, a.users_id ';
			$value_y .= 'FROM attendance a JOIN users b ON a.users_id = b.users_id ';
			$value_y .= 'WHERE program_id = '.$check['program_id'].' AND b.active = 1 '; 
			$value_y .= 'AND YEAR(a.datetime) = YEAR(CURDATE()) ';
			
			if($user_select != "")
				$value_y .= 'AND a.users_id = '.$user_select;
				
			$value_y .= ' GROUP BY DATE(a.datetime), a.users_id ';
			$value_y .= ') a ';
			$value_y .= 'GROUP BY MONTH(DATE) ';
			
			$value_y .= ') b ON a.months = b.months ';

			$value_ys    = $this->db->query($value_y);
			$value_my = $value_ys->getResultArray();

			foreach($value_my as $i=>$ii)
			{
				$d = cal_days_in_month(CAL_GREGORIAN, $ii['monthss'], date("Y"));
				$perc = ((int)$ii['total'] / ($total_ae->counts * $d)) * 100;
				$nilai[$i] = round($perc);
				// echo var_dump($nilai[$i]);exit;
			}
		}

		$dataArr = [
			'title' => $title,
			'label' => $label,
			'value' => $nilai,
		]; 
		
		$this->_sendResponse(200, '', $dataArr);
	}

	public function getTabVisit()
	{
		header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);	
		
		$token = "";
		$program_id = "";
		$user_select = "";
		$view_by = "";
		$error = "";
		
		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value;
			
			if($var == "program_id")
				$program_id = $value;	
			
			if($var == "user_select")
				$user_select = $value;
			
			if($var == "view_by")
				$view_by = $value;
		}
		
		$check = $this->checkToken($token, $program_id);

		$dataArr[] = array();
		$label = array();
		$nilai = array();

		$query = "SELECT COUNT(a.users_id) AS counts FROM users a JOIN usersgroupprogram b ON a.users_id = b.users_id ";
		$query .= "WHERE active = 1 AND b.program_id = ".$check['program_id'] ;
		
		if($user_select != "")
			$query .= " AND a.users_id = ".$user_select;

		$querys    = $this->db->query($query);
		$total_ae = $querys->getRow();
		
		$title = 'Roadmap vs Actual Attendance ('.$total_ae->counts.' users active)';

		if($view_by == "weekly")
		{
			$value_x = 'SELECT WEEK(dte) AS Weeks FROM ( ';
			$value_x .= 'SELECT CONCAT(YEAR(CURDATE()),"-",MONTH(CURDATE()),"-01") + INTERVAL a + b DAY AS dte ';
			$value_x .= 'FROM (SELECT 0 a UNION SELECT 1 a UNION SELECT 2 UNION SELECT 3 ';
			$value_x .= 'UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 ';
			$value_x .= 'UNION SELECT 8 UNION SELECT 9 ) d, ( ';
			$value_x .= 'SELECT 0 b UNION SELECT 10 UNION SELECT 20 UNION SELECT 30 UNION SELECT 40  UNION SELECT 50) m ';
			$value_x .= 'WHERE CONCAT(YEAR(CURDATE()),"-",MONTH(CURDATE()),"-01") + INTERVAL a + b DAY  <=  DATE(NOW()) ';
			$value_x .= 'ORDER BY dte) alias ';
			$value_x .= 'GROUP BY WEEK(dte) ';

			$value_xs    = $this->db->query($value_x);
			$value_w = $value_xs->getResultArray();
			
			foreach($value_w as $i=>$ii)
			{
				$label[$i] = "Week ".$ii['Weeks'];
			}
			
			$value_y = 'SELECT IFNULL(percentage, 0) as percentage FROM (';
			
			$value_y .= $value_x;
			$value_y .= ') a LEFT JOIN (';
			
			$value_y .= 'SELECT a.weeks, ROUND((IFNULL(result, 0) * 100) / target) AS percentage  FROM ( ';
			$value_y .= 'SELECT weeks, COUNT(*) AS target FROM ( ';
			$value_y .= 'SELECT WEEK(`datetime`) AS weeks, users_id, client_organization_id  FROM roadmap ';
			$value_y .= 'WHERE program_id = '.$check['program_id'].' AND YEAR(`datetime`) = YEAR(CURDATE()) AND MONTH(`datetime`) = MONTH(CURDATE()) ';
			
			if($user_select != "")
				$value_y .= 'AND users_id = '.$user_select;
				
			$value_y .= ' GROUP BY WEEK(`datetime`), users_id, client_organization_id ';
			$value_y .= ') a ';
			$value_y .= 'GROUP BY weeks ';
			$value_y .= ') a LEFT JOIN ( ';
			$value_y .= 'SELECT weeks, COUNT(*) AS result FROM ( ';
			$value_y .= 'SELECT WEEK(`datetime`) AS weeks, a.users_id, b.client_organization_id ';
			$value_y .= 'FROM attendance a JOIN usersorganization b ON a.users_organization_id = b.users_organization_id ';
			$value_y .= 'WHERE a.program_id = '.$check['program_id'].' AND YEAR(`datetime`) = YEAR(CURDATE()) AND MONTH(`datetime`) = MONTH(CURDATE()) ';
			
			if($user_select != "")
				$value_y .= ' AND a.users_id = '.$user_select;
				
			$value_y .= ' GROUP BY WEEK(`datetime`), a.users_id, b.client_organization_id ';
			$value_y .= ') a ';
			$value_y .= 'GROUP BY weeks ';
			$value_y .= ') b ON a.weeks = b.weeks ';
			$value_y .= ') b ON a.weeks = b.weeks ';

			
			$value_ys    = $this->db->query($value_y);
			$value_ws = $value_ys->getResultArray();
			
			foreach($value_ws as $i=>$ii)
			{
				$nilai[$i] = $ii['percentage'];
				
			}
		}

		if($view_by == "monthly")
		{
			$value_x = 'SELECT MONTHNAME(CONCAT(YEAR(CURRENT_DATE),"-",m,"-01")) AS months ';
			$value_x .= 'FROM ';
			$value_x .= '(SELECT 1 m '; 
			$value_x .= 'UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 ';
			$value_x .= 'UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 ';
			$value_x .= 'UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12) months ';
			$value_x .= 'WHERE m <= MONTH(CURRENT_DATE) ';

			$value_xs    = $this->db->query($value_x);
			$value_my = $value_xs->getResultArray();

			foreach($value_my as $i=>$ii)
			{
				$label[$i] = $ii['months'];
			}

			$value_y = 'SELECT IFNULL(percentage, 0) as percentage FROM (';
			$value_y .= $value_x;
			$value_y .= ') a LEFT JOIN (';
			
			$value_y .= 'SELECT a.months, ROUND((IFNULL(result, 0) * 100) / target) AS percentage  FROM ( ';
			$value_y .= 'SELECT months, COUNT(*) AS target FROM ( ';
			$value_y .= 'SELECT MONTHNAME(`datetime`) AS months, users_id, client_organization_id  FROM roadmap ';
			$value_y .= 'WHERE program_id = '.$check['program_id'].' AND YEAR(`datetime`) = YEAR(CURDATE()) AND MONTH(`datetime`) = MONTH(CURDATE()) ';
			
			if($user_select != "")
				$value_y .= 'AND users_id = '.$user_select;
				
			$value_y .= ' GROUP BY MONTHNAME(`datetime`), users_id, client_organization_id ';
			$value_y .= ') a ';
			$value_y .= 'GROUP BY months ';
			$value_y .= ') a LEFT JOIN ( ';
			$value_y .= 'SELECT months, COUNT(*) AS result FROM ( ';
			$value_y .= 'SELECT MONTHNAME(`datetime`) AS months, a.users_id, b.client_organization_id ';
			$value_y .= 'FROM attendance a JOIN usersorganization b ON a.users_organization_id = b.users_organization_id ';
			$value_y .= 'WHERE a.program_id = '.$check['program_id'].' AND YEAR(`datetime`) = YEAR(CURDATE()) AND MONTH(`datetime`) = MONTH(CURDATE()) ';
			
			if($user_select != "")
				$value_y .= ' AND a.users_id = '.$user_select;
				
			$value_y .= ' GROUP BY MONTHNAME(`datetime`), a.users_id, b.client_organization_id ';
			$value_y .= ') a ';
			$value_y .= 'GROUP BY months ';
			$value_y .= ') b ON a.months = b.months ';
			$value_y .= ') b ON a.months = b.months ';

			$value_ys    = $this->db->query($value_y);
			$value_ms = $value_ys->getResultArray();

			foreach($value_ms as $i=>$ii)
			{
				$nilai[$i] = $ii['percentage'];
			}
		}

		$dataArr = [
			'title' => $title,
			'label' => $label,
			'value' => $nilai,
		];
		
		$this->_sendResponse(200, '', $dataArr);
	}

	public function getTabDistribution()
	{
		header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);	
		
		$token = "";
		$program_id = "";
		$user_select = "";
		$view_by = "";
		$error = "";
		
		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value;
			
			if($var == "program_id")
				$program_id = $value;	
			
			if($var == "user_select")
				$user_select = $value;
			
			if($var == "view_by")
				$view_by = $value;
		}

		$check = $this->checkToken($token, $program_id);

		$dataArr[] = array();
		$label = array();
		$nilai = array();
		
		$query = "SELECT COUNT(organization_id) as Total FROM ( ";
		$query .= "SELECT c.organization_id ";
		$query .= "FROM usersgroupprogram a JOIN usersorganization b ON a.users_id = b.users_id ";
		$query .= "JOIN clientorganization c ON b.client_organization_id = c.client_organization_id ";
		$query .= "JOIN users d ON a.users_id = d.users_id JOIN `status` e ON b.status_id = e.status_id ";
		$query .= "WHERE e.code = 'UORG_ACTIVE' AND d.active = 1 AND program_id = ".$check['program_id'];
		
		if($user_select != "")
			$query .= " AND d.users_id = ".$user_select;
		
		$query .= "  GROUP BY c.organization_id ";
		$query .= ") a ";

		$querys    = $this->db->query($query);
		$total_channel = $querys->getRow();
		
		$title = 'Channel Distribution ('.$total_channel->Total.' Channel Active)';

		if($view_by == "weekly")
		{
			$value_x = 'SELECT WEEK(dte) AS weeks FROM ( ';
			$value_x .= 'SELECT CONCAT(YEAR(CURDATE()),"-",MONTH(CURDATE()),"-01") + INTERVAL a + b DAY AS dte ';
			$value_x .= 'FROM (SELECT 0 a UNION SELECT 1 a UNION SELECT 2 UNION SELECT 3 ';
			$value_x .= 'UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 ';
			$value_x .= 'UNION SELECT 8 UNION SELECT 9 ) d, ( ';
			$value_x .= 'SELECT 0 b UNION SELECT 10 UNION SELECT 20 UNION SELECT 30 UNION SELECT 40  UNION SELECT 50) m ';
			$value_x .= 'WHERE CONCAT(YEAR(CURDATE()),"-",MONTH(CURDATE()),"-01") + INTERVAL a + b DAY  <=  DATE(NOW()) ';
			$value_x .= 'ORDER BY dte) alias ';
			$value_x .= 'GROUP BY WEEK(dte) ';

			$value_xs    = $this->db->query($value_x);
			$value_wx = $value_xs->getResultArray();
			
			foreach($value_wx as $i=>$ii)
			{
				$label[$i] = "Weeks ".$ii['weeks'];
			}

			$value_y = 'SELECT a.weeks, IFNULL(b.total, 0) AS total FROM ( ';
			$value_y .= $value_x;
			$value_y .= ') a LEFT JOIN ( ';
		
			$value_y .= 'SELECT WEEK(dte) AS weeks, COUNT(users_organization_id) AS total FROM ( ';
			$value_y .= 'SELECT DATE(a.distribution_date) AS dte, a.users_organization_id ';
			$value_y .= 'FROM merchandisedistribution a ';
			$value_y .= 'JOIN programitem b ON a.program_item_id = b.program_item_id ';
			$value_y .= 'JOIN usersorganization c ON a.users_organization_id = c.users_organization_id ';
			$value_y .= 'JOIN users d ON c.users_id = d.users_id ';
			$value_y .= 'WHERE program_id = '.$check['program_id'].' AND d.active = 1 '; 
			$value_y .= 'AND MONTH(a.distribution_date) = (MONTH(CURDATE())) AND YEAR(a.distribution_date) = YEAR(CURDATE()) '; 
			
			if($user_select != "")
				$value_y .= 'AND d.users_id = '.$user_select;
				
			$value_y .= ' GROUP BY DATE(a.distribution_date), a.users_organization_id ';
			$value_y .= ') a ';
			$value_y .= 'GROUP by WEEK(dte) ';
			$value_y .= ') b ON a.weeks = b.weeks ';

			$value_ys    = $this->db->query($value_y);
			$value_yx = $value_ys->getResultArray();

			foreach($value_yx as $i=>$ii)
			{
				$nilai[$i] = $ii['total'];
			}
		}

		if($view_by == "monthly")
		{
			$value_x = 'SELECT MONTHNAME(CONCAT(YEAR(CURRENT_DATE),"-",m,"-01")) AS months ';
			$value_x .= 'FROM ';
			$value_x .= '(SELECT 1 m '; 
			$value_x .= 'UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 ';
			$value_x .= 'UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 ';
			$value_x .= 'UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12) months ';
			$value_x .= 'WHERE m <= MONTH(CURRENT_DATE) ';

			$value_xs    = $this->db->query($value_x);
			$value_mx = $value_xs->getResultArray();

			foreach($value_mx as $i=>$ii)
			{
				$label[$i] = $ii['months'];
			}

			$value_y = 'SELECT a.months, IFNULL(b.total, 0) as total FROM ( ';
			$value_y .= $value_x;
			$value_y .= ') a LEFT JOIN ( ';

			$value_y .= 'SELECT MONTHNAME(dte) AS months, COUNT(users_organization_id) AS total FROM ( ';
			$value_y .= 'SELECT DATE(a.distribution_date) AS dte, a.users_organization_id ';
			$value_y .= 'FROM merchandisedistribution a ';
			$value_y .= 'JOIN programitem b ON a.program_item_id = b.program_item_id ';
			$value_y .= 'JOIN usersorganization c ON a.users_organization_id = c.users_organization_id ';
			$value_y .= 'JOIN users d ON c.users_id = d.users_id ';
			$value_y .= 'WHERE program_id = '.$check['program_id'].' AND d.active = 1 '; 
			$value_y .= 'AND YEAR(a.distribution_date) = YEAR(CURDATE()) '; 
			
			if($user_select != "")
				$value_y .= 'AND d.users_id = '.$user_select;
				
			$value_y .= ' GROUP BY DATE(a.distribution_date), a.users_organization_id ';
			$value_y .= ') a ';
			$value_y .= 'GROUP BY MONTH(dte) ';
			$value_y .= ') b ON a.months = b.months ';

			$value_ys    = $this->db->query($value_y);
			$value_yx = $value_ys->getResultArray();

			foreach($value_yx as $i=>$ii)
			{
				$nilai[$i] = $ii['total'];
			}
		}

		$dataArr = [
			'title' => $title,
			'label' => $label,
			'value' => $nilai,
		];
		
		$this->_sendResponse(200, '', $dataArr);
	}

	public function getTabSelling()
	{
		header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);	
		
		$token = "";
		$program_id = "";
		$user_select = "";
		$view_by = "";
		$error = "";
		
		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value;
			
			if($var == "program_id")
				$program_id = $value;	
			
			if($var == "user_select")
				$user_select = $value;
			
			if($var == "view_by")
				$view_by = $value;
		}
		
		$check = $this->checkToken($token, $program_id);
		
		$dataArr[] = array();
		$label = array();
		$nilai = array();
		
		$query = "SELECT COUNT(a.users_id) AS count_users FROM users a JOIN usersgroupprogram b ON a.users_id = b.users_id ";
		$query .= "WHERE active = 1 AND b.program_id = ".$check['program_id'] ;
		
		if($user_select != "")
			$query .= " AND a.users_id = ".$user_select;

		$querys    = $this->db->query($query);
		$total_ae = $querys->getRow();
		
		$title = 'Actual Selling ('.$total_ae->count_users.' users active)';
		
		if($view_by == "weekly")
		{
			$value_x = 'SELECT WEEK(dte) AS weeks FROM ( ';
			$value_x .= 'SELECT CONCAT(YEAR(CURDATE()),"-",MONTH(CURDATE()),"-01") + INTERVAL a + b DAY AS dte ';
			$value_x .= 'FROM (SELECT 0 a UNION SELECT 1 a UNION SELECT 2 UNION SELECT 3 ';
			$value_x .= 'UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 ';
			$value_x .= 'UNION SELECT 8 UNION SELECT 9 ) d, ( ';
			$value_x .= 'SELECT 0 b UNION SELECT 10 UNION SELECT 20 UNION SELECT 30 UNION SELECT 40  UNION SELECT 50) m ';
			$value_x .= 'WHERE CONCAT(YEAR(CURDATE()),"-",MONTH(CURDATE()),"-01") + INTERVAL a + b DAY  <=  DATE(NOW()) ';
			$value_x .= 'ORDER BY dte) alias ';
			$value_x .= 'GROUP BY WEEK(dte) ';

			$value_xs    = $this->db->query($value_x);
			$value_wx = $value_xs->getResultArray();

			foreach($value_wx as $i=>$ii)
			{
				$label[$i] = "Week ".$ii['weeks'];
			}

			$value_y = 'SELECT iFNULL(total, 0) AS total FROM ( ';
			$value_y .= $value_x;
			$value_y .= ') a LEFT JOIN ( ';
			
			$value_y .= 'SELECT WEEK(a.selling_date) AS weeks, count(selling_id) as total '; 
			$value_y .= 'FROM selling a LEFT JOIN programitem b ON a.program_item_id = b.program_item_id ';
			$value_y .= 'JOIN usersorganization c on a.users_organization_id = c.users_organization_id ';
			$value_y .= 'JOIN users d on c.users_id = d.users_id ';
			$value_y .= 'WHERE MONTH(a.selling_date) = MONTH(CURDATE()) AND YEAR(a.selling_date) = YEAR(CURDATE()) '; 
			$value_y .= 'AND b.program_id = '.$check['program_id'];
			
			if($user_select != "")
				$value_y .= ' AND d.users_id = '.$user_select;
			
			$value_y .= ' GROUP BY WEEK(a.selling_date) ';
			$value_y .= ') b ON a.weeks = b.weeks ';

			$value_ys    = $this->db->query($value_y);
			$value_wy = $value_ys->getResultArray();
			
			foreach($value_wy as $i=>$ii)
			{
				$nilai[$i] = (int)$ii['total'];
			}
		}

		if($view_by == "monthly")
		{
			$value_x = 'SELECT MONTHNAME(CONCAT(YEAR(CURRENT_DATE),"-",m,"-01")) AS months ';
			$value_x .= 'FROM ';
			$value_x .= '(SELECT 1 m '; 
			$value_x .= 'UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 ';
			$value_x .= 'UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 ';
			$value_x .= 'UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12) months ';
			$value_x .= 'WHERE m <= MONTH(CURRENT_DATE) ';

			$value_xs    = $this->db->query($value_x);
			$value_mx = $value_xs->getResultArray();

			foreach($value_mx as $i=>$ii)
			{
				$label[$i] = $ii['months'];
			}

			$value_y = 'SELECT IFNULL(b.total, 0) as total FROM ( ';
			$value_y .= $value_x;
			$value_y .= ') a LEFT JOIN ( ';
		
			$value_y .= 'SELECT MONTHNAME(a.selling_date) as months, SUM(a.quantity) AS total '; 
			$value_y .= 'FROM selling a LEFT JOIN programitem b ON a.program_item_id = b.program_item_id ';
			$value_y .= 'JOIN usersorganization c ON a.users_organization_id = c.users_organization_id ';
			$value_y .= 'JOIN users d ON c.users_id = d.users_id ';
			$value_y .= 'WHERE YEAR(a.selling_date) = YEAR(CURDATE()) AND b.program_id = '.$check['program_id'];
			
			if($user_select != "")
				$value_y .= ' AND d.users_id = '.$user_select;
				
			$value_y .= ' GROUP BY Month ';
			$value_y .= 'ORDER BY MONTH(a.selling_date) ';
			$value_y .= ') b ON a.months = b.months ';

			$value_ys    = $this->db->query($value_y);
			$value_my = $value_ys->getResultArray();

			foreach($value_my as $i=>$ii)
			{
				$nilai[$i] = (int)$ii['total'];
			}
		}

		$dataArr = [
			'title' => $title,
			'label' => $label,
			'value' => $nilai,
		];
		
		$this->_sendResponse(200, '', $dataArr);
	}
}