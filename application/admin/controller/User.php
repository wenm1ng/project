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


class User extends Controller
{
    
    public function __construct(){
        parent::__construct();
        define('UID',session::get('uid'));
        $this->username = session::get('username');
    }

    public function login(){
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
                    
                    $this->success('登录成功',url('admin/index/index'));
                }else{
                    $this->error('密码错误');
                }
            }else{
                $this->error('您没有权限！');
            }
        }else{
            $this->error('该用户不存在！');
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
            $this->success('退出登录成功!',url('admin/Index/login'));
        } else {
            // return json(self::erres("退出登录失败"));
            $this->error('退出登录失败!');
        }
    }

    //用户列表
    public function index(){
        if(UID){
            $table_name = "user";
            $list = Db::name($table_name)->select();
            //把分页数据赋值给模板变量
            $p = new \think\Page($list,10);
            // $list =$this->obj_to_array($list);
            $this->assign('list',$list);
            $this->assign('p',$p);

            // print_r($p);exit;
            return $this->fetch();
        }else{
            $this->redirect('admin');
        }
    }
}
