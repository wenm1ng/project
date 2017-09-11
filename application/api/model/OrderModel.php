<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 17-4-25
 * Time: 下午9:28
 */
namespace app\admin\model;

use think\Model;
use think\Db;

class OrderModel extends Model
{
    public $status_waiting_pay = 1;
    public $status_pay_suc = 2;
    public $status_in_delivery = 3;
    public $status_start_eat = 5;
    public $status_apply_packing = 6;
    public $status_waiting_checkup_refund = -110;
    public $status_checkup_suc_refund = -120;
    public $status_checkup_fail_refund = -130;
    public $status_waiting_refund = -200;
    public $status_refund_suc = -300;
    public $status_overtime_repast = -400;
    public $status_overtime_closed = -900;
    public $status_final_closed = -1000;

    /**
     * 获取外卖订单列表
     */
    public function getTakeoutlist($startime, $endtime, $shopid = '', $orderid = '', $page = 1, $pagesize = 20)
    {
        $where = array(
            'a.f_type' => 1
        );
		if (is_numeric($shopid)){
			$where['a.f_shopid'] = $shopid;
        }else if(!empty($shopid)){
            $where['b.f_shopname'] = array('like','%'.$shopid.'%');
        }
        if(!empty($orderid)) {
            $where['a.f_oid'] = $orderid;
        }else{
            $where['a.f_addtime'] = array('between time', [$startime.' 00:00:00', $endtime.' 23:59:59']);
        }
        $allnum = Db::table('t_orders')->alias('a')->join('t_dineshop b','a.f_shopid = b.f_sid','left')->where($where)->count();
        $orderlist = Db::table('t_orders')
            ->alias('a')
            ->field('a.f_oid orderid,a.f_shopid shopid,b.f_shopname shopname,a.f_userid userid,a.f_type ordertype,a.f_status status,a.f_orderdetail orderdetail,a.f_ordermoney ordermoney,a.f_deliverymoney deliverymoney,a.f_allmoney allmoney,a.f_paymoney paymoney,a.f_paytype paytype,d.f_name recipientname,d.f_mobile recipientmobile,c.f_username deliveryname,c.f_mobile deliverymobie,a.f_deliverytime deliverytime,CONCAT(d.f_province,d.f_city,d.f_address) deliveryaddress,a.f_addtime addtime')
            ->join('t_dineshop b','a.f_shopid = b.f_sid','left')
            ->join('t_dineshop_distripersion c','a.f_deliveryid = c.f_id','left')
            ->join('t_user_address_info d','a.f_addressid = d.f_id','left')
            ->where($where)
            ->order('a.f_addtime desc')
            ->page($page, $pagesize)
            ->select();
        return array(
            "allnum" => $allnum,
            "orderlist" => $orderlist
        );
    }
    /**
     * 获取食堂订单列表
     */
    public function getEatinlist($startime, $endtime, $shopid = '', $orderid = '', $page = 1, $pagesize = 20)
    {
        $where = array(
            'a.f_type' => 2,
            'a.f_addtime' => array('between time', [$startime, $endtime])
        );
        if (is_numeric($shopid)){
			$where['a.f_shopid'] = $shopid;
        }else if(!empty($shopid)){
            $where['b.f_shopname'] = array('like','%'.$shopid.'%');
        }
        if(!empty($orderid)) {
            $where['a.f_oid'] = $orderid;
        }
        $allnum = Db::table('t_orders')->alias('a')->join('t_dineshop b','a.f_shopid = b.f_sid','left')->where($where)->count();
        $orderlist = Db::table('t_orders')
            ->alias('a')
            ->field('a.f_oid orderid,a.f_shopid shopid,b.f_shopname shopname,a.f_userid userid,c.f_mobile usermobile,a.f_deskid deskid,d.f_seatnum seatnum,a.f_type ordertype,a.f_status status,a.f_orderdetail orderdetail,a.f_ordermoney ordermoney,a.f_deliverymoney deliverymoney,a.f_allmoney allmoney,a.f_paymoney paymoney,a.f_paytype paytype,a.f_mealsnum mealsnum,a.f_startime startime,a.f_endtime endtime,a.f_addtime addtime')
            ->join('t_dineshop b','a.f_shopid = b.f_sid','left')
            ->join('t_user_info c','a.f_userid = c.f_uid','left')
            ->join('t_dineshop_deskinfo d','a.f_deskid = d.f_id','left')
            ->where($where)
            ->order('a.f_addtime desc')
            ->page($page, $pagesize)
            ->select();
        return array(
            "allnum" => $allnum,
            "orderlist" => $orderlist
        );
    }  
    /**
     * 处理订单
     */
    public function processOrder($orderid, $data)
    {
        $res = array();
        $update = array();
        if(isset($data['status'])) $update['f_status'] = $data['status'];
        if(isset($data['distripid'])) $update['f_deliveryid'] = $data['distripid'];
        if(count($update) > 0){
            $res = Db::table('t_orders')->where('f_oid', $orderid)->update($update);
        }
        return $res;
    }
    /**
     * 获取订单详情
     */
    public function deliveryOrder($orderid, $distripid)
    {
        $res = Db::table('t_orders')->where('f_oid', $orderid)->update(array('f_status' => 3, 'f_deliveryid' => $distripid));
        return $res;
    }

