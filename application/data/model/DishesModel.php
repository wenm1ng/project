<?php
/**
 * Dineshop店铺信息管理类
 */
namespace app\data\model;

use think\Model;
use think\Db;

class DishesModel extends Model
{
    /**
     * 新增菜品
     * @return bool|int
     */
    public function addDishes()
    {
        $table_name = 'food_dishes';
        $data = array(
            'f_name' => $name,
            'f_icon' => $icon,
            'f_price' => $price,
            'f_tastesid' => $tastesid,
            'f_cuisineid' => $cuisineid,
            'f_classid' => $classid
        );
        $dishesid = intval(Db::name($table_name)->insertGetId($data));
        if ($dishesid <= 0) {
            return false;
        }
        return $dishesid;
    }

    /**
     * 检测菜品是否已经存在
     */
    public function checkDishes($dishesname)
    {
        $table_name = 'food_dishes';
        $check = Db::name($table_name)
            ->where('f_name', $dishesname)
            ->find();
        if(empty($check)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 更新店铺信息
     */
    public function updateDishes($dishesid, $params)
    {
        $table_name = 'food_dishes';
        $data = array();
        if($params['dishicon']) $data['f_icon'] = $params['dishicon'];
        if($params['dishname']) $data['f_name'] = $params['dishname'];
        if($params['dishprice']) $data['f_price'] = $params['dishprice'];
        if($params['dishstate']) $data['f_state'] = $params['dishstate'];
        if($params['tastesid']) $data['f_tastesid'] = $params['tastesid'];
        if($params['cuisineid']) $data['f_cuisineid'] = $params['cuisineid'];
        if($params['classid']) $data['f_classid'] = $params['classid'];
        if($params['salenum']) $data['f_salenum'] = $params['salenum'];
        if(count($data) < 1) return true;
        $ret = Db::name($table_name)
            ->where('f_id', $dishesid)
            ->update($data);
        if($ret !== false){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取菜品信息
     */
    public function getDishesList($menulist){
        $disheslist = Db::table('t_food_dishes')
            ->alias('a')
            ->field('a.f_id id, a.f_icon icon, a.f_name dishesname, format(a.f_price,2) price, a.f_discount discount, a.f_tastesid tastesid, b.f_cname classifyname, c.f_cname cuisinename,a.f_desc as food_desc,f_salenum as salenum')
            ->join('t_food_classify b','a.f_classid = b.f_cid','left')
            ->join('t_food_cuisine c','a.f_cuisineid = c.f_cid','left')
            ->whereIn('a.f_id', explode(',',$menulist))
            ->select();
        return $disheslist?$disheslist:false;
    }

    /**
     * 根据店铺ID获取菜品列表信息
     */
    public function getDishesListBysid($shopid){
        $disheslist = Db::table('t_food_dishes')
            ->alias('a')
            ->field('a.f_id id, a.f_icon icon, a.f_name dishesname, format(a.f_price,2) price, a.f_tastesid tastesid, b.f_cname classifyname, c.f_cname cuisinename,a.f_desc as food_desc,f_salenum as salenum,a.f_discount default_discount')
            ->join('t_food_classify b','a.f_classid = b.f_cid','left')
            ->join('t_food_cuisine c','a.f_cuisineid = c.f_cid','left')
            ->where('a.f_sid', $shopid)
            ->where('a.f_state', '>=', 0)
            ->select();
        error_lo('qq','2'.Db::getlastsql());
        return $disheslist;
    }
    
    /**
     * 删除菜品信息
     */
    public function delDishes($dishesid){
        $table_name = 'food_dishes';
        $res = Db::name($table_name)
            ->where('f_id', $dishesid)
            ->delete();
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }
}