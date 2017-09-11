<?php
namespace app\api\controller;

use base\Baseapi;
use \app\api\model\TastesModel;
use \app\api\model\ClassifyModel;

class Classify extends Baseapi
{
    /**
     * 添加分类
	 * type=1表示第一分类,type=2表示第二分类,type=3表示口味
     */
    public function addClassify(){
        $info = array();
        $list = array();
        //获取第一类分类信息
        $cid = input('cid');
        $cname = input('cname');
		$type = input('type');
		$type = intval($type);
        if(empty($cname)){
            return json($this->erres('分类不能为空'));
        }
		if(!in_array($type,array(1,2,3))){
			return json($this->erres('类型参数错误'));
		}
		//判断登录
        if(!$this->checkAdminLogin()){
            //return json($this->errjson(-10001));
        }
		$res = false;
		if ($type == 1 || $type == 2){
			 $model = new ClassifyModel();
			if($cid){
				if ($type == 1)
					$res = $model->modCuisine($cid, $cname);
				else 
					$res = $model->modClassify($cid, $cname);
			}else{
				if ($type == 1)
					$res = $model->addCuisine($cname);
				else
					$res = $model->addClassify($cname);
			}
		}else if($type == 3){
			$model = new TastesModel();
			if($cid){
				$res = $model->modTastes($cid, $cname);
			}else{
				$res = $model->addTastes($cname);
			}
		}
        if($res === false){
            return json($this->errjson(-1));
        }else{
            return json($this->sucjson($info, $list));
        }
    }

	
	/**
     * 删除分类项
	 * type=1表示第一分类,type=2表示第二分类,type=3表示口味
     */
    public function delClassify(){
        $info = array();
        $list = array();
        $cid = input('cid');
		$type = input('type');
		$type = intval($type);
        if(empty($cid)){
            return json($this->erres('参数错误'));
        }
		if(empty($type) || !in_array($type,array(1,2,3))){
            return json($this->erres('类型参数错误1'));
        }
        //判断登录
        if(!$this->checkAdminLogin()){
            //return json($this->errjson(-10001));
        }
		
        if($type == 1 || $type == 2){
			$model = new ClassifyModel();
			if($type == 1){
				$res = $model->delCuisine($cid);
			}else{
				$res = $model->delClassify($cid);
			}
			if($res){
				return json($this->sucjson());
			}else{
				return json($this->errjson(-1)); 
			}
		}else if ($type == 3){
			$model = new TastesModel();
			$res = $model->delTastes($cid);
			if($res){
				return json($this->sucjson());
			}else{
				return json($this->errjson(-1)); 
			}
		}else{
			return json($this->erres('类型参数错误2'));
		}
    }
}
