<?php
namespace app\admin\controller;

use base\Base;
use \app\admin\model\DishesModel;
use \app\admin\model\TastesModel;
use \app\admin\model\ClassifyModel;
use think\Session;
use think\Db;
class Dishes extends Base
{

    /**
    * 获取菜肴信息列表
    */
    public function index(){
        $DishesModel = new DishesModel();
        $list = $DishesModel->getDishesListBysid(input('shopid'));
        // var_dump(input('shopid'));
        // var_dump($list);
        $shopid = input('shopid');
        $shopinfo = Db::table('t_admin_dineshop')->where("f_sid = $shopid")->find();
        //获取菜品1级分类
        $list1 = Db::table('t_food_cuisine')->where('f_grade',1)->select();
        $list2 = Db::table('t_food_cuisine')->where('f_grade',2)->select();
        // var_dump($list1);
        $status = array(
            0 => '初始',
            1 => '审核中',
            100 => '审核通过',
            -100 => '审核不通过',
            -300 => '已下架'
        );
        return view('index',['list'=>$list['disheslist'],'shopid'=>input('shopid'),'list1'=>$list1,'list2'=>$list2,'shopinfo'=>$shopinfo,'status'=>$status]);
    }

    /**
    * 获取修改菜肴信息视图
    */
    public function editDish(){
        // print_r(input('shopid'));
        if(!input('dishid')){
            return json($this->erres("参数错误"));
        }
        $DishesModel = new DishesModel();
        $info = $DishesModel->getDishesInfo(input('dishid'));
        // print_r($info);
        //获取菜品1级分类
        $list1 = Db::table('t_food_cuisine')->where('f_grade',1)->select();
        $list2 = Db::table('t_food_cuisine')->where('f_grade',2)->select();
        return view('editdish',['info'=>$info,'shopid'=>input('shopid'),'dishid'=>input('dishid'),'list1'=>$list1,'list2'=>$list2]);
    }

    /**
    * 删除菜肴
    */
    public function delDish(){
        if(!input('dishid')){
            return json($this->erres("参数错误"));
        }
        $DishesModel = new DishesModel();
        $res = $DishesModel->delDishes(input('dishid'));
        echo Db::getlastsql();exit;
        if($res){
            return json($this->sucres("删除成功"));
        }else{
            return json($this->erres("删除失败"));
        }
    }
    // /*
    // * 显示菜肴
    // */
    // public function index()
    // {
       
    //     // echo '123';
    //     echo $_GET['sid'];
    //     return view('index',['sid'=>$_GET['sid']]);
        
    // }

