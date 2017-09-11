<?php
namespace app\index\controller;
use base\Base_1;
use \app\admin\model\UserModel;
use \app\admin\model\AdminUserModel;
use think\Log;
use think\Request;
use think\Db;
use think\Session;
// namespace base;
class Index extends Base_1
{
    public $uid;
    public $uname;
    public function index(){
        
        // echo Db::getlastsql();exit;
        // $this->view->info = $userinfo;
    	return view('index');
    }
    //头部视图
    public function head(){
        return view('Public/head');
    }
    //菜单视图
    public function menu(){
        return view('Public/menu');
    }
    //详细视图
    public function main(){
        return view('Public/main');
    }

    public function test(){
        return view();
    }
}
