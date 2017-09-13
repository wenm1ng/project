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
	public function __construct(){
		parent::__construct();
	}
}
?>