    /*
    * 显示菜肴添加
    */
    public function showDishes()
    {
        
    }
    /**
     * 添加菜肴
     */
    public function addDishes(){
        if($_POST){
            $info = array();
            $list = array();
            // print_r($_SESSION);exit;
            //获取添加店铺信息
            $dishid = input('dishid');
            $adduser = input('adduser');
            if(empty($adduser)){
                return json($this->erres("系统错误"));
                // return json("您还没登录");
            }
            $dishname = input('dishname');
            if(empty($dishname)){
                return json($this->erres("菜肴名称不能为空"));
            }
            $dishdesc = input('dishdesc');
            $price = input('price');
            if(empty($price)){
                return json($this->erres('菜肴价格不能为空'));
            }
            $discount = round(input('discount'),1);
            if(empty($discount)){
                return json($this->erres('菜肴默认折扣不能为空'));
            }
            if($discount > 1){
                return json($this->erres('菜肴默认折扣不能大于1'));
            }
            $tastesid = input('tastesid');
            
            $classid = input('classid');
            if(empty($classid)){
                return json($this->erres('菜肴分类不能为空'));
            }
            $shopid = input('shopid');
            if(empty($shopid)){
                return json($this->erres('菜肴所属店铺不能为空'));
            }
            $dishicon = input('dishicon');
            if(empty($dishicon)){
                return json($this->erres('菜肴图片不能为空'));
            }
            if(strstr($dishicon,'upload/') && is_file(ROOT_PATH.$dishicon)){
                $dishiconurl = str_replace("upload","public/static/images", $dishicon);
                try{
                    copy(ROOT_PATH.$dishicon, ROOT_PATH.$dishiconurl); //拷贝到新目录
                }catch (\Exception $e) {
                    return json($this->errjson("文件传输错误"));
                }
            }else{
                $dishiconurl = $dishicon;
            }
            $cuisineid = input('cuisineid');
            $salenum = intval(input('salenum',0));
            if(empty($cuisineid)){
                return json($this->errjson(-80009));
            }
            //判断登录
            if(!$this->checkAdminLogin()){
                return json($this->errjson(-10001));
            }
            $DishesModel = new DishesModel();
            if($dishid){
                $res = $DishesModel->modDishes($dishid, $dishname, $dishdesc, $dishiconurl, $price, $discount, $tastesid, $cuisineid, $classid,$salenum);
            }else{
                $res = $DishesModel->addDishes($dishname, $dishdesc, $dishiconurl, $price, $discount, $tastesid, $cuisineid, $classid, $shopid, $adduser,$salenum);
            }
            if($res){
                if(!empty($tastesid)){
                    //将菜肴口味添加到口味表
                    $data['f_tname'] = $tastesid;
                    $data['f_lasttime'] = date('Y-m-d H:i:s');
                    Db::table('t_food_tastes')->insert($data);
                }
                
                return json($this->sucjson($info, $list));
            }else{
                return json($this->errjson(-1));
            }
        }else{
            //获取菜品1级分类
            $list1 = Db::table('t_food_cuisine')->where('f_grade',1)->select();
            $list2 = Db::table('t_food_cuisine')->where('f_grade',2)->select();
            return view('adddishes',['shopid'=>input('shopid'),'list1'=>$list1,'list2'=>$list2]);
        }
        
    }
    /**
     * 获取口味信息列表
     */
    public function getTastesList(){
        $info = array();
        $list = array();
        $tasteslist = input('tasteslist');
        $TastesModel = new TastesModel();
        $list = $TastesModel->getTastesList($tasteslist);
        return json($this->sucjson($info, $list));
    }
    /**
     * 获取分类信息列表
     */
    public function getClassifyList(){
        $info = array();
        $list = array();
        $tasteslist = input('tasteslist');
        $ClassifyModel = new ClassifyModel();
        $list = $ClassifyModel->getClassifyList();
        return json($this->sucjson($info, $list));
    }
    /**
     * 修改菜肴状态
     */
    public function modDishestatus(){
        //获取添加店铺信息
        $dishid = $_POST['id'];
        $key = $_POST['status'];
        if(empty($dishid) || empty($key)){
            return json($this->erres('参数错误'));
        }
        //判断登录
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $status = '';
        $status = $key;
        // if($key == '审核'){
        //     $status = 1;
        // }else if($key == '通过审核'){
        //     $status = 100;
        // }else if($key == '审核不通过'){
        //     $status = -100;
        // }else if($key == '删除'){
        //     $status = -300;
        // }
        $res = false;
        $DishesModel = new DishesModel();
        if($status != ''){
            $res = $DishesModel->modDishestatus($dishid, $status);
        }
        if($res){
            echo 1;
            // return json($this->sucjson());
        }else{
            echo 2;
            // return json($this->errjson(-1));
        }
    }
    
    /**
     * 获取菜肴信息
     */
    public function getDishesInfo(){
        $info = array();
        $list = array();
        $dishid = input('dishid');
        if(empty($dishid)){
            return json($this->erres('参数错误'));
        }
        //判断登录
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DishesModel = new DishesModel();
        $info = $DishesModel->getDishesInfo($dishid);
        return json($this->sucjson($info, $list));
    }
}
