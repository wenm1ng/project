<?php
//后台首页父类控制器
namespace base;

use base\Errcode;
use \app\data\model\UserModel;
use \app\admin\model\AdminUserModel;
use \think\Request;
use \think\Session;
use \think\Jump;
use think\Controller;
use app\index\model\User;
class Base_1 extends Controller
{
	public function __construct(){
		parent::__construct();
		// session_start();
		define('UID', self::is_login());
		// error_lo('uid',session::get('uid'));
		if(!UID){
			$this->redirect('admin/Login/login');
		}
	}

	public function is_login(){
		if(!empty(session::get('uid')) && !empty(session::get('sign')) && !empty(session::get('loginName'))){
			if($a = sha1(md5(session::get('loginName')).'wenminghenshuai') == $b = session::get('sign')){
				return session::get('uid');
			}else{
				return 0;
			}
		}else{
			return 0;
		}
	}

	public function obj_to_array($list){
		return json_decode(json_encode($list),true);
	}

}
?>