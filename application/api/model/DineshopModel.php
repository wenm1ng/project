<?php
/**
 * Dineshop店铺信息管理类
 */
namespace app\admin\model;

use think\Model;
use think\Db;

class DineshopModel extends Model
{
    /**
     * 添加店铺信息
     */
    public function addDineshop($shopname, $shopdesc, $shopicon, $cuisineid, $maplon, $maplat, $sales, $deliveryfee, $minprice, $preconsume, $isbooking, $isaway, $opentime, $shophone, $address, $adduser, $minconsume,$servicecharge){
        try{
            $data = array(
                'f_adduser' => $adduser,
                'f_shopname' => $shopname,
                'f_shopdesc' => $shopdesc,
                'f_shopicon' => $shopicon,
                'f_shophone' => $shophone,
                'f_address' => $address,
                'f_cuisineid' => $cuisineid,
                'f_maplon' => $maplon,
                'f_maplat' => $maplat,
                'f_sales' => $sales,
                'f_deliveryfee' => $deliveryfee,
                'f_minprice' => $minprice,
                'f_minconsume' => $minconsume,
                'f_preconsume' => $preconsume,
                'f_servicecharge' => $servicecharge,
                'f_isbooking' => $isbooking,
                'f_opentime' => $opentime,
                'f_isaway' => $isaway,
                'f_addtime' => date('Y-m-d H:i:s')
            );
            $shopid = intval(Db::table('t_admin_dineshop')->insertGetId($data));
            return $shopid;
        }catch (\Exception $e) {
            $shopinfo = Db::table('t_admin_dineshop')->field('f_sid shopid, f_fontshopid fontshopid')->where('f_adduser', $adduser)->find();
            if($shopinfo && isset($shopinfo['shopid'])){
                return $shopinfo['shopid'];
            }else{
                return false;
            }
        }
    }
    /**
     * 修改店铺信息
     */
    public function modDineshop($shopid, $shopname, $shopdesc, $shopicon, $cuisineid, $maplon, $maplat, $sales, $deliveryfee, $minprice, $preconsume, $isbooking, $isaway, $opentime, $shophone, $address, $minconsume,$servicecharge){
        // 启动事务
        Db::startTrans();
        try{
            $data = array(
                'f_shopname' => $shopname,
                'f_shopdesc' => $shopdesc,
                'f_shopicon' => $shopicon,
                'f_shophone' => $shophone,
                'f_address' => $address,
                'f_cuisineid' => $cuisineid,
                'f_maplon' => $maplon,
                'f_maplat' => $maplat,
                'f_sales' => $sales,
                'f_deliveryfee' => $deliveryfee,
                'f_minprice' => $minprice,
                'f_minconsume' => $minconsume,
                'f_preconsume' => $preconsume,
                'f_isbooking' => $isbooking,
                'f_servicecharge' => $servicecharge,
                'f_opentime' => $opentime,
                'f_isaway' => $isaway
            );
            Db::table('t_admin_dineshop')->where('f_sid', $shopid)->update($data); //更新管理后台表
            $shopinfo = Db::table('t_admin_dineshop')->field('f_status status, f_fontshopid fontshopid')->where('f_sid', $shopid)->find();
            if($shopinfo['status'] == 100){
                //审核通过则同步到前端表
                Db::table('t_dineshop')->where('f_sid', $shopinfo['fontshopid'])->update($data);
            }
            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
        
        return Db::table('t_admin_dineshop')->where('f_sid', $shopid)->update($data);
    }
    /**
     * 修改店铺信息
     */
    public function modDineshopStatus($shopid, $status){
        // 启动事务
        Db::startTrans();
        try{
            Db::table('t_admin_dineshop')->where('f_sid', $shopid)->update(array('f_status' => $status)); //更新管理后台表
            if($status == 100){
                //审核通过则同步到前端表
                $shopinfo = Db::table('t_admin_dineshop')->field('f_shopname,f_shopdesc,f_shopicon,f_shophone,f_address,f_cuisineid,f_maplon,f_maplat,f_sales,f_deliveryfee,f_minprice,f_minconsume,f_preconsume,f_isbooking,f_servicecharge,f_opentime,f_isaway')->where('f_sid', $shopid)->find();
                $shopinfo['f_addtime'] = date('Y-m-d H:i:s');
                $fontshopid = Db::table('t_dineshop')->insertGetId($shopinfo);
                Db::table('t_admin_dineshop')->where('f_sid', $shopid)->update(array('f_fontshopid' => $fontshopid)); 
            }else if($status == -300){
                //删除则从前端表下架
                $shopinfo = Db::table('t_admin_dineshop')->field('f_shopname shopname')->where('f_sid', $shopid)->find();
                Db::table('t_dineshop')->where('f_shopname', $shopinfo['shopname'])->delete();
                Db::table('t_admin_dineshop')->where('f_sid', $shopid)->update(array('f_fontshopid' => 0)); 
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
     * 获取推荐店铺信息列表
     */
    public function getRecomDineshopList($page = 1, $pagesize = 20){
        $allnum = Db::table('t_dineshop')->where('f_isrecom', 1)->count();
        $dineshoplist = Db::table('t_dineshop')
            ->field('f_sid id, f_isrecom isrecom, f_shopname shopname, f_shopdesc shopdesc, f_shopicon shopicon, f_shophone shophone, f_address address, f_isbooking isbooking, f_opentime opentime, f_isaway isaway, f_deliverytime deliverytime, f_addtime addtime')
            ->where('f_isrecom', 1)
            ->order('f_addtime desc')
            ->page($page, $pagesize)
            ->select();
        return array(
            "allnum" => $allnum,
            "dineshoplist" => $dineshoplist
        );
    }
    /**
     * 获取可推荐店铺信息列表
     */
    public function getCanRecomDineshopList($page = 1, $pagesize = 20){
        $allnum = Db::table('t_dineshop')->count();
        $dineshoplist = Db::table('t_dineshop')
            ->field('f_sid id, f_isrecom isrecom, f_shopname shopname, f_shopdesc shopdesc, f_shopicon shopicon, f_shophone shophone, f_address address, f_isbooking isbooking, f_opentime opentime, f_isaway isaway, f_deliverytime deliverytime, f_addtime addtime')
            ->order('f_addtime desc')
            ->page($page, $pagesize)
            ->select();
        return array(
            "allnum" => $allnum,
            "dineshoplist" => $dineshoplist
        );
    }
    /**
     * 添加推荐
     */
    public function addRecomDineshop($shopid){
        return Db::table('t_dineshop')->where('f_sid', $shopid)->update(array('f_isrecom' => 1));
    }
    
    /**
     * 添加推荐
     */
    public function delRecomDineshop($shopid){
        return Db::table('t_dineshop')->where('f_sid', $shopid)->update(array('f_isrecom' => 0));
    }
    /**
     * 获取店铺信息列表
     */
    public function getDineshopList($page = 1, $pagesize = 20){
        $allnum = Db::table('t_admin_dineshop')->count();
        $dineshoplist = Db::table('t_admin_dineshop')
            ->alias('a')
            ->field('a.f_sid id, a.f_adduser userid, b.f_username adduser, a.f_status status, a.f_shopname shopname, a.f_shopdesc shopdesc, a.f_shopicon shopicon, a.f_shophone shophone, a.f_address address, a.f_isbooking isbooking, a.f_opentime opentime, a.f_isaway isaway, a.f_deliverytime deliverytime, a.f_addtime addtime, a.f_fontshopid fontshopid')
            ->join('t_admin_userinfo b','a.f_adduser = b.f_uid','left')
            ->order('a.f_addtime desc')
            ->page($page, $pagesize)
            ->select();
        return array(
            "allnum" => $allnum,
            "dineshoplist" => $dineshoplist
        );
    }
    /**
     * 获取店铺信息
     */
    public function getDineshopInfo($shopid){
        $dineshopinfo = Db::table('t_admin_dineshop')
            ->alias('a')
            ->field('a.f_sid id, a.f_adduser userid, a.f_shopname shopname, a.f_status status, a.f_shopdesc shopdesc, a.f_shopicon shopicon, a.f_shophone shophone, a.f_address address, a.f_cuisineid cuisineid, b.f_cname cuisinename, a.f_menulist menulist, a.f_maplon maplon, a.f_maplat maplat, a.f_sales sales, a.f_deliveryfee deliveryfee, a.f_minprice minprice,a.f_minconsume minconsume, a.f_preconsume preconsume,a.f_servicecharge servicecharge, a.f_isbooking isbooking, a.f_opentime opentime, a.f_isaway isaway, a.f_deliverytime deliverytime, a.f_addtime addtime, a.f_fontshopid fontshopid')
            ->join('t_food_cuisine b','a.f_cuisineid = b.f_cid','left')
            ->where('a.f_sid', $shopid)
            ->find();
        return $dineshopinfo;
    }
    /**
     * 获取店铺信息
     */
    public function getDineshopInfoByadduser($adduser){
        $dineshopinfo = Db::table('t_admin_dineshop')->where('f_adduser', $adduser)->find();
        return $dineshopinfo;
    }
	/**
     * 获取店铺信息(按店铺名模糊搜索)
     */
    public function getDineshopInfoByName($shopname){
        $dineshopinfo = Db::table('t_admin_dineshop')
            ->alias('a')
            ->field('a.f_sid id, a.f_adduser userid, a.f_shopname shopname, a.f_status status, a.f_shopdesc shopdesc, a.f_shopicon shopicon, a.f_shophone shophone, a.f_address address, a.f_cuisineid cuisineid, b.f_cname cuisinename, a.f_menulist menulist, a.f_maplon maplon, a.f_maplat maplat, a.f_sales sales, a.f_deliveryfee deliveryfee, a.f_minprice minprice, a.f_minconsume minconsume, a.f_preconsume preconsume, a.f_servicecharge servicecharge, a.f_isbooking isbooking, a.f_opentime opentime, a.f_isaway isaway, a.f_deliverytime deliverytime, a.f_addtime addtime, a.f_fontshopid fontshopid')
            ->join('t_food_cuisine b','a.f_cuisineid = b.f_cid','left')
            ->where(array('a.f_shopname'=>array('like','%'.$shopname.'%')))
            ->find();
        return $dineshopinfo;
    }

    /**
     * 查询店铺折扣记录 根据时间段和日期
     */
    public function getDiscount($shopid, $date, $slotid){
        $discount = Db::table('t_dineshop_discount')
            ->field('f_id id, f_discount discount') 
            ->where('f_sid', $shopid)
            ->where('f_date', $date)
            ->where('f_timeslot', $slotid)
            ->find();
        return $discount;
    }
    /**
     * 新增店铺折扣信息
     */
    public function addDineshopDiscount($shopid, $date, $slotid, $discount){
        $data = array(
            'f_sid' => $shopid,
            'f_date' => $date,
            'f_timeslot' => $slotid,
            'f_discount' => $discount,
            'f_addtime' => date('Y-m-d H:i:s'),
        );
        $discountid = intval(Db::table('t_dineshop_discount')->insertGetId($data));
        return $discountid;
    }
    /**
     * 修改店铺折扣信息
     */
    public function modDineshopDiscount($id, $discount){
        $data = array("f_discount" => $discount);
        $ret = Db::table('t_dineshop_discount')->where('f_id', $id)->update($data);
        if($ret !== false){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 删除店铺折扣信息
     */
    public function delDineshopDiscount($id){
        $data = array("f_status" => 0);
        $ret = Db::table('t_dineshop_discount')->where('f_id', $id)->update($data);
        if($ret !== false){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 获取店铺折扣信息
     */
    public function getDineshopDiscount($shopid, $startdate, $endate){
        $discountlist = Db::table('t_dineshop_discount')
            ->alias('a')
            ->field('a.f_id id, a.f_sid shopid, a.f_date date, a.f_timeslot timeslotid, concat(b.f_starttime, \'-\', b.f_endtime) timeslot, a.f_discount discount, a.f_addtime addtime') 
            ->join('t_dineshop_discount_timeslot b','a.f_timeslot = b.f_id','left')
            ->where('a.f_sid', $shopid)
            ->where('a.f_status', 1)
            ->where('a.f_date',['>=',$startdate],['<',$endate])
            ->order('a.f_date asc')
            ->select();
            
        return $discountlist;
    }
    /**
     * 获取店铺放号信息
     */
    public function getDineshopSell($shopid, $startdate, $endate){
        $discountlist = Db::table('t_dineshop_sellinfo')
            ->alias('a')
            ->field('a.f_id id, a.f_sid shopid, a.f_date date, a.f_timeslot timeslotid, concat(b.f_starttime, \'-\', b.f_endtime) timeslot, a.f_sellinfo sellinfo, a.f_addtime addtime') 
            ->join('t_dineshop_discount_timeslot b','a.f_timeslot = b.f_id','left')
            ->where('a.f_sid', $shopid)
            ->where('a.f_status', 1)
            ->where('a.f_date',['>=',$startdate],['<',$endate])
            ->order('a.f_date asc')
            ->select();   
        return $discountlist;
    }
    
    /**
     * 查询店铺放号记录 根据时间段和日期
     */
    public function getSellinfo($shopid, $date, $slotid){
        $sellinfo = Db::table('t_dineshop_sellinfo')
            ->field('f_id id, f_sellinfo sellinfo') 
            ->where('f_sid', $shopid)
            ->where('f_date', $date)
            ->where('f_timeslot', $slotid)
            ->find();
        return $sellinfo;
    }
    
    /**
     * 新增店铺折扣信息
     */
    public function addDineshopSell($shopid, $date, $slotid, $sellinfo){
        $data = array(
            'f_sid' => $shopid,
            'f_date' => $date,
            'f_timeslot' => $slotid,
            'f_sellinfo' => $sellinfo,
            'f_addtime' => date('Y-m-d H:i:s'),
        );
        $sellid = intval(Db::table('t_dineshop_sellinfo')->insertGetId($data));
        return $sellid;
    }
    /**
     * 修改店铺折扣信息
     */
    public function modDineshopSell($id, $sellinfo){
        $data = array("f_sellinfo" => $sellinfo);
        $ret = Db::table('t_dineshop_sellinfo')->where('f_id', $id)->update($data);
        if($ret !== false){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 删除店铺折扣信息
     */
    public function delDineshopSell($id){
        $data = array("f_status" => 0);
        $ret = Db::table('t_dineshop_sellinfo')->where('f_id', $id)->update($data);
        if($ret !== false){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 添加店铺折扣时间段
     */
    public function addDiscountTimeslot($startime, $endtime){
        $timeslot = Db::table('t_dineshop_discount_timeslot')->field('f_id slotid')->where('f_starttime', $startime)->where('f_endtime', $endtime)->find();
        if($timeslot){
            return $timeslot['slotid'];
        }else{
            $data = array(
                'f_starttime' => $startime,
                'f_endtime' => $endtime,
                'f_addtime' => date('Y-m-d H:i:s')
            );
            $slotid = intval(Db::table('t_dineshop_discount_timeslot')->insertGetId($data));
            return $slotid?$slotid:false;
        }
    }
    /**
     * 删除店铺折扣时间段
     */
    public function delDiscountTimeslot($slotid){
        // 启动事务
        Db::startTrans();
        try{
            Db::table('t_dineshop_discount_timeslot')->whereIn('f_id', explode(',',$slotid))->delete(); //删除折扣时间段
            Db::table('t_dineshop_discount')->whereIn('f_timeslot', explode(',',$slotid))->delete(); //删除时间段内的所有折扣信息
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
     * 获取店铺折扣时间段
     */
    public function getDiscountTimeslot(){
        $discountimeslot = Db::table('t_dineshop_discount_timeslot')
            ->field('f_id id, concat(f_starttime, \'-\', f_endtime) timeslot, f_addtime addtime')
            ->order('f_starttime asc')
            ->select();
        return $discountimeslot;
    }
    /**
     * 获取店铺配送员信息
     */
    public function getDistripList($shopid){
        $distriplist = Db::table('t_dineshop_distripersion')
            ->field('f_id id, f_dineshopid shopid, f_id id, f_username distripname, f_mobile distripmobile')
            ->where('f_state', 0)
            ->where('f_dineshopid', $shopid)
            ->select();
        return $distriplist;
    }
    /**
     * 添加店铺桌型
     */
    public function addDesk($shopid, $deskid, $seatnum, $desknum){
        try{
            $data = array(
                'f_sid' => $shopid,
                'f_deskid' => $deskid,
                'f_seatnum' => $seatnum,
                'f_amount' => $desknum,
                'f_addtime' => date('Y-m-d H:i:s')
            );
            if(Db::table('t_dineshop_deskinfo')->insertGetId($data) > 0){
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
    /**
     * 修改店铺桌型
     */
    public function modDesk($shopid, $deskid, $desknum, $status)
    {
        $data = array(
            "f_amount" => $desknum,
            "f_status" => $status
        );
        $ret = Db::table('t_dineshop_deskinfo')
            ->where('f_sid', $shopid)
            ->where('f_deskid', $deskid)
            ->update($data);
        if ($ret !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取店铺桌型列表
     */
    public function getDesklist($shopid){
        $desklist = Db::table('t_dineshop_deskinfo')
            ->field('f_deskid deskid, f_sid shopid, f_seatnum seatnum, f_amount desknum, f_orderamount ordernum,f_status status, f_addtime addtime')
            ->where('f_sid', $shopid)
            ->order('f_deskid asc')
            ->select();
        return $desklist;
    }
    /**
     * 获取店铺桌型信息
     */
    public function getDeskinfo($shopid, $deskid)
    {
        $deskinfo = Db::table('t_dineshop_deskinfo')
            ->alias('a')
            ->field('a.f_id id, a.f_sid shopid, b.f_shopname shopname, b.f_shopdesc shopdesc, b.f_shopicon shopicon, b.f_shophone shophone, b.f_address address, a.f_seatnum seatnum, a.f_amount desknum, a.f_status status, a.f_addtime addtime')
            ->join('t_dineshop b', 'a.f_sid = b.f_sid', 'left')
            ->where('a.f_sid', $shopid)
            ->where('a.f_deskid', $deskid)
            ->find();
        return $deskinfo;
    }
    /**
     * 获取菜系列表
     */
    public function getCuisineList(){
        $list = Db::table('t_food_cuisine')
            ->field('f_cid id, f_cname cuisinename, f_lasttime addtime')
            ->where('f_status', 0)
            ->select();
        return $list;
    }

    /**
     * 获取用户的店铺信息
     */
    public function getUserDineshopInfo($userid){
        $table_name = 'admin_dineshop';
        $dineshopinfo = Db::name($table_name)
            ->alias('a')
            ->field('a.f_sid id, a.f_adduser userid, a.f_shopname shopname, a.f_status status, a.f_shopdesc shopdesc, a.f_shopicon shopicon, a.f_shophone shophone, a.f_address address, a.f_cuisineid cuisineid, b.f_cname cuisinename, a.f_menulist menulist, a.f_maplon maplon, a.f_maplat maplat, a.f_sales sales, a.f_deliveryfee deliveryfee, a.f_minprice minprice,a.f_minconsume minconsume, a.f_preconsume preconsume, a.f_servicecharge servicecharge, a.f_isbooking isbooking, a.f_opentime opentime, a.f_isaway isaway, a.f_deliverytime deliverytime, a.f_addtime addtime')
            ->join('t_food_cuisine b','a.f_cuisineid = b.f_cid','left')
            ->where('a.f_adduser', $userid)
            ->find();
        return $dineshopinfo;
    }
}