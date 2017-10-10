<?php
namespace app\index\controller;
use base\Base_2;
use \app\admin\model\UserModel;
use \app\admin\model\AdminUserModel;
use think\Log;
use think\Request;
use think\Db;
use think\Session;
// namespace base;
class Index extends Base_2
{
    public $uid;
    public $uname;
    //前台
    public function index(){
        
        // echo Db::getlastsql();exit;
        // $this->view->info = $userinfo;
        $name = session::get('home_username','home');
    	return view('index',['name'=>$name]);
    }
    
}
