<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 17-4-25
 * Time: 下午9:28
 */
namespace app\data\model;

use think\Model;
use think\Db;

class UserModel extends Model
{

    /**
     * 检查该手机号码是否已注册
     * @param $mobile
     * @return bool
     */
    public function checkMobile($mobile)
    {
        $table_name = 'user_info';
        $ret = Db::name($table_name)->field('f_uid as uid')->where('f_mobile', $mobile)->find();
        error_lo('tp',Db::getlastsql());
        if (empty($ret)) {
            return false;
        }
        return $ret['uid'];
    }

    /**
     * 新增用户
     * @param $mobile
     * @return bool|int
     */
    public function addUser($mobile)
    {
        $table_name = 'user_info';
        $data = array(
            'f_mobile' => $mobile,
            'f_regtime' => date("Y-m-d H:i:s"),
        );
        $userId = intval(Db::name($table_name)->insertGetId($data));
        if ($userId <= 0) {
            return false;
        }
        return $userId;
    }

    /**
     * 检查并记录短信发送日志
     * @param $uid
     * @param $mobile
     * @return bool
     */
    public function checkSmslog($uid, $mobile)
    {
        $table_name = 'user_smslog';
        $ret = Db::name($table_name)->where('f_uid', $uid)
            ->where('f_mobile', $mobile)
            ->field('f_lasttime as lasttime')
            ->field('now() as curtime')
            ->find();
        if (empty($ret)) {
            $data = array(
                'f_uid' => $uid,
                'f_mobile' => $mobile,
            );
            Db::name($table_name)->insert($data);
        } else {
            $lasttime = $ret['lasttime'];
            $curtime = $ret['curtime'];
            if(strtotime($curtime)-strtotime($lasttime) <= 60){
                return false;
            }
        }
        return true;
    }

