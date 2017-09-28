<?php 
	namespace app\home\controller;

	use think\Db;
	use think\Controller;

	Class User extends Controller{
		

		//前台登录
		public function login(){
			if(empty(input('loginName'))){
	            $this->error('请填写登录账号！');exit;
	        }
	        if(empty(input('password'))){
	            $this->error('请填写登录密码！');exit;
	        }
	        // print_r(111);exit;
	        $table_name = 'user';
	        $where = array(
	            'loginName' => input('loginName'),
	            );
	        $info = Db::name($table_name)->where($where)->find();
	        if(!empty($info)){
                if($info['password'] == input('password')){
                    session::set('home_uid',$info['user_id']);
                    session::set('home_loginName',$info['loginName']);
                    session::set('home_username',$info['user_name']);
                    session::set('home_sign',sha1(md5($info['loginName']).'wenminghenshuai'));
                    
                    $this->redirect();
                }else{
                    $this->error('密码错误');
                }
	        }else{
	            $this->error('该用户不存在！');
	        }
		}
	}
 ?>