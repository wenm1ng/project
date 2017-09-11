<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 17-4-25
 * Time: 下午9:28
 */
namespace app\api\model;

use think\Exception;
use think\Log;
use think\Model;
use think\Db;

class AdminUserModel extends Model
{
    /**
     * 检查该用户名是否已注册
     * @param $username
     * @return bool
     */
    public function checkUserName($username)
    {
        $table_name = 'admin_userinfo';
        $ret = Db::name($table_name)->field('f_uid as uid')->where('f_username', $username)->find();
        if (empty($ret)) {
            return false;
        }
        return $ret;
    }

    /**
     * 新增用户
     * @param $username
     * @param $password
     * @param $realname
     * @return bool|int
     */
    public function addUser($username,$password,$realname)
    {
        $table_name = 'admin_userinfo';
        $data = array(
            'f_username' => $username,
            'f_password' => strtoupper(md5($password)),
            'f_realname' => $realname,
            'f_addtime' => date("Y-m-d H:i:s"),
        );
        $userId = intval(Db::name($table_name)->insertGetId($data));
        if ($userId <= 0) {
            return false;
        }
        return $userId;
    }

    /**
     * 延长登录过期时间
     * @param $ck
     * @return bool
     */
    public function extendExpireTime($ck){
        $table_name = 'admin_login';
        $data = array(
            'f_expiretime' => date("Y-m-d H:i:s", time()+1800),
        );
        $retup = Db::name($table_name)
            ->where('f_usercheck',$ck)
            ->where('f_lasttime','< time',time()-60)
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
        $table_name = 'admin_login';
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
     * @param $ip
     * @return array|bool
     */
    public function addUserLogin($ck, $uid, $ip){
        $table_name = 'admin_login';
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

        $expiretime = date("Y-m-d H:i:s", time()+1800);
        $data = array(
            'f_usercheck' => $ck,
            'f_uid' => $uid,
            'f_ip' => $ip,
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
        $table_name = 'admin_login';
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
     * 通过UID获取用户信息
     * @param $uid
     * @return array|false|\PDOStatement|string|Model
     */
    public function getUserInfoByUid($uid){
        $table_name = 'admin_userinfo';
        $userinfo = Db::name($table_name)
            ->where('f_uid',$uid)
            ->field('f_uid as uid')
            ->field('f_username as username')
            ->field('f_password as password')
            ->field('f_realname as realname')
            ->field('f_userstatus as userstatus')
            ->field('f_addtime as addtime')
            ->find();
        return $userinfo;
    }

    /**
     * 更新用户信息(含角色信息)
     * @param $uid
     * @param $new_userinfo
     * @return bool
     */
    public function updateUserInfo($uid, $new_userinfo){
        $table_user_info = 'admin_userinfo';
        $table_user_role = 'admin_user_role';
        Db::startTrans();
        try{
            //获取更新前用户信息
            $ori_userinfo = self::getUserInfoByUid($uid);
            $userinfo = array(
                'f_password' => empty($new_userinfo['password']) ? $ori_userinfo['password'] : strtoupper(md5($new_userinfo['password'])),
                'f_realname' => empty($new_userinfo['realname']) ? $ori_userinfo['realname'] : $new_userinfo['realname'],
                'f_userstatus' => empty($new_userinfo['userstatus']) ? $ori_userinfo['userstatus'] : $new_userinfo['userstatus'],
            );
             Db::name($table_user_info)
                ->where('f_uid',$uid)
                ->update($userinfo);
            //更新角色信息
            //查询修改前用户包含角色信息，与新角色信息比较，新增or删除
            $user_roleinfo = self::getUserRoleList($uid);
            $user_rolelist = array();
            if(!empty($user_roleinfo)){
                foreach($user_roleinfo as $role){
                    array_push($user_rolelist,$role['rid']);
                }
            }
            //新增
            $role_insert = array_diff($new_userinfo['rolelist'],$user_rolelist);
            if(!empty($role_insert)){
                $insert_data = array();
                foreach($role_insert as $rid){
                    array_push($insert_data,array('f_uid'=>$uid,'f_rid'=>$rid));
                }
                Db::name($table_user_role)->insertAll($insert_data);
            }
            //删除
            $role_del = array_diff($user_rolelist,$new_userinfo['rolelist']);
            if(!empty($role_del)){
                foreach($role_del as $rid){
                    Db::name($table_user_role)->where('f_uid',$uid)->where('f_rid',$rid)->delete();
                }
            }
            Db::commit();
            return true;
        }catch (Exception $e){
            Log::record($e);
            Db::rollback();
            return false;
        }
    }

    /**
     * 删除用户信息
     * 一并删除用户角色关联信息
     * 一并删除用户登录信息
     * @param $uidlist
     * @return bool
     */
    public function delUser($uidlist){
        $table_user_info = 'admin_userinfo';
        $table_user_role = 'admin_user_role';
        $table_user_login = 'admin_login';
        Db::startTrans();
        try{
            //删除用户信息
            Db::name($table_user_info)->delete($uidlist);
            //删除用户角色关联信息
            Db::name($table_user_role)->where('f_uid','in',$uidlist)->delete();
            //删除用户登录信息
            Db::name($table_user_login)->where('f_uid','in',$uidlist)->delete();
            Db::commit();
            return true;
        }catch (Exception $e){
            Log::record($e);
            Db::rollback();
            return false;
        }
    }

    /**
     * 检查用户状态(登录时检查)
     * @param $uid
     * @return bool
     */
    public function checkUserStatus($uid){
        $userinfo = self::getUserInfoByUid($uid);
        $userstatus = intval($userinfo['userstatus']);
        if($userstatus !== 100){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 检查角色名是否存在
     * @param $rolename
     * @return bool
     */
    public function checkRoleName($rolename)
    {
        $table_name = 'admin_role';
        $ret = Db::name($table_name)->field('f_rid as rid')->where('f_name', $rolename)->find();
        if (empty($ret)) {
            return false;
        }
        return true;
    }

    /**
     * 新增角色信息
     * @param $rolename
     * @param $describle
     * @return bool|int
     */
    public function addRole($rolename,$describle)
    {
        $table_name = 'admin_role';
        $data = array(
            'f_name' => $rolename,
            'f_describle' => $describle,
        );
        $roleId = intval(Db::name($table_name)->insertGetId($data));
        if ($roleId <= 0) {
            return false;
        }
        return $roleId;
    }

    /**
     * 新增模块信息
     * @param $modulename
     * @param $describle
     * @param $moduletype
     * @param $xpath
     * @param $parentid
     * @param $showorder
     * @return bool|int
     */
    public function addModule($modulename,$describle,$moduletype,$xpath,$parentid,$showorder)
    {
        $table_name = 'admin_module';
        $data = array(
            'f_name' => $modulename,
            'f_describle' => $describle,
            'f_moduletype' => $moduletype,
            'f_xpath' => $xpath,
            'f_parentid' => $parentid,
            'f_showorder' => $showorder,
        );
        $moduleId = intval(Db::name($table_name)->insertGetId($data));
        if ($moduleId <= 0) {
            return false;
        }
        return $moduleId;
    }

    /**
     * 删除模块信息
     * 一并删除模块角色关联信息
     * @param $midlist
     * @return bool
     */
    public function delModule($midlist){
        $table_module_info = 'admin_module';
        $table_role_module = 'admin_role_module';
        Db::startTrans();
        try{
            //删除模块信息
            Db::name($table_module_info)->delete($midlist);
            //删除模块角色关联信息
            Db::name($table_role_module)->where('f_mid','in',$midlist)->delete();
            Db::commit();
            return true;
        }catch (Exception $e){
            Log::record($e);
            Db::rollback();
            return false;
        }
    }

    /**
     * 获取模块基本信息
     * @param $mid
     * @return array|false|\PDOStatement|string|Model
     */
    public function getModuleInfo($mid){
        $table_name = 'admin_module';
        $moduleinfo = Db::name($table_name)
            ->where('f_mid',$mid)
            ->field('f_mid as mid')
            ->field('f_name as modulename')
            ->field('f_describle as describle')
            ->field('f_moduletype as moduletype')
            ->field('f_xpath as xpath')
            ->field('f_parentid as parentid')
            ->field('f_showorder as showorder')
            ->field('f_lasttime as lasttime')
            ->find();
        return $moduleinfo;
    }

    /**
     * 根据角色ID获取用户列表(默认查全部用户)
     */
    public function getUserList($rid){
        if($rid > 0){
            $sql = "select f_uid as uid,f_username as username,f_realname realname,f_userstatus as userstatus from t_admin_userinfo where f_uid in (select f_uid from t_admin_user_role where f_rid = :rid group by f_uid) order by username";
            $args = array(
                'rid' => $rid,
            );
            $userlist = Db::query($sql,$args);
        }else{
            $sql = "select f_uid as uid,f_username as username,f_realname realname,f_userstatus as userstatus from t_admin_userinfo order by username";
            $userlist = Db::query($sql);
        }
        return $userlist;
    }

    /**
     * 删除角色信息
     * 一并删除角色模块关联信息
     * @param $rid
     * @return bool
     */
    public function delRole($rid){
        $table_role_info = 'admin_role';
        $table_role_module = 'admin_role_module';
        Db::startTrans();
        try{
            //删除角色信息
            Db::name($table_role_info)->delete($rid);
            //删除角色模块关联信息
            Db::name($table_role_module)->where('f_rid','in',$rid)->delete();
            Db::commit();
            return true;
        }catch (Exception $e){
            Log::record($e);
            Db::rollback();
            return false;
        }
    }

    /**
     * 获取角色列表
     */
    public function getRoleList(){
        $table_name = 'admin_role';
        $rolelist = Db::name($table_name)
            ->field('f_rid as rid')
            ->field('f_name as rolename')
            ->field('f_describle as describle')
            ->field('f_lasttime as lasttime')
            ->order('rolename asc')
            ->select();
        return $rolelist;
    }

    /**
     * 获取单个用户角色列表
     */
    public function getUserRoleList($uid){
        $sql = "select a.f_rid as rid,a.f_name as rolename from t_admin_role a inner join t_admin_user_role b on a.f_rid = b.f_rid where b.f_uid = :uid";
        $args = array(
            'uid' => $uid,
        );
        return Db::query($sql,$args);
    }

    /**
     * 获取模块列表
     */
    public function getModuleList(){
        $table_name = 'admin_module';
        $modulelist = Db::name($table_name)
            ->field('f_mid as mid')
            ->field('f_name as modulename')
            ->field('f_describle as describle')
            ->field('f_moduletype as moduletype')
            ->field('f_xpath as xpath')
            ->field('f_parentid as parentid')
            ->field('f_showorder as showorder')
            ->field('f_lasttime as lasttime')
            ->order('modulename asc')
            ->select();
        return $modulelist;
    }

    /**
     * 根据父模块ID获取模块列表
     */
    public function getModuleByParentid($parentid,$uid=0){
        $table_name = 'admin_module';
        if($uid == 0){
            $modulelist = Db::name($table_name)
                ->where('f_parentid',$parentid)
                ->field('f_mid as mid')
                ->field('f_name as modulename')
                ->field('f_moduletype as moduletype')
                ->field('f_xpath as xpath')
                ->field('f_parentid as parentid')
                ->field('f_showorder as showorder')
                ->order('f_showorder,f_mid asc')
                ->select();
            return $modulelist;
        }else{
            $sql = "select a.f_mid as mid,a.f_name as modulename,a.f_moduletype as moduletype,a.f_xpath as xpath,a.f_parentid as parentid,a.f_showorder as showorder from t_admin_module a inner join t_admin_role_module b on a.f_mid = b.f_mid inner join t_admin_user_role c on b.f_rid = c.f_rid where c.f_uid = :uid and a.f_parentid = :parentid";
            $args = array(
                'uid' => $uid,
                'parentid' => $parentid,
            );
            return Db::query($sql,$args);
        }
    }

    /**
     * 更新模块信息
     */
    public function updateModuleInfo($mid, $new_moduleinfo){
        $table_name = 'admin_module';
        //获取更新前用户信息
        $ori_moduleinfo = self::getModuleInfo($mid);
        $moduleinfo = array(
            'f_name' => empty($new_moduleinfo['modulename']) ? $ori_moduleinfo['modulename'] : $new_moduleinfo['modulename'],
            'f_describle' => empty($new_moduleinfo['describle']) ? $ori_moduleinfo['describle'] : $new_moduleinfo['realname'],
            'f_moduletype' => empty($new_moduleinfo['moduletype']) ? $ori_moduleinfo['moduletype'] : $new_moduleinfo['moduletype'],
            'f_xpath' => empty($new_moduleinfo['xpath']) ? $ori_moduleinfo['xpath'] : $new_moduleinfo['xpath'],
            'f_parentid' => empty($new_moduleinfo['parentid']) ? $ori_moduleinfo['parentid'] : $new_moduleinfo['parentid'],
            'f_showorder' => empty($new_moduleinfo['showorder']) ? $ori_moduleinfo['showorder'] : $new_moduleinfo['showorder'],
        );
        $retup = Db::name($table_name)
            ->where('f_mid',$mid)
            ->update($moduleinfo);
        if($retup !== false){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取角色基本信息
     * @param $rid
     * @return array|false|\PDOStatement|string|Model
     */
    public function getRoleInfo($rid){
        $table_name = 'admin_role';
        $roleinfo = Db::name($table_name)
            ->where('f_rid',$rid)
            ->field('f_rid as rid')
            ->field('f_name as rolename')
            ->field('f_describle as describle')
            ->find();
        return $roleinfo;
    }

    /**
     * 根据角色ID获取模块列表
     */
    public function getModuleListByRid($rid){
        $sql = "select b.f_mid as mid from t_admin_role a inner join t_admin_role_module b on a.f_rid = b.f_rid where a.f_rid = :rid;";
        $args = array(
            'rid' => $rid,
        );
        return Db::query($sql,$args);
    }

    /**
     * 更新角色信息(含模块信息)
     * @param $rid
     * @param $new_roleinfo
     * @return bool
     */
    public function updatRoleInfo($rid, $new_roleinfo){
        $table_role_info = 'admin_role';
        $table_role_module = 'admin_role_module';
        Db::startTrans();
        try{
            //获取更新前角色信息
            $ori_roleinfo = self::getRoleInfo($rid);
            $roleinfo = array(
                'f_name' => empty($new_roleinfo['rolename']) ? $ori_roleinfo['rolename'] : $new_roleinfo['rolename'],
                'f_describle' => empty($new_roleinfo['describle']) ? $ori_roleinfo['describle'] : $new_roleinfo['describle'],
            );
            Db::name($table_role_info)
                ->where('f_rid',$rid)
                ->update($roleinfo);
            //更新模块信息
            //查询修改前角色包含模块信息，与新模块信息比较，新增or删除
            $role_moduleinfo = self::getModuleListByRid($rid);
            $role_modulelist = array();
            if(!empty($role_moduleinfo)){
                foreach($role_moduleinfo as $module){
                    array_push($role_modulelist,$module['mid']);
                }
            }
            //新增
            $module_insert = array_diff($new_roleinfo['modulelist'],$role_modulelist);
            if(!empty($module_insert)){
                $insert_data = array();
                foreach($module_insert as $mid){
                    array_push($insert_data,array('f_rid'=>$rid,'f_mid'=>$mid));
                }
                Db::name($table_role_module)->insertAll($insert_data);
            }
            //删除
            $module_del = array_diff($role_modulelist,$new_roleinfo['modulelist']);
            if(!empty($module_del)){
                foreach($module_del as $mid){
                    Db::name($table_role_module)->where('f_rid',$rid)->where('f_mid',$mid)->delete();
                }
            }
            Db::commit();
            return true;
        }catch (Exception $e){
            Log::record($e);
            Db::rollback();
            return false;
        }
    }
}