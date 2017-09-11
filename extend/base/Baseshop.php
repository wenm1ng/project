<?php
namespace base;

use base\Errcode;
use \app\data\model\UserModel;
use \app\dineshop\model\ShopUserModel;
use \think\Request;
use \think\Session;
use \think\Jump;
use think\Controller;
// use app\index\model\User;
class Baseshop extends Controller
{
    // 配置参数
    private $sid = -1;
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
        // define('sid',is_login());
        // if( !sid ){// 还没登录 跳转到登录页面
        //     $this->redirect('Admin/Index/login');
        // }
                // $this->redirect('Admin/Index/login');

        $this->sid = session::get('sid');
        $this->ck = session::get('ck');
        $this->model = new ShopUserModel();
        //检查用户是否登录
        $request = Request::instance();
        if(!in_array($request->action(),array('login','logout','logindineshop'))){
            if(!$this->checkAdminLogin($this->sid,$this->ck)){
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
     * @param string $sid
     * @param string $ck
     * @return bool
     */
    public function checkLogin($sid = '', $ck = ''){
        if(!$sid) $sid = input('sid');
        if(!$ck) $ck = input('ck');
        if(empty($sid) || empty($ck)){
            return false;
        }
        $UserModel = new UserModel();
        $userinfo = $UserModel->getLoginUserInfo($ck,$sid);
        if(empty($userinfo)){
            return false;
        }
        $UserModel->extendExpireTime($ck);
        return true;
    }
    
    /**
     * 检查管理员用户是否登录
     * @param string $sid
     * @param string $ck
     * @return bool
     */
    public function checkAdminLogin($sid = '', $ck = ''){
        if(!$sid) $sid = $this->sid;
        if(!$ck) $ck = $this->ck;

        if(empty($sid) || empty($ck)){
            // echo json_encode(0);exit;
            return false;
        }
        $AdminUserModel = new ShopUserModel();
        $userinfo = $AdminUserModel->getLoginShopInfo($ck,$sid);
        if(empty($userinfo)){
            // echo 1;
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

   
}

?>
