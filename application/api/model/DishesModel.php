<?php
/**
 * Dineshop店铺信息管理类
 */
namespace app\admin\model;

use think\Model;
use think\Db;

class DishesModel extends Model
{
    /**
     * 添加菜肴信息
     */
    public function addDishes($dishname, $dishdesc, $dishicon, $price, $discount, $tastesid, $cuisineid, $classid, $shopid, $adduser, $salenum){
        try{
            $data = array(
                'f_adduser' => $adduser,
                'f_sid' => $shopid,
                'f_icon' => $dishicon,
                'f_name' => $dishname,
                'f_desc' => $dishdesc,
                'f_price' => $price,
                'f_discount' => $discount,
                'f_tastesid' => $tastesid,
                'f_cuisineid' => $cuisineid,
                'f_classid' => $classid,
                'f_salenum' => $salenum,
            );
            $dishid = intval(Db::table('t_admin_food_dishes')->insertGetId($data));
            return $dishid;
        }catch (\Exception $e) {
            $dishesinfo = Db::table('t_admin_food_dishes')->field('f_id dishid')->where('f_sid', $shopid)->where('f_name', $dishname)->find();
            if($dishesinfo && isset($dishesinfo['dishid'])){
                return $dishesinfo['dishid'];
            }else{
                return false;
            }
        }
    }
    /**
     * 修改菜肴信息
     */
    public function modDishes($dishid, $dishname, $dishdesc, $dishicon, $price, $discount, $tastesid, $cuisineid, $classid,$salenum){
        // 启动事务
        Db::startTrans();
        try{
            $data = array(
                'f_icon' => $dishicon,
                'f_name' => $dishname,
                'f_desc' => $dishdesc,
                'f_price' => $price,
                'f_discount' => $discount,
                'f_tastesid' => $tastesid,
                'f_cuisineid' => $cuisineid,
                'f_classid' => $classid,
                'f_salenum' => $salenum,
            );
            Db::table('t_admin_food_dishes')->where('f_id', $dishid)->update($data); //更新管理后台表
            $dishesinfo = Db::table('t_admin_food_dishes')->field('f_status status, f_fontdishid fontdishid')->where('f_id', $dishid)->find();
            if($dishesinfo['status'] == 100){
                //审核通过则同步到前端表
                Db::table('t_food_dishes')->where('f_id', $dishesinfo['fontdishid'])->update($data);
            }
            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
    }
    /**
     * 获取菜品信息
     */
    public function getDishesList($menulist){
        $disheslist = Db::table('t_food_dishes')
            ->alias('a')
            ->field('a.f_id id, a.f_icon icon, a.f_name dishesname, a.f_price price, b.f_cname classifyname, c.f_cname cuisinename, a.f_salenum salenum')
            ->join('t_food_classify b','a.f_classid = b.f_cid','left')
            ->join('t_food_cuisine c','a.f_cuisineid = c.f_cid','left')
            ->whereIn('a.f_id', explode(',',$menulist))
            ->select();
        return $disheslist?$disheslist:false;
    }

    /**
     * 根据店铺ID获取菜品列表信息
     */
    public function getDishesListBysidNoPage($shopid){
        $disheslist = Db::table('t_admin_food_dishes')
            ->alias('a')
            ->field('a.f_id id, a.f_icon icon, a.f_name dishesname, format(a.f_price,2) price, a.f_tastesid tastesid, b.f_cname classifyname, c.f_cname cuisinename,a.f_desc as food_desc,a.f_salenum as salenum')
            ->join('t_food_classify b','a.f_classid = b.f_cid','left')
            ->join('t_food_cuisine c','a.f_cuisineid = c.f_cid','left')
            ->where('a.f_sid', $shopid)
            ->select();
        return $disheslist;
    }

    /**
     * 根据店铺ID获取菜品列表信息
     */
    public function getDishesListBysid($shopid, $page, $pagesize){
        $allnum = Db::table('t_admin_food_dishes')->where('f_sid', $shopid)->count();
        $disheslist = Db::table('t_admin_food_dishes')
            ->alias('a')
            ->field('a.f_id id, a.f_adduser userid, b.f_username adduser, a.f_status status, a.f_sid shopid, e.f_shopname shopname, a.f_name dishname, a.f_desc dishdesc, a.f_icon dishicon, a.f_price dishprice, a.f_discount defdiscount, a.f_tastesid tastesid, a.f_cuisineid cuisineid, d.f_cname cuisinename, a.f_classid classid, c.f_cname classname, a.f_lasttime addtime, a.f_salenum salenum')
            ->join('t_admin_userinfo b','a.f_adduser = b.f_uid','left')
            ->join('t_food_classify c','a.f_classid = c.f_cid','left')
            ->join('t_food_cuisine d','a.f_cuisineid = d.f_cid','left')
            ->join('t_admin_dineshop e','a.f_sid = e.f_sid','left')
            ->where('a.f_sid', $shopid)
            ->order('a.f_lasttime desc')
            ->page($page, $pagesize)
            ->select();
        return array(
            "allnum" => $allnum,
            "disheslist" => $disheslist
        );
    }
    
    /**
     * 根据店铺ID获取可推荐菜品列表信息
     */
    public function getRecomDishesListBysid($shopid, $page, $pagesize){
        $allnum = Db::table('t_food_dishes')->where('f_sid', $shopid)->count();
        $disheslist = Db::table('t_food_dishes')
            ->alias('a')
            ->field('a.f_id id, a.f_state status, a.f_isrecom isrecom, a.f_sid shopid, e.f_shopname shopname, a.f_name dishname, a.f_desc dishdesc, a.f_icon dishicon, a.f_price dishprice, a.f_discount defdiscount, a.f_tastesid tastesid, a.f_cuisineid cuisineid, d.f_cname cuisinename, a.f_classid classid, c.f_cname classname, a.f_lasttime addtime, a.f_salenum salenum')
            ->join('t_food_classify c','a.f_classid = c.f_cid','left')
            ->join('t_food_cuisine d','a.f_cuisineid = d.f_cid','left')
            ->join('t_admin_dineshop e','a.f_sid = e.f_sid','left')
            ->where('a.f_sid', $shopid)
            ->order('a.f_lasttime desc')
            ->page($page, $pagesize)
            ->select();
        return array(
            "allnum" => $allnum,
            "disheslist" => $disheslist
        );
    }
    
    /**
     * 获取推荐菜品列表信息
     */
    public function getRecomDishesList($page, $pagesize){
        $allnum = Db::table('t_food_dishes')->where('f_isrecom', 1)->count();
        $disheslist = Db::table('t_food_dishes')
            ->alias('a')
            ->field('a.f_id id, a.f_state status, a.f_sid shopid, e.f_shopname shopname, a.f_name dishname, a.f_desc dishdesc, a.f_icon dishicon, a.f_price dishprice, a.f_discount defdiscount, a.f_tastesid tastesid, a.f_cuisineid cuisineid, d.f_cname cuisinename, a.f_classid classid, c.f_cname classname, a.f_lasttime addtime, a.f_salenum salenum')
            ->join('t_food_classify c','a.f_classid = c.f_cid','left')
            ->join('t_food_cuisine d','a.f_cuisineid = d.f_cid','left')
            ->join('t_admin_dineshop e','a.f_sid = e.f_sid','left')
            ->where('a.f_isrecom', 1)
            ->order('a.f_lasttime desc')
            ->page($page, $pagesize)
            ->select();
        return array(
            "allnum" => $allnum,
            "disheslist" => $disheslist
        );
    }
    
    /**
     * 添加推荐
     */
    public function addRecomDishes($dishid){
        return Db::table('t_food_dishes')->where('f_id', $dishid)->update(array('f_isrecom' => 1));
    }
    
    /**
     * 添加推荐
     */
    public function delRecomDishes($dishid){
        return Db::table('t_food_dishes')->where('f_id', $dishid)->update(array('f_isrecom' => 0));
    }
    
    /**
     * 获取菜品信息
     */
    public function getDishesInfo($dishid){
        $dishesinfo = Db::table('t_admin_food_dishes')
            ->alias('a')
            ->field('a.f_id id, a.f_adduser userid, b.f_username adduser, a.f_status status, a.f_sid shopid, e.f_shopname shopname, a.f_name dishname, a.f_desc dishdesc, a.f_icon dishicon, a.f_price dishprice, a.f_discount defdiscount, a.f_tastesid tastesid, a.f_cuisineid cuisineid, d.f_cname cuisinename, a.f_classid classid, c.f_cname classname, a.f_lasttime addtime, a.f_salenum salenum')
            ->join('t_admin_userinfo b','a.f_adduser = b.f_uid','left')
            ->join('t_food_classify c','a.f_classid = c.f_cid','left')
            ->join('t_food_cuisine d','a.f_cuisineid = d.f_cid','left')
            ->join('t_admin_dineshop e','a.f_sid = e.f_sid','left')
            ->where('a.f_id', $dishid)
            ->find();
        return $dishesinfo;
    }
    /**
     * 修改菜品信息
     */
    public function modDishestatus($dishid, $status){
        // 启动事务
        Db::startTrans();
        try{
            Db::table('t_admin_food_dishes')->where('f_id', $dishid)->update(array('f_status' => $status)); //更新管理后台表
            if($status == 100){
                //审核通过则同步到前端表
                $dishinfo = Db::table('t_admin_food_dishes')->field('f_sid,f_icon,f_name,f_desc,f_price,f_discount,f_state,f_tastesid,f_cuisineid,f_classid,f_salenum')->where('f_id', $dishid)->find();
                $fontdishid = Db::table('t_food_dishes')->insertGetId($dishinfo);
                Db::table('t_admin_food_dishes')->where('f_id', $dishid)->update(array('f_fontdishid' => $fontdishid)); 
            }else if($status == -300){
                //删除则从前端表下架
                $dishinfo = Db::table('t_admin_food_dishes')->field('f_sid shopid, f_name dishname')->where('f_id', $dishid)->find();
                Db::table('t_food_dishes')->where('f_sid', $dishinfo['shopid'])->where('f_name', $dishinfo['dishname'])->delete();
                Db::table('t_admin_food_dishes')->where('f_id', $dishid)->update(array('f_fontdishid' => 0)); 
            }
            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
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