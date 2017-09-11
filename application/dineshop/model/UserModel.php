<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 17-4-25
 * Time: 下午9:28
 */
namespace app\admin\model;

use think\Exception;
use think\Log;
use think\Model;
use think\Db;

class UserModel extends Model
{
    /**
     * 获取前端全部用户
     */
    public function getUserList($page = 1, $pagesize = 20){
        // $allnum = Db::table('t_user_info')->count();
        $subQuery = Db::table('t_orders')->field('f_userid userid, count(f_oid) allnum, SUM(f_allmoney) allmoney')->where('f_status = 100 or f_status = 90')->group('userid')->select(false);
        $addressQuery = Db::table('t_user_address_info')->field('f_address address, f_uid userid')->where('f_status = 0 and f_isactive = 1')->group('f_uid')->select(false);
        $userlist = Db::table('t_user_info')
            ->alias('a')
            ->field('a.f_uid userid, a.f_nickname nickname, a.f_mobile mobile, a.f_realname realname, a.f_sex sex, a.f_idcard idcard, a.f_auth_status auth_status, a.f_usermoney usermoney, a.f_freezemoney freezemoney, a.f_depositmoney depositmoney, a.f_user_status user_status, a.f_regtime regtime, b.allnum allnum, b.allmoney allmoney, c.address address')
            ->join('('.$subQuery.') b','a.f_uid = b.userid','left')
            ->join('('.$addressQuery.') c','a.f_uid = c.userid','left')
            ->order('a.f_regtime desc')
            // ->page($page, $pagesize)
            ->select();
        // return array(
        //     "allnum" => $allnum,
        //     "userlist" => $userlist
        // );
            return $userlist;
    }
    
    /**
     * 获取注册用户信息
     */
    public function getUserInfo($userid){
        $userinfo = Db::table('t_user_info')
            ->alias('a')
            ->field('a.f_uid userid, a.f_nickname nickname, a.f_mobile mobile, a.f_realname realname, a.f_sex sex, a.f_idcard idcard, a.f_auth_status auth_status, a.f_usermoney usermoney, a.f_freezemoney freezemoney, a.f_depositmoney depositmoney, a.f_user_status user_status, a.f_regtime regtime')
            ->where('a.f_uid', $userid)
            ->find();
        return $userinfo;
    }
    
    /**
     * 更新注册用户信息
     */
    public function updateUserInfo($userid, $status){
        $userinfo = array('f_user_status' => $status);
        $userinfo = Db::table('t_user_info')->where('f_uid',$userid)->update($userinfo);
        return $userinfo;
    }

}