    /**
     * 更新短信发送日志
     * @param $uid
     * @param $mobile
     * @return bool
     */
    public function updateSmslog($uid, $mobile)
    {
        $table_name = 'user_smslog';
        $ret = Db::name($table_name)
            ->where('f_uid', $uid)
            ->where('f_mobile', $mobile)
            ->setInc('f_count');
        if(intval($ret) > 0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 延长登录过期时间
     * @param $ck
     * @return bool
     */
    public function extendExpireTime($ck){
        $table_name = 'user_login';
        $data = array(
            'f_expiretime' => date("Y-m-d H:i:s", time()+30*24*3600),
        );
        $retup = Db::name($table_name)
            ->where('f_usercheck',$ck)
            ->where('f_lasttime','< time',time()-3600)
            ->update($data);
        if($retup !== false){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 设置CK过期
     * @param $ck
     * @return bool
     */
    public function setCkExpired($ck){
        $table_name = 'user_login';
        $data = array(
            'f_expiretime' => date("Y-m-d H:i:s", time()-60),
        );
        $retup = Db::name($table_name)
            ->where('f_usercheck',$ck)
            ->update($data);
        if($retup !== false){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 记录登录信息
     * @param $ck
     * @param $uid
     * @param $deviceid
     * @param $platform
     * @param $ip
     * @param $remark
     * @return array
     */
    public function addUserLogin($ck, $uid, $deviceid, $platform, $ip, $remark){
        $table_name = 'user_login';
        //判断用户是否重复登录
        $userinfo = Db::name($table_name)
            ->where('f_uid',$uid)
            ->where('f_expiretime', '> time', time())
            ->field('f_uid as uid')
            ->field('f_usercheck as ck')
            ->order('f_expiretime desc')
            ->find();
        if(!empty($userinfo)){
            return array(
                'ck' => $userinfo['ck'],
                'uid' => $uid,
            );
        }

        $expiretime = date("Y-m-d H:i:s", time()+30*24*3600);
        $data = array(
            'f_usercheck' => $ck,
            'f_uid' => $uid,
            'f_deviceid' => $deviceid,
            'f_platform' => $platform,
            'f_ip' => $ip,
            'f_remark' => $remark,
            'f_expiretime' => $expiretime,
        );
        Db::name($table_name)->insert($data);
        if(Db::name($table_name)->getLastInsID() <= 0){
            return false;
        }
        return array(
            'ck' => $ck,
            'uid' => $uid,
        );
    }

    /**
     * 通过ck获取用户登录信息
     * @param $ck
     * @param $uid
     * @return array|false|\PDOStatement|string|Model
     */
    public function getLoginUserInfo($ck,$uid){
        $table_name = 'user_login';
        $userinfo = Db::name($table_name)
            ->where('f_uid',$uid)
            ->where('f_usercheck',$ck)
            ->where('f_expiretime', '> time', time())
            ->field('f_uid as uid')
            ->field('f_usercheck as ck')
            ->find();
        return $userinfo;
    }

    /**
     * 新增地址
     */
    public function addAddress($userid, $province, $city, $address, $name, $mobile, $sex)
    {
        $table_name = 'user_address_info';
        $data = array(
            'f_uid' => $userid,
            'f_province' => $province,
            'f_city' => $city,
            'f_address' => $address,
            'f_name' => $name,
            'f_sex' => $sex,
            'f_mobile' => $mobile,
            'f_addtime' => date("Y-m-d H:i:s"),
        );
        $addressid = intval(Db::name($table_name)->insertGetId($data));
        if ($addressid <= 0) {
            return false;
        }
        return $addressid;
    }

    /**
     * 检测地址是否已经注册
     */
    public function checkAddress($userid, $province, $city, $address)
    {
        $table_name = 'user_address_info';
        $checkaddress = Db::name($table_name)
            ->field('f_id id')
            ->where('f_uid', $userid)
            ->where('f_province', $province)
            ->where('f_city', $city)
            ->where('f_address', $address)
            ->find();
        if(empty($checkaddress)){
            return false;
        }else{
            return $checkaddress["id"];
        }
    }

    /**
     * 更新地址
     */
    public function updateAddress($addressid, $params)
    {
        $table_name = 'user_address_info';
        $data = array();
        if($params['province']) $data['f_province'] = $params['province'];
        if($params['city']) $data['f_city'] = $params['city'];
        if($params['address']) $data['f_address'] = $params['address'];
        if($params['name']) $data['f_name'] = $params['name'];
        if($params['mobile']) $data['f_mobile'] = $params['mobile'];
        if($params['sex']) $data['f_sex'] = $params['sex'];
        if(count($data) < 1) return true;
        $ret = Db::name($table_name)
            ->where('f_id', $addressid)
            ->update($data);
        if($ret !== false){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取某一条地址信息
     */
    public function getAddressInfo($addressid){
        $table_name = 'user_address_info';
        $address = Db::name($table_name)
            ->where('f_id', $addressid)
            ->field('f_id id,f_province province,f_city city,f_address address,f_name name,f_sex male,f_mobile mobile,f_isactive isactive')
            ->order('f_addtime', 'desc')
            ->find();
        return $address?$address:false;
    }
    /**
     * 删除地址信息
     */
    public function delAddress($addressid){
        $table_name = 'user_address_info';
        $res = Db::name($table_name)
            ->where('f_id', $addressid)
            ->delete();
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 设置默认地址
     */
    public function setDefAddress($userid, $addressid){
        $table_name = 'user_address_info';
        $ret = Db::name($table_name)
            ->where('f_uid', $userid)
            ->update(array( 'f_isactive' => 0 ));
        if($ret !== false){
            $ret = Db::name($table_name)
                ->where('f_id', $addressid)
                ->update(array( 'f_isactive' => 1 ));
            if($ret !== false){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    /**
     * 获取用户配送地址信息
     */
    public function getAddressList($userid){
        $table_name = 'user_address_info';
        $address = Db::name($table_name)
            ->where('f_uid', $userid)
            ->field('f_id id,f_province province,f_city city,f_address address,f_name name,f_sex as male,f_mobile mobile,f_isactive isactive')
            ->order('f_isactive', 'desc')
            ->order('f_addtime', 'desc')
            ->select();
        return $address;
    }


    /**
     * 通过UID获取用户信息
     * @param $uid
     * @return array|false|\PDOStatement|string|Model
     */
    public function getUserInfoByUid($uid){
        $table_name = 'user_info';
        $userinfo = Db::name($table_name)
            ->where('f_uid',$uid)
            ->field('f_uid as uid')
            ->field('f_nickname as nickname')
            ->field('f_mobile as mobile')
            ->field('f_realname as realname')
            ->field('f_sex as sex')
            ->field('f_idcard as idcard')
            ->field('f_auth_status as auth_status')
            ->field('f_usermoney as usermoney')
            ->field('f_freezemoney as freezemoney')
            ->field('f_depositmoney as depositmoney')
            ->field('f_user_status as user_status')
            ->field('f_regtime as regtime')
            ->find();
        return $userinfo;
    }

    /**
     * 更新用户信息
     * @param $uid
     * @param $new_userinfo
     * @return bool
     */
    public function updateUserInfo($uid, $new_userinfo){
        $table_name = 'user_info';
        //获取更新前用户信息
        $ori_userinfo = self::getUserInfoByUid($uid);
        $userinfo = array(
            'f_nickname' => empty($new_userinfo['nickname']) ? $ori_userinfo['nickname'] : $new_userinfo['nickname'],
            'f_mobile' => empty($new_userinfo['mobile']) ? $ori_userinfo['mobile'] : $new_userinfo['mobile'],
            'f_realname' => empty($new_userinfo['realname']) ? $ori_userinfo['realname'] : $new_userinfo['realname'],
            'f_sex' => empty($new_userinfo['sex']) ? $ori_userinfo['sex'] : $new_userinfo['sex'],
            'f_idcard' => empty($new_userinfo['idcard']) ? $ori_userinfo['idcard'] : $new_userinfo['idcard'],
            'f_auth_status' => empty($new_userinfo['auth_status']) ? $ori_userinfo['auth_status'] : $new_userinfo['auth_status'],
            'f_user_status' => empty($new_userinfo['user_status']) ? $ori_userinfo['user_status'] : $new_userinfo['user_status'],
        );
        //实名认证成功,用户状态从0更新成100
        if(intval($userinfo['f_auth_status']) == 100 && intval($userinfo['f_user_status']) == 0){
            $userinfo['f_user_status'] = 100;
        }
        $retup = Db::name($table_name)
            ->where('f_uid',$uid)
            ->update($userinfo);
        if($retup !== false){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 检查用户状态
     * @param $uid
     * @param string $checktype
     * @return bool
     */
    public function checkUserStatus($uid, $checktype='trade'){
        $userinfo = self::getUserInfoByUid($uid);
        $userstatus = intval($userinfo['user_status']);
        //0-默认,100-已实名认证,200-已充值押金,-100-黑名单,-200-已清户(余额为0,押金退回)
        switch($checktype){
            case 'auth':
                //尚未实名认证方可进行实名认证
                $checkstatus = 0;
                break;
            case 'charge':
                //必须实名认证后方可充值押金
                $checkstatus = 100;
                break;
            case 'trade':
            case 'draw':
                //必须实名认证且充值押金后方可交易
                //必须用户状态正常方可退押金
                $checkstatus = 200;
                break;
            default:
                $checkstatus = -1;
        }
        if($userstatus !== $checkstatus){
            return false;
        }else{
            return true;
        }
    }
}