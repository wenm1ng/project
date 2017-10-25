<?php
namespace base;

use base\Errcode;
use \app\data\model\UserModel;
use \app\admin\model\AdminUserModel;
use \think\Request;
use \think\Session;
use \think\Jump;
use think\Controller;
use app\index\model\User;
class Base_2 extends Controller
{
	public $uid;
	public $username;

	public function __construct(){
		parent::__construct();
		$this->uid = !empty(session::get('home_uid'))?session::get('home_uid'):"";
		$this->username = !empty(session::get('home_username'))?session::get('home_username'):"";
	}

	public function obj_to_array($list){
		return json_decode(json_encode($list),true);
	}
}
?>