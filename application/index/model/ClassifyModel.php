<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 17-4-25
 * Time: 下午9:28
 */
namespace app\index\model;
use think\Model;
use think\Db;
class ClassifyModel extends Model
{
    /**
     * 添加分类
     * @param $yiji 一级分类
     * @param $erji 二级分类
     * @return bool
     */
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
        if (empty($ret)) {
            return false;
        }
        return $ret;
    }
    
}