<?php
namespace third;

class Curl{
    public function get($url, $params = array(), $httpheader = array()){
        return $this->curl($url, $params, $httpheader, 0);
    }

    function post($url, $params = array(), $httpheader = array()){
        return $this->curl($url, $params, $httpheader, 1);
    }

    function curl($url, $params = array(), $httpheader = array(), $type = 0){
        if($type == 0){
            if(strpos($url, '?')){
                $url .= "&".http_build_query($params);
            }else{
                $url .= "?".http_build_query($params);
            }
        }
        $ua = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:"";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if($type == 1){
            curl_setopt($ch, CURLOPT_POST, 1);//启用POST提交
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params)); //设置POST提交的字符串
        }else{
            curl_setopt($ch,CURLOPT_CUSTOMREQUEST, 'GET');
        }
        $arr_httpheader = array("Content-Type:application/x-www-form-urlencoded;charset=utf-8");
        foreach ($httpheader as $key=>$value){
            $arr_httpheader[] = $key.':'.$value;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $arr_httpheader);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);  //数据超时
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); //连接超时
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $ret = curl_exec($ch);#?得到的返回值 
        curl_close($ch);
        unset($ch);
        return $ret;
    }
}
?>