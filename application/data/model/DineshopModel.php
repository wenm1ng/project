<?php
/**
 * Dineshop店铺信息管理类
 */
namespace app\data\model;

use think\Model;
use think\Db;

class DineshopModel extends Model
{
    /**
     * 新增店铺
     * @return bool|int
     */
    public function addDineshop($shopname,$shophone,$address,$maplon,$maplat,$shopdesc='',$shopicon='',$cuisineid='',$menulist='',$sales='',$deliveryfee='',$minprice='',$preconsume='',$isbooking='',$opentime='',$isaway='',$deliverytime='',$minconsume='',$servicecharge='')
    {
        $table_name = 'dineshop';
        $data = array(
            'f_shopname' => $shopname,
            'f_shophone' => $shophone,
            'f_address' => $address,
            'f_maplon' => $maplon,
            'f_maplat' => $maplat,
            'f_addtime' => date("Y-m-d H:i:s"),
        );
        if($shopdesc) $data['f_shopdesc'] = $shopdesc;
        if($shopicon) $data['f_shopicon'] = $shopicon;
        if($cuisineid) $data['f_cuisineid'] = $cuisineid;
        if($menulist) $data['f_menulist'] = $menulist;
        if($sales) $data['f_sales'] = $sales;
        if($deliveryfee) $data['f_deliveryfee'] = $deliveryfee;
        if($minprice) $data['f_minprice'] = $minprice;
        if($minconsume) $data['f_minconsume'] = $minconsume;
        if($preconsume) $data['f_preconsume'] = $preconsume;
        if($servicecharge) $data['f_servicecharge'] = $servicecharge;
        if($isbooking) $data['f_isbooking'] = $isbooking;
        if($opentime) $data['f_opentime'] = $opentime;
        if($isaway) $data['f_isaway'] = $isaway;
        if($deliverytime) $data['f_deliverytime'] = $deliverytime;

        $shopid = intval(Db::name($table_name)->insertGetId($data));
        if ($shopid <= 0) {
            return false;
        }
        return $shopid;
    }

    /**
     * 检测店铺是否已经存在
     */
    public function checkDineshop($shopname)
    {
        $table_name = 'dineshop';
        $check = Db::name($table_name)
            ->where('f_shopname', $shopname)
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
    public function updateDineshop($shopid, $params)
    {
        $table_name = 'dineshop';
        $data = array();
        if($params['shopdesc']) $data['f_shopdesc'] = $params['shopdesc'];
        if($params['shopicon']) $data['f_shopicon'] = $params['shopicon'];
        if($params['shophone']) $data['f_shophone'] = $params['shophone'];
        if($params['address']) $data['f_address'] = $params['address'];
        if($params['cuisineid']) $data['f_cuisineid'] = $params['cuisineid'];
        if($params['menulist']) $data['f_menulist'] = $params['menulist'];
        if($params['maplon']) $data['f_maplon'] = $params['maplon'];
        if($params['maplat']) $data['f_maplat'] = $params['maplat'];
        if($params['sales']) $data['f_sales'] = $params['sales'];
        if($params['deliveryfee']) $data['f_deliveryfee'] = $params['deliveryfee'];
        if($params['minprice']) $data['f_minprice'] = $params['minprice'];
        if($params['minconsume']) $data['f_minconsume'] = $params['minconsume'];
        if($params['preconsume']) $data['f_preconsume'] = $params['preconsume'];
        if($params['servicecharge']) $data['f_servicecharge'] = $params['servicecharge'];
        if($params['isbooking']) $data['f_isbooking'] = $params['isbooking'];
        if($params['opentime']) $data['f_opentime'] = $params['opentime'];
        if($params['isaway']) $data['f_isaway'] = $params['isaway'];
        if($params['deliverytime']) $data['f_deliverytime'] = $params['deliverytime'];
        if(count($data) < 1) return true;
        $ret = Db::name($table_name)
            ->where('f_sid', $shopid)
            ->update($data);
        if($ret !== false){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取店铺信息
     */
    public function getShopInfo($shopid){
        $shopinfo = Db::table('t_dineshop')
            ->alias('a')
            ->field('a.f_sid sid,a.f_shopname shopname,a.f_shopicon shopicon,a.f_shophone shophone,a.f_address address,a.f_menulist menulist,a.f_sales sales,a.f_deliveryfee deliveryfee,a.f_minprice minprice,a.f_minconsume minconsume,a.f_preconsume preconsume,a.f_servicecharge servicecharge,a.f_isbooking isbooking,a.f_isaway isaway,a.f_opentime opentime,a.f_deliverytime deliverytime,b.f_cname cuisinename')
            ->join('food_cuisine b','a.f_cuisineid = b.f_cid','left')
            ->where('a.f_sid',$shopid)
            ->find();
        error_lo('sql',Db::getlastsql());
        return $shopinfo?$shopinfo:false;
    }
    
    /**
     * 删除店铺信息
     */
    public function delShop($shopid){
        $table_name = 'dineshop';
        $res = Db::name($table_name)
            ->where('f_sid', $shopid)
            ->delete();
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 获取店铺折扣时间段
     */
    public function getDiscountTimeslot(){
        $discountimeslot = Db::table('t_dineshop_discount_timeslot')
            ->field('f_id id, concat(f_starttime, \'-\', f_endtime) timeslot, f_addtime addtime')
            ->order('f_starttime asc')
            ->select();
        error_lo('sql',Db::getlastsql());
        return $discountimeslot;
    }

    /**
     * 获取店铺折扣信息
     */
    public function getDineshopDiscount($shopid, $slotid, $date){
        $subQuery = Db::table('t_dineshop_discount')->where('f_sid',$shopid)->where('f_timeslot',$slotid)->where('f_date',$date)->buildSql();
        $info = Db::table('t_dineshop')
            ->alias('a')
            ->field('a.f_menulist dishid, b.f_discount discount')
            ->join($subQuery.' b','a.f_sid = b.f_sid','left')
            ->where('a.f_sid',$shopid)
            ->find();
        error_lo('qq','1'.Db::getlastsql());
        return $info;
    }

    /**
     * 获取某店铺某日期某时间段桌型放号信息
     * @param $shopid
     * @param $date
     * @param $slotid
     * @return $this
     */
    public function getDeskSellIinfo($shopid, $date, $slotid){
        $table_name = "dineshop_sellinfo";
        $sellinfo = Db::name($table_name)
            ->where('f_sid',$shopid)
            ->where('f_date',$date)
            ->where('f_timeslot',$slotid)
            ->where('f_status',1)
            ->field('f_sellinfo as sellinfo')
            ->select();

        return $sellinfo;
    }

    /**
     * 获取桌型信息
     * @param $shopid
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getDeskInfo($shopid){
        $table_name = "dineshop_deskinfo";
        $deskinfo = Db::name($table_name)
            ->where('f_sid',$shopid)
            ->where('f_status',1)
            ->field('f_deskid as deskid')
            ->field('f_sid as shopid')
            ->field('f_seatnum as seatnum')
            ->field('f_amount as amount')
            ->field('f_orderamount as orderamount')
            ->select();

        return $deskinfo;
    }

    /**
     * 获取菜肴口味字典表
     */
    public function getFoodTastesInfo(){
        $table_name = "food_tastes";
        $result = Db::name($table_name)
            ->field('f_tid as tastid')
            ->field('f_tname as tastname')
            ->select();
        error_lo('qq','3'.Db::getlastsql());
        return $result;
    }
}