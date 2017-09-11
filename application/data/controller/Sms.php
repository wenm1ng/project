<?php
namespace app\data\controller;
use \base\Baseapi;

class Sms extends Baseapi
{
    public function sendsms(){
        $mobile = input('mobile');
        $hasmatch = preg_match('/^1[0-9]{10}$/', $mobile, $matches);
        if(!$hasmatch){
            $this->res['code'] = -1;
            $this->res['msg'] = '手机号格式不对';
            return json($this->res);
        }
        $Sms = new \third\Sms();
        $res = $Sms -> sendsms($mobile);
        $this->res['code'] = $res['code'];
        $this->res['msg'] = $res['msg'];
        return json($this->res);
    }
    public function checksms(){
        $mobile = input('mobile');
        $code = input('code');
        $hasmatch = preg_match('/^1[0-9]{10}$/', $mobile, $matches);
        if(!$hasmatch){
            $this->res['code'] = -1;
            $this->res['msg'] = '手机号格式不对';
            return json($this->res);
        }elseif(strlen($code) != 4){
            $this->res['code'] = -1;
            $this->res['msg'] = '请输入4位验证码';
            return json($this->res);
        }
        $Sms = new \third\Sms();
        $res = $Sms->checksms($mobile, $code);
        $this->res['code'] = $res['code'];
        $this->res['msg'] = $res['msg'];
        return json($this->res);
    }
}
