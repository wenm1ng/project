<?php
/**
 * Dineshop店铺信息管理类
 */
namespace app\admin\model;

use think\Model;
use think\Db;

class ClassifyModel extends Model
{
    /**
     * 获取分类信息
     */
    public function getClassifyList(){
        $field = 'f_cid id, f_cname classname, f_lasttime lastime';
        $classifylist = Db::table('t_food_classify')->field($field)->where('f_status', 0)->select();
        return $classifylist?$classifylist:array();
    }

	/**
     * 添加第二类分类(口味)
     */
    public function addClassify($cname){
        try{
            $data = array(
                'f_cname' => $cname
            );
            $dishid = intval(Db::table('t_food_classify')->insertGetId($data));
            return $dishid;
        }catch (\Exception $e) {
            return false;
        }
    }

	/**
     * 修改第二类分类(口味)
     */
    public function modClassify($cid,$cname){
        try{
            $data = array(
                'f_cname' => $cname
            );
            $ret = Db::table('t_food_classify')->where('f_cid', $cid)->update($data);
            return $ret;
        } catch (\Exception $e) {
            return false;
        }
    }

	/**
     * 删除第二类分类(口味)
     */
    public function delClassify($cid){
        try{
            $data = array(
                'f_status' => -1
            );
            $ret = Db::table('t_food_classify')->where('f_cid', $cid)->update($data);
            return $ret;
        } catch (\Exception $e) {
            return false;
        }
    }

	/**
     * 添加第一类分类(菜系)
     */
    public function addCuisine($cname){
        try{
            $data = array(
                'f_cname' => $cname
            );
            $dishid = intval(Db::table('t_food_cuisine')->insertGetId($data));
            return $dishid;
        }catch (\Exception $e) {
            return false;
        }
    }

	/**
     * 修改第一类分类(菜系)
     */
    public function modCuisine($cid,$cname){
        try{
            $data = array(
                'f_cname' => $cname
            );
            $ret = Db::table('t_food_cuisine')->where('f_cid', $cid)->update($data);
            return $ret;
        } catch (\Exception $e) {
            return false;
        }
    }

	/**
     * 删除第一类分类(菜系)
     */
    public function delCuisine($cid){
        try{
            $data = array(
                'f_status' => -1
            );
            $ret = Db::table('t_food_cuisine')->where('f_cid', $cid)->update($data);
            return $ret;
        } catch (\Exception $e) {
            return false;
        }
    }
}