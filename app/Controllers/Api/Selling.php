<?php namespace App\Controllers\Api;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\OrganizationModel;
use App\Models\NotificationModel;
use App\Models\CustomModel;
use App\Models\SellingModel;
use App\Models\SellingTypeModel;
use App\Models\ProgramItemModel;
 
class Selling extends ResourceController
{
	protected $notificationModel;
    use ResponseTrait;

	public function __construct()
    {
        $this->customModel = new CustomModel();
        $this->sellingtype = new SellingTypeModel();
        $this->programitem = new programItemModel();
        $this->selling = new sellingModel();
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
				$ctr = $this->$controller->find($id);
				// $ctr = $this->db->table($controller)->select('*')->where($controller.'_id', $id)->get()->getRow();
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

    public function postSelling()
    {
        header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);	
		
		$token = "";
		$program_id = "";
		$datetime = "";
		$error = "";
		
		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value;
			
			if($var == "program_id")
				$program_id = $value;	
			
			if($var == "datetime")
				$datetime = $value;	
		}

		$check = $this->checkToken($token, $program_id);

        $model = new Selling; 
        $model->program_item_id = $obj->program_product_id;
        $model->unique_number = $obj->unique_code;

        $program = $this->db->table('program')->select('*')->where('program_id', $program_id)->get()->getRow();
        $sellingType = $this->db->table('sellingtype')->select('*')->where('selling_type_id', $obj->selling_type_id)->get()->getRow();

        if($sellingType->name != "Display" && $sellingType->name != "Stock" && $sellingType->name != "Order")
        {
            if($program->product_code_unique == 1)
            {
                if($obj->unique_code == "" || $obj->unique_code == NULL)
                    $this->_sendResponse(500, "Unique Code can't empty", '');
                else
                {
                    $query = 'SELECT * FROM selling a
                            JOIN programitem b ON a.program_item_id = b.program_item_id
                            WHERE a.unique_number ="'.$model->unique_number.'" AND b.program_id ="'.$program_id.'"';
                    
                    $sql  = $this->db->query($query);
		            $selling = $sql->getResultArray();
                    
                    if(count($selling) > 0)
                        $this->_sendResponse(500, "Unique Code already exist", '');

                    $query = 'SELECT b.name AS item, a.program_item_id, a.item_id, total_digit FROM programitem a
                            JOIN item b ON a.item_id = b.item_id
                            WHERE program_item_id = "'.$model->program_item_id.'"';

                    $sql  = $this->db->query($query);
                    $progItem = $sql->getRow();
                    
                    if(strlen($model->unique_number) != $progItem->total_digit && $progItem->total_digit > 0)
                        $this->_sendResponse(500, "Unique Number must ".$progItem->total_digit." digit.", '');
                }
            }
        }

        $selType = $this->checkId($obj->selling_type_id, "sellingtype");
		$usOrgz = $this->checkId($obj->users_organization_id, "usersorganization");
		$progItem = $this->checkId($obj->program_product_id, "programitem");
        
        if($datetime == "" || $datetime == NULL)
            $date = date("Y-m-d H:i");
        else
            $date = $datetime;
            
        $n = date('m',strtotime($date));
        $q = "";
        if($n < 4)
            $q = "1";
        else if($n > 3 && $n <7)
            $q = "2";
        else if($n >6 && $n < 10)
            $q = "3";
        else if($n >9)
            $q = "4";

        $model->selling_date = $date;
        $model->year = date('Y',strtotime($date));
        $model->quarter = $q;
        $model->month = date('m',strtotime($date));
        $model->week = date('W',strtotime($date));
        $model->day = date('d',strtotime($date));
        $model->code = $this->customModel->setCounterNumber('Selling', 'code', 'SLL');

        $codeTml = $this->customModel->setCounterNumber('timeline', 'code', 'TML');

        $urlimgName = "";
        if($obj->picture) 
        {
            $imgName = $model->code.'.jpg';
			$imageData = base64_decode($obj->picture);
			
			$uploadPath = FCPATH .'upload/image/selling/';
			
			helper('filesystem');
			write_file($uploadPath . $imgName, $imageData);

			$urlimgName = base_url()."/upload/image/selling/".$imgName;
        }

        if($obj->revenue == "")
			$model->revenue = "0";

        $saveSll = [
            'selling_type_id' => $obj->selling_type_id,
            'users_organization_id' => $obj->users_organization_id,
            'program_item_id' => $obj->program_product_id,
            'code' => $model->code,
            'selling_date' => $model->selling_date,
            'invoice_number' => $obj->invoice_number,
            'invoice_date' => $obj->invoice_date,
            'unique_number' => $obj->unique_code,
            'year' => $model->year,
            'quarter' => $model->quarter,
            'month' => $model->month,
            'week' => $model->week,
            'day' => $model->day,
            'quantity' => $obj->quantity,
            'revenue' => $obj->revenue,
            'picture' => $urlimgName,
            'remark' => $obj->remark,
            'created_date' => date("Y-m-d H:i:s"),
			'modified_date' => date("Y-m-d H:i:s"),
			'created_by' => $check["users_id"],
			'modified_by' => $check["users_id"]
        ];
        
