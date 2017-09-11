<?php
/**
 * Dineshop店铺信息管理类
 */
namespace app\admin\model;

use think\Model;
use think\Db;

class TastesModel extends Model
{
    /**
     * 获取口味信息
     */
    public function getTastesList($tasteslist){
        $field = 'f_tid id, f_tname tastes, f_lasttime lastime';
        if($tasteslist){
            $tasteslist = Db::table('t_food_tastes')->field($field)->whereIn('f_tid', explode(',',$tasteslist))->select();
        }else{
            $tasteslist = Db::table('t_food_tastes')->field($field)->where('f_status', 0)->select();
        }
        return $tasteslist?$tasteslist:array();
    }

	/**
     * 添加口味
     */
    public function addTastes($tname){
        try{
            $data = array(
                'f_tname' => $tname
            );
            $dishid = intval(Db::table('t_food_tastes')->insertGetId($data));
            return $dishid;
        }catch (\Exception $e) {
            return false;
        }
    }

	/**
     * 修改口味
     */
    public function modTastes($tid,$tname){
        try{
            $data = array(
                'f_tname' => $tname
            );
            $ret = Db::table('t_food_tastes')->where('f_tid', $tid)->update($data);
            return $ret;
        } catch (\Exception $e) {
            return false;
        }
    }

	/**
     * 删除口味
     */
    public function delTastes($tid){
        try{
            $data = array(
                'f_status' => -1
            );
            $ret = Db::table('t_food_tastes')->where('f_tid', $tid)->update($data);
            return $ret;
        } catch (\Exception $e) {
            return false;
        }
    }
}