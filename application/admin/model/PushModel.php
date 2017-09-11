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

class PushModel extends Model
{
    public function addPush($data)
    {
        $suc = Db::table('t_push')->insert($data);
        return $suc;
    }

    public function getPushList()
    {
        return $list = Db::table('t_push')->field('id,title,addtime,status')->where('del = 0')->order('addtime desc')->select();
    }

    public function getDetails($id)
    {
        return $find = Db::table('t_push')->where("id=$id")->find();
    }

    public function doDetails($id,$data)
    {
        return $find = Db::table('t_push')->where("id=$id")->update($data);
    }

    public function doDel($id)
    {
        Db::table('t_push_user')->where("id=$id")->delete();
        return $id = Db::table('t_push')->where("id=$id")->delete();
    }

    public function doPush($id)
    {
        $data['status'] = 1;
        Db::table('t_push')->where("id=$id")->update($data);
        $user['push_id'] = $id;
        $user['pushtime'] = time();
        $id = Db::table('t_push_user')->insert($user);
        return $id;
    }

    public function doRead($id,$uid)
    {
        $where = "push_id = '$id'";
        $data['uid'] = $uid.',';
        $id = Db::table('t_push_user')->where($where)->update($data);
        return $id;
    }


    public function showMe()
    {
        return $id = Db::table('t_push_user')->select();
        // var_dump($id);exit;
    }
}