        if($this->db->table('selling')->insert($saveSll))
        {
            $selling_id = $this->db->insertID();
            
            // query menampilkan nama channel
            $sql = 'SELECT a.`users_organization_id`, c.`organization_id`, c.name AS channel FROM usersorganization a
            JOIN clientorganization b ON a.`client_organization_id` = b.`client_organization_id`
            JOIN organization c ON b.`organization_id` = c.`organization_id`
            WHERE users_organization_id = "'.$usOrgz['id'].'"';

            $query  = $this->db->query($sql);
            $org_name = $query->getRow();
            
            $saveTml = [
                'reference_id' => $selling_id,
                'program_id' => $check['program_id'],
                'users_id' => $check['users_id'],
                'type' => "Selling",
                'code' => $codeTml,
                'datetime' => $model->selling_date,
                'name' => "",
                'location' => $org_name->channel,
                'description' => $obj->remark,
                'photo' => $urlimgName,
                'created_date' => date("Y-m-d H:i:s"),
                'modified_date' => date("Y-m-d H:i:s"),
                'created_by' => $check["users_id"],
                'modified_by' => $check["users_id"]
            ];

            if($this->db->table('timeline')->insert($saveTml)) 
            {
                $this->_sendResponse(200, '', '');
            }
            else
            {
                $this->_sendResponse(500, '', '');
            }
        }
        else
        {
            $this->_sendResponse(500, '', '');
        }
    }

    public function getSelling()
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

        $sql = 'SELECT a.selling_id, g.`username`, e.`name` AS channel_name, f.`name` AS item_name, a.`selling_date`, a.`quantity` FROM selling a
                JOIN programitem b ON a.program_item_id = b.program_item_id
                JOIN usersorganization c ON a.`users_organization_id` = c.`users_organization_id`
                JOIN clientorganization d ON c.`client_organization_id` = d.`client_organization_id`
                JOIN organization e ON d.`organization_id` = e.`organization_id`
                JOIN item f ON b.`item_id` = f.`item_id`
                JOIN users g ON c.`users_id` = g.`users_id`
            WHERE b.program_id = "'.$check['program_id'].'"';

        $autorize = $this->db->table('usersgroupprogram')->select('*')
        ->where('users_id', $check['users_id'])->get()->getRow();

        if($autorize)
        {
            if($autorize->data_level == "low")
                $sql .= 'AND a.created_by = "'.$check['users_id'].'"';
        }

        $sql .= ' ORDER BY a.`created_date` DESC, a.selling_id DESC';
        $sql .= ' LIMIT '.$perPage.' OFFSET '.(($page - 1) * $perPage);

        $query  = $this->db->query($sql);
        $criteria = $query->getResultArray();
        
        $sqls = 'SELECT  COUNT(*) AS counts FROM selling a
            JOIN programitem b ON a.program_item_id = b.program_item_id
            WHERE b.program_id = "'.$check['program_id'].'"';

        $sqls    = $this->db->query($sqls);
        $count = $sqls->getRow()->counts;

        $totalPages = ceil($count / $perPage);
        
        $dataArr = array();
		foreach ($criteria as $models)
		{
			$dataArr[] = array(
                'selling_id' => $models['selling_id'],
                'username' => $models['username'],
                'channel_name' => $models['channel_name'],
                'product_name' => $models['item_name'],
                'selling_date' => $models['selling_date'],
                'quantity' => $models['quantity'],
            );
		}

        $arrData = array(
            'total_page' => $totalPages,
            'selling_list' => $dataArr,
        );

        $this->_sendResponse(200, '', $arrData);
    }

    public function sellingDetail()
    {
        header("content-type: application/json");
		$json = file_get_contents('php://input');
		$obj = json_decode($json);
		
		$token = "";
		$program_id = "";
		$selling_id = "";
		$error = "";
		
		foreach($obj as $var=>$value) 
		{
			if($var == "token")
				$token = $value;
			
			if($var == "program_id")
				$program_id = $value;	
			
			if($var == "selling_id")
				$selling_id = $value;	
		}
		$check = $this->checkToken($token, $program_id);
		$checkId = $this->checkId($selling_id, "selling");
        
        $sql = 'SELECT a.selling_id, g.`username`, e.`name` AS channel_name, f.`name` AS item_name, 
                a.`selling_date`, a.`quantity`, h.name AS type_name, a.revenue, a.unique_number, a.picture, a.remark FROM selling a
                JOIN programitem b ON a.program_item_id = b.program_item_id
                JOIN usersorganization c ON a.`users_organization_id` = c.`users_organization_id`
                JOIN clientorganization d ON c.`client_organization_id` = d.`client_organization_id`
                JOIN organization e ON d.`organization_id` = e.`organization_id`
                JOIN item f ON b.`item_id` = f.`item_id`
                JOIN users g ON c.`users_id` = g.`users_id`
                JOIN sellingtype h ON a.selling_type_id = h.selling_type_id
                WHERE selling_id = "'.$checkId["id"].'"';
        
        $sqls    = $this->db->query($sql);
        $selling = $sqls->getRow();
        
        $arr[] = array (
            'selling_id' => $selling->selling_id,
            'channel_name' => $selling->channel_name,
            'selling_type' => $selling->type_name,
            'product_name' => $selling->item_name,
            'selling_date' => $selling->selling_date,
            'quantity' => $selling->quantity,
            'revenue' => $selling->revenue,
            'unique_number' => $selling->unique_number,
            'picture' => $selling->picture,
            'remark' => $selling->remark,
        );
 
        $this->_sendResponse(200, '', $arr);
    }
}