    /**
     * 获取订单详情
     */
    public function getOrderinfo($userid, $orderid)
    {
        $where = array(
            'a.f_userid' => $userid,
            'a.f_oid' => $orderid
        );
        $orderinfo = Db::table('t_orders')
            ->alias('a')
            ->field('a.f_oid orderid,a.f_shopid shopid,b.f_shopname shopname,a.f_userid userid,a.f_type ordertype,a.f_status status,a.f_orderdetail orderdetail,a.f_ordermoney ordermoney,a.f_deliverymoney deliverymoney,a.f_allmoney allmoney,a.f_paymoney paymoney,a.f_paytype paytype,a.f_mealsnum mealsnum,a.f_servicemoney servicemoney,a.f_deskid deskid,a.f_startime startime,a.f_endtime endtime,c.f_name recipientname,c.f_mobile recipientmobile,d.f_username deliveryname,d.f_mobile deliveryphone,a.f_deliverytime deliverytime,CONCAT(c.f_province,c.f_city,c.f_address) deliveryaddress,a.f_addtime addtime')
            ->join('t_dineshop b','a.f_shopid = b.f_sid','left')
            ->join('t_user_address_info c', 'a.f_addressid = c.f_id','left')
            ->join('t_dineshop_distripersion d', 'a.f_deliveryid = d.f_id','left')
            ->where($where)
            ->find();
        return $orderinfo?$orderinfo:false;
    }

    /**
     * 更新交易订单信息
     * @param $uid
     * @param $orderid
     * @param $status
     * @param $paymoney
     * @return bool
     */
    public function updateTradeOrderInfo($uid, $orderid, $status, $paymoney=0){
        $sql = "update t_orders set f_status = :status, f_paymoney = f_paymoney + :paymoney where f_userid = :uid and f_oid = :orderid";
        $args = array(
            "uid" => $uid,
            "orderid" => $orderid,
            "status" => $status,
            "paymoney" => $paymoney
        );
        $ret = Db::execute($sql,$args);
        if($ret !== false){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取交易订单信息(订单支付退款用)
     * @param $uid
     * @param $orderid
     * @return array|false|\PDOStatement|string|Model
     */
    public function getTradeOrderInfo($uid, $orderid){
        $table_name = 'orders';
        $orderinfo = Db::name($table_name)
            ->where('f_userid',$uid)
            ->where('f_oid',$orderid)
            ->field('f_status as status')
            ->field('f_allmoney as allmoney')
            ->field('f_paymoney as paymoney')
            ->find();
        return $orderinfo;
    }

    /**
     * 堂食订单取消后更新预订桌型数量
     */
    public function cancelTradeOrderDeskOrdernum($deskid){
        $table_name = 'dineshop_deskinfo';
        Db::name($table_name)
            ->where('f_id',$deskid)
            ->setDec('f_orderamount');
    }

    /**
     * 获取待处理订单列表
     */
    public function getPendingOrderList($pending_list,$limit_num=100){
        $table_name = "orders";
        $order_list = Db::name($table_name)
            ->where('f_status','in',$pending_list)
            ->field('f_oid as orderid')
            ->field('f_userid as userid')
            ->field('f_type as ordertype')
            ->field('f_status as status')
            ->field('f_shopid as shopid')
            ->field('f_deskid as deskid')
            ->field('f_addtime as addtime')
            ->field('f_startime as startime')
            ->field('f_endtime as endtime')
            ->order('f_addtime desc')
            ->limit($limit_num)
            ->select();
        return $order_list;
    }

    /**
     * 释放桌型
     * @param $shopid
     * @param $deskid
     * @return bool
     */
    public function releaseDesk($shopid, $deskid){
        $table_deskinfo = 'dineshop_deskinfo';
        $retup = Db::name($table_deskinfo)
            ->where('f_sid',$shopid)
            ->where('f_deskid',$deskid)
            ->setInc('f_orderamount');
        if($retup !== false){
            return true;
        }
        return false;
    }

    /**
     * 获取待退款订单列表
     */
    public function getCancelOrderList($status,$limit_num=100){
        $table_name = "orders";
        $order_list = Db::name($table_name)
            ->where('f_status','in',$status)
            ->field('f_oid as orderid')
            ->field('f_userid as userid')
            ->field('f_type as ordertype')
            ->field('f_status as status')
            ->field('f_shopid as shopid')
            ->field('f_deskid as deskid')
            ->field('f_addtime as addtime')
            ->field('f_startime as startime')
            ->field('f_endtime as endtime')
            ->order('f_addtime desc')
            ->limit($limit_num)
            ->select();
        return $order_list;
    }
}