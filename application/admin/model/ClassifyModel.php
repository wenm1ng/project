<?php
/**
 * Dineshop店铺信息管理类
 */
namespace app\admin\model;

use think\Model;
use think\Db;

class ClassifyModel extends Model
{

     public function addClassify($yiji,$erji){
        //存储插入失败的分类 和 重复的名称
        $err = $cf = '';
        //循环插入一级分类
        foreach($yiji as $k=>$v){
            //判断为空直接跳出
            if(empty($v)){continue;}
            if(false !== $this->checkClassifyName($v)){
                $cf .=$v.',';
                continue;
            }
            $data = array(
                'f_cname'=>$v,
                'f_grade'=>1,
            );
            $allnum = Db::table('t_food_cuisine')->insertGetId($data);
            if($allnum <= 0){
                $err .= $v.',';
            }
        }
        
        //循环插入二级分类
        
        foreach($erji as $k=>$v){
            //判断为空直接跳出
            if(empty($v)){continue;}
            if(false !== $this->checkClassifyName($v)){
                $cf .=$v.',';
                continue;
            }
            $data2 = array(
                'f_cname'=>$v,
                'f_grade'=>2,
            );
            $allnum2 = Db::table('t_food_cuisine')->insertGetId($data2);
            if($allnum2 <= 0){
                $err .= $v.',';
            }
        }
        
        return '插入重复:'.$cf.'<br>插入失败：'.$err;

    }

    /**
     * 检查分类名
     * @param $yiji 一级分类
     * @param $erji 二级分类
     * @return bool
     */
    public function checkClassifyName($name)
    {
        $table_name = 't_food_cuisine';
        $ret = Db::table($table_name)->field('f_cid as cid')->where('f_cname', $name)->find();
        //如果为空，则return false;否则返回查询到id
        if (empty($ret)) {
            return false;
        }
        return $ret;
    }

    /**
     * 显示所有分类
     * @param $yiji 一级分类
     * @param $erji 二级分类
     * @return bool
     */
    public function getClassify()
    {
        $yiji = Db::table('t_food_cuisine')->field('f_cid,f_cname')->where('f_grade=1')->select();
        $erji = Db::table('t_food_cuisine')->field('f_cid,f_cname')->where('f_grade=2')->select();
        $list = array(
            'yiji'=>$yiji,
            'erji'=>$erji,
            );
        return $list;
    }

    public function delClassify($cid)
    {
        $res = Db::table('t_food_cuisine')->where('f_cid',$cid)->delete();
        if($res){
            echo '1';
        }else{
            echo '2';
        }
    }


    public function updateClassify($cid,$cname)
    {
        //分类名已存在或未更改
        if($this->checkClassifyName($cname)){
            return 3;
        }
        $mod = array();
        $mod['f_cname'] = $cname;
        $res = Db::table('t_food_cuisine')->where('f_cid',$cid)->update($mod);
        // return  Db::table('t_food_cuisine')->getLastSql();
        if($res){
            //修改成功
            return 1;
        }else{
            //修改失败
            return 2;
        }

    }


 //    /**
 //     * 获取分类信息
 //     */
 //    public function getClassifyList(){
 //        $field = 'f_cid id, f_cname classname, f_lasttime lastime';
 //        $classifylist = Db::table('t_food_classify')->field($field)->where('f_status', 0)->select();
 //        return $classifylist?$classifylist:array();
 //    }

    // /**
 //     * 添加第二类分类(口味)
 //     */
 //    public function addClassify($cname){
 //        try{
 //            $data = array(
 //                'f_cname' => $cname
 //            );
 //            $dishid = intval(Db::table('t_food_classify')->insertGetId($data));
 //            return $dishid;
 //        }catch (\Exception $e) {
 //            return false;
 //        }
 //    }

    // /**
 //     * 修改第二类分类(口味)
 //     */
 //    public function modClassify($cid,$cname){
 //        try{
 //            $data = array(
 //                'f_cname' => $cname
 //            );
 //            $ret = Db::table('t_food_classify')->where('f_cid', $cid)->update($data);
 //            return $ret;
 //        } catch (\Exception $e) {
 //            return false;
 //        }
 //    }

    // /**
 //     * 删除第二类分类(口味)
 //     */
 //    public function delClassify($cid){
 //        try{
 //            $data = array(
 //                'f_status' => -1
 //            );
 //            $ret = Db::table('t_food_classify')->where('f_cid', $cid)->update($data);
 //            return $ret;
 //        } catch (\Exception $e) {
 //            return false;
 //        }
 //    }

    // /**
 //     * 添加第一类分类(菜系)
 //     */
 //    public function addCuisine($cname){
 //        try{
 //            $data = array(
 //                'f_cname' => $cname
 //            );
 //            $dishid = intval(Db::table('t_food_cuisine')->insertGetId($data));
 //            return $dishid;
 //        }catch (\Exception $e) {
 //            return false;
 //        }
 //    }

    // /**
 //     * 修改第一类分类(菜系)
 //     */
 //    public function modCuisine($cid,$cname){
 //        try{
 //            $data = array(
 //                'f_cname' => $cname
 //            );
 //            $ret = Db::table('t_food_cuisine')->where('f_cid', $cid)->update($data);
 //            return $ret;
 //        } catch (\Exception $e) {
 //            return false;
 //        }
 //    }

    // /**
 //     * 删除第一类分类(菜系)
 //     */
 //    public function delCuisine($cid){
 //        try{
 //            $data = array(
 //                'f_status' => -1
 //            );
 //            $ret = Db::table('t_food_cuisine')->where('f_cid', $cid)->update($data);
 //            return $ret;
 //        } catch (\Exception $e) {
 //            return false;
 //        }
 //    }
}