<?php
namespace third;

use think\Config;
use \base\Baseapi;

class Sms extends Baseapi{
    /**
     * 发送短信验证码
     * @param number $mobile 手机号码
     * @return string
     */
    public function sendsms($mobile){
        $url = Config::get('sms_sendurl');
        $Curl = new \third\Curl();
        $res = $Curl->post($url, array("mobile"=>$mobile, "templateid"=>"3056293"), $this->getHttpHeader());
        if($res){
            $res = json_decode($res, true);
            if($res['code'] == 200){
                $this->res['code'] = 1;
                $this->res['msg'] = 'success';
            }else{
                $this->res['code'] = -1;
                if($res['code'] == 315) $this->res['msg'] = 'IP限制';
                else if($res['code'] == 403) $this->res['msg'] = '非法操作或没有权限';
                else if($res['code'] == 414) $this->res['msg'] = '参数错误';
                else if($res['code'] == 416) $this->res['msg'] = '请求太频繁';
                else if($res['code'] == 500) $this->res['msg'] = '服务器内部错误';
            }
        }
        return $this->res;
    }
    /**
     * 短信验证码验证
     * @param number $mobile 手机号码
     * @param number $code 验证码 4位
     * @return string
     */
    public function checksms($mobile, $code){
        $url = Config::get('sms_verifyurl');
        $Curl = new \third\Curl();
        $params = array("mobile" => $mobile, "code" => $code);
        $res = $Curl->post($url, $params, $this->getHttpHeader());
        if($res){
            $res = json_decode($res, true);
            if($res['code'] == 200){
                $this->res['code'] = 1;
                $this->res['msg'] = 'success';
            }else{
                $this->res['code'] = -1;
                if($res['code'] == 301) $this->res['msg'] = '被封禁';
                elseif($res['code'] == 315) $this->res['msg'] = 'IP限制';
                elseif($res['code'] == 403) $this->res['msg'] = '非法操作或没有权限';
                elseif($res['code'] == 404) $this->res['msg'] = '对象不存在';
                elseif($res['code'] == 413) $this->res['msg'] = '验证失败(短信服务)';
                elseif($res['code'] == 414) $this->res['msg'] = '参数错误';
                elseif($res['code'] == 500) $this->res['msg'] = '服务器内部错误';
            }
        }
        return $this->res;
    }
    /**
     * 短信验证码httpheader 参数
     */
    public function getHttpHeader(){
        $AppKey = Config::get('sms_appkey');
        $AppSecret = Config::get('sms_appsecret');
        $Nonce = $this->randomNonce();
        $CurTime = time();
        $CheckSum = SHA1($AppSecret.$Nonce.$CurTime);
        
        /*$AppKey = 'go9dnk49bkd9jd9vmel1kglw0803mgq3';
        $Nonce = '4tgggergigwow323t23t';
        $CurTime = '1443592222';
        $CheckSum = '9e9db3b6c9abb2e1962cf3e6f7316fcc55583f86';*/
        
        return array(
            "AppKey" => $AppKey, 
            "Nonce" => $Nonce, 
            "CurTime" => $CurTime, 
            "CheckSum" => $CheckSum
        );
    }
    
    /**
     * 随机字符
     * @param number $length 长度
     * @return string
     */
    function randomNonce($length = 12){
        $string = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $code = '';
        $strlen = strlen($string) -1;
        for($i = 0; $i < $length; $i++){
            $code .= $string{mt_rand(0, $strlen)};
        }
        return $code;
    }
}
?>