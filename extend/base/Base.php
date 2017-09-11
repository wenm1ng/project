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
class Base extends Controller
{
    // 配置参数
    private $uid = -1;
    private $ck = '';
    private $model;
    private $sign_key = 'x$sfxF%Qu4';
    public $res = ["code" => "-1", "msg" => "", "info" => array(), "list" => []];

    /**
     * 构造函数
     * @param array $res
     */
    public function __construct($res = array()){
            $request = Request::instance();
    	    if(!empty($this->res)){
                $this->res['info'] = (object)$this->res['info'];
            }
            if ($res && count($res) > 0) {
                foreach ($res as $key => $val) {
                    $this->res[$key] = $val;
                }
            }
            //控制器初始化
            if(method_exists($this,'_initialize')){
                $this->_initialize();
            }
            /*if(!self::checkToken()){
                die(json_encode($this->res));
            }*/
    }
    public function _initialize(){
        // 获取当前用户ID
        // define('UID',is_login());
        // if( !UID ){// 还没登录 跳转到登录页面
        //     $this->redirect('Admin/Index/login');
        // }
                // $this->redirect('Admin/Index/login');

        $this->uid = session::get('uid');
        $this->ck = session::get('ck');
        $this->model = new AdminUserModel();
        //检查用户是否登录
        $request = Request::instance();
        if(!in_array($request->action(),array('login','logout'))){
            if(!$this->checkAdminLogin($this->uid,$this->ck)){
                // echo 1;
                // die(json_encode($this->errjson(-10001)));
                $this->redirect('admin/index/login');
            }
        }
        // // 是否是超级管理员
        // define('IS_ROOT',   is_administrator());
        // if(!IS_ROOT){
        //     $this->
        // }
    }
    /**
     * 错误返回
     * @param array $res
     */
    public function errjson($code = -1, $info = array(), $list = array()){
        $Errcode = new Errcode();
        $this->res['code'] = $code;
        $this->res['msg'] = isset($Errcode->errcode[$code])?$Errcode->errcode[$code]:'';
        $this->res['info'] = (object)$info;
        $this->res['list'] = $list;
        // $this->res['msg'] = ;
        // $this->res['status'] = 0;
        return $this->res;
    }
    /**
     * 成功返回
     * @param array $res
     */
    public function sucjson($info = array(), $list = array()){
        $this->res['code'] = 1;
        $this->res['msg'] = 'success';
        $this->res['info'] = (object)$info;
        $this->res['list'] = $list;
        return $this->res;
    }
    /**
     * 错误返回
     * @param array $res
     */
    public function erres($msg, $code = -1){
        $this->res['code'] = $code;
        $this->res['msg'] = $msg;
        return $this->res;
    }
    /**
     * 成功返回
     * @param array $res
     */
    public function sucres($msg){
        $this->res['code'] = 1;
        $this->res['msg'] = $msg;
        return $this->res;
    }

    /**
     * 验证签名
     * @return bool
     */
    public function checkToken(){
        //验证签名
        $sign_str = '';
        $token_ori = input('token','');
        if(empty($token_ori)){
            $this->res['code'] = -1;
            $this->res['msg'] = 'Token签名串不能为空';
            return false;
        }
        $params = input();
        $pathinfo = '/'.request()->pathinfo();
        if(!empty($params)){
            ksort($params);
            foreach($params as $k=>$v){
                if($k == 'token' || $k == $pathinfo){
                    continue;
                }
                $sign_str .= $k."=".$v."&";
            }
            if(!empty($sign_str)){
                $sign_str = substr($sign_str,0,-1);
            }
        }
        
        $token = strtoupper(md5($sign_str.$this->sign_key));
        if(strtoupper($token_ori) != $token){
            $this->res['code'] = -1;
            $this->res['msg'] = 'Token签名错误';
            return false;
        }
        return true;
    }

    /**
     * 检查用户是否登录
     * @param string $uid
     * @param string $ck
     * @return bool
     */
    public function checkLogin($uid = '', $ck = ''){
        if(!$uid) $uid = input('uid');
        if(!$ck) $ck = input('ck');
        if(empty($uid) || empty($ck)){
            return false;
        }
        $UserModel = new UserModel();
        $userinfo = $UserModel->getLoginUserInfo($ck,$uid);
        if(empty($userinfo)){
            return false;
        }
        $UserModel->extendExpireTime($ck);
        return true;
    }
    
    /**
     * 检查管理员用户是否登录
     * @param string $uid
     * @param string $ck
     * @return bool
     */
    public function checkAdminLogin($uid = '', $ck = ''){
        if(!$uid) $uid = $this->uid;
        if(!$ck) $ck = $this->ck;

        if(empty($uid) || empty($ck)){
            // echo json_encode(0);exit;
            return false;
        }
        $AdminUserModel = new AdminUserModel();
        $userinfo = $AdminUserModel->getLoginUserInfo($ck,$uid);
        if(empty($userinfo)){
            // echo json_encode(1);exit;
            return false;
        }
        $AdminUserModel->extendExpireTime($ck);
        // echo json_encode(2);exit;
        return true;
    }

    /**
     * 视图实例对象
     * @var view
     * @access protected
     */    
    protected $view     =  null;

    /**
     * 控制器参数
     * @var config
     * @access protected
     */      
    protected $config   =   array();

   // 导出Excel表数据格式化
    public function excelDataFormat($data)
    {
        $new_arr = array();
        for ($i = 0; $i < count($data); $i++) {
            $each_arr = $data[$i];
            $new_arr[] = array_values($each_arr); //返回所有键值
        }
        //$new_key[] = array_keys($data[0]); //返回所有索引值
        return array(
            // 'excel_title' => $new_key[0], 
            'excel_ceils' => $new_arr);
    }
}

?>
