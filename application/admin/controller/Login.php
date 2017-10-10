<?php
namespace app\admin\controller;

use \base\Base_1;
use think\Db;
use think\Controller;
use think\Session;
use think\Request;
class Login extends Controller
{
    
    public function __construct(){
        parent::__construct();
        define('UID',session::get('uid'));
        $this->username = session::get('username');
    }

    public function login(){
    	if(Request::instance()->isPost()){
    		if(empty(input('loginName'))){
	            $this->error('请填写登录账号！');
	        }
	        if(empty(input('password'))){
	            $this->error('请填写登录密码！');
	        }
	        // print_r(111);exit;
	        $table_name = 'user';
	        $where = array(
	            'loginName' => input('loginName'),
	            );
	        $info = Db::name($table_name)->where($where)->find();
	        if(!empty($info)){
	            if($info['is_admin'] == 1){
	                if($info['password'] == input('password')){
	                    session::set('uid',$info['user_id']);
	                    session::set('loginName',$info['loginName']);
	                    session::set('username',$info['user_name']);
	                    session::set('sign',sha1(md5($info['loginName']).'wenminghenshuai'));
	                    
	                    $this->success('登录成功',url('admin/index/home'));
	                }else{
	                    $this->error('密码错误');
	                }
	            }else{
	                $this->error('您没有权限！');
	            }
	        }else{
	            $this->error('该用户不存在！');
	        }
    	}else{
        	return view('login');
    	}
        
    }

    /**
     * 退出登录
     * @return \think\response\Json
     */
    public function logout()
    {
        // $this->model = new AdminUserModel();
        session(null);
        if (!session::has('uid')) {
            // return json(self::sucres());
            $this->success('退出登录成功!',url('admin/login/login'));
        } else {
            // return json(self::erres("退出登录失败"));
            $this->error('退出登录失败!');
        }
    }
}
