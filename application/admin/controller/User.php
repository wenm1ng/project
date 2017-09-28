<?php
namespace app\admin\controller;

use base\Base_1;
use \app\admin\model\UserModel;
use \app\admin\model\AdminUserModel;
use think\Log;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Page;

class User extends Base_1
{
    
    

    //用户列表
    // public function index(){
    //     if(UID){
    //         $table_name = "user";
    //         $list = Db::name($table_name)->select();
    //         //把分页数据赋值给模板变量
    //         $p = new \think\Page($list,2);
    //         // $list =$this->obj_to_array($list);
    //         // $this->assign('list',$list);
    //         $this->assign('p',$p);

    //         // print_r($p);exit;
    //         return $this->fetch();
    //     }else{
    //         $this->redirect('admin');
    //     }
    // }
    public function index(){
        if(UID){
            $table_name = "user";
            $list = Db::name($table_name)->paginate(10);
            //把分页数据赋值给模板变量
            // $p = new \think\Page($list,2);
            $this->assign('page',$list);
            $list =$this->obj_to_array($list);
            $this->assign('list',$list);
            $this->assign('meta_title','用户列表');

            // print_r($list);exit;
            return $this->fetch();
        }else{
            $this->redirect('admin');
        }
    }

    //将对象转为数组
    public function obj_to_array($list){
        return json_decode(json_encode($list),true);
    }

    public function addinfo(){
        if(Request::instance()->isPost()){
            if(empty($_POST['loginName'])){
                $this->error('用户名不能为空！');exit;
            }
            if(empty($_POST['user_name'])){
                $this->error('昵称不能为空！');exit;
            }
            if(empty($_POST['password'])){
                $this->error('密码不能为空！');exit;
            }
            if(empty($_POST['repassword'])){
                $this->error('确认密码不能为空！');exit;
            }
            if($_POST['password'] != $_POST['repassword']){
                $this->error('两次密码不一致');exit;
            }
            if(empty($_POST['email'])){
                $this->error('邮箱不能为空！');exit;
            }

            $email_info = Db::name('user')->where("email",'=',"{$_POST['email']}")->find();
            if(!empty($email_info)){
                $this->error('该邮箱已被注册！');exit;
            }

            if(empty($_POST['phone'])){
                $this->error('手机号不能为空！');exit;
            }
            $phone_info = Db::name('user')->where("phone",'=',"{$_POST['phone']}")->find();
            if(!empty($phone_info)){
                $this->error('该手机号已被注册！');exit;
            }

            if(empty($_POST['sex'])){
                $this->error('性别不能为空！');exit;
            }
            
            $data = array(
                'loginName' => $_POST['loginName'],
                'password' => $_POST['password'],
                'user_name' => $_POST['user_name'],
                'is_admin' => 1,
                'status' => 1,
                'sex' => $_POST['sex'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'],
                'create_time' => date('Y-m-d')
                );

            //判断是否存在该用户名的用户
            $info = Db::name('user')->where("loginName",'=',"{$_POST['loginName']}")->find();

            if(empty($info)){
                $result = Db::name('user')->insert($data);
                if($result){
                    $this->success('添加用户成功','index');
                }else{
                    log_error('fail_sql',Db::getlastsql());
                    $this->error('添加用户失败');
                }
            }else{
                $this->error('该用户名已存在，请换一个用户名！');exit;
            }
        }else{
            return view('addinfo',['meta_title'=>'新增用户']);
        }
    }
}
