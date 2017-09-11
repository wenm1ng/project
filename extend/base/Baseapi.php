<?php
namespace base;

use base\Errcode;
use \app\data\model\UserModel;
use \app\api\model\AdminUserModel;

class Baseapi
{
    // 配置参数
    private $sign_key = 'x$sfxF%Qu4';
    public $res = ["code" => "-1", "msg" => "", "info" => array(), "list" => []];

    /**
     * 构造函数
     * @param array $res
     */
    public function __construct($res = array())
    {
	if(!empty($this->res)){
            $this->res['info'] = (object)$this->res['info'];
        }
        if ($res && count($res) > 0) {
            foreach ($res as $key => $val) {
                $this->res[$key] = $val;
            }
        }
        /*if(!self::checkToken()){
            die(json_encode($this->res));
        }*/
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
    public function sucres($info = array(), $list = array()){
        $this->res['code'] = 1;
        $this->res['msg'] = 'success';
        $this->res['info'] = (object)$info;
        $this->res['list'] = $list;
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
        if(!$uid) $uid = input('uid');
        if(!$ck) $ck = input('ck');

        if(empty($uid) || empty($ck)){
            return false;
        }
        $AdminUserModel = new AdminUserModel();
        $userinfo = $AdminUserModel->getLoginUserInfo($ck,$uid);
        if(empty($userinfo)){
            return false;
        }
        $AdminUserModel->extendExpireTime($ck);
        return true;
    }
}

?>
