<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 17-4-25
 * Time: 下午9:28
 */
namespace app\data\model;

use think\Log;
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
     * 新增外卖订单
     * @param $userid 用户ID
     * @param $shopid 店铺ID
     * @param $orderdetail 订单详情
     * @param $ordermoney 订单金额
     * @param $deliverymoney 配送费
     * @param $allmoney 订单总金额
     * @param $paytype 支付方式
     * @param $deliverytime 配送时间
     * @param $addressid 配送地址ID
     * @return bool|int
     */
    public function addTakeoutOrders($userid, $shopid, $orderdetail, $ordermoney, $deliverymoney, $allmoney, $paytype, $deliverytime, $addressid)
    {
        $table_name = 'orders';
        $data = array(
            'f_userid' => $userid,
            'f_shopid' => $shopid,
            'f_type' => 1,
            'f_orderdetail' => $orderdetail,
            'f_ordermoney' => $ordermoney,
            'f_deliverymoney' => $deliverymoney,
            'f_allmoney' => $allmoney,
            'f_paytype' => $paytype,
            'f_deliverytime' => $deliverytime,
            'f_addressid' => $addressid,
            'f_addtime' => date("Y-m-d H:i:s"),
        );
        $orderid = intval(Db::name($table_name)->insertGetId($data));
        if ($orderid <= 0) {
            return false;
        }
        return $orderid;
    }

    /**
     * 新增食堂订单
     * @param $userid 用户ID
     * @param $shopid 店铺ID
     * @param $orderdetail 订单详情
     * @param $ordermoney 订单金额
     * @param $deliverymoney 配送费
     * @param $allmoney 订单总金额
     * @param $paytype 支付方式
     * @param $mealsnum 就餐人数
     * @param $startime 就餐开始时间
     * @param $endtime 就餐结束时间
     * @param $servicemoney
     * @param $deskid
     * @return bool|int
     */
    public function addEatinOrders($userid, $shopid, $orderdetail, $ordermoney, $deliverymoney, $allmoney, $paytype, $mealsnum, $startime, $endtime, $servicemoney, $deskid)
    {
        $table_orders = 'orders';
        $table_deskinfo = 'dineshop_deskinfo';
        $data = array(
            'f_userid' => $userid,
            'f_shopid' => $shopid,
            'f_type' => 2,
            'f_orderdetail' => $orderdetail,
            'f_ordermoney' => $ordermoney,
            'f_deliverymoney' => $deliverymoney,
            'f_allmoney' => $allmoney,
            'f_paytype' => $paytype,
            'f_mealsnum' => $mealsnum,
            'f_startime' => $startime,
            'f_endtime' => $endtime,
            'f_addtime' => date("Y-m-d H:i:s"),
            'f_servicemoney' => $servicemoney,
            'f_deskid' => $deskid,
        );
        Db::startTrans();
        try{
            $orderid = intval(Db::name($table_orders)->insertGetId($data));
            if ($orderid > 0) {
                Db::name($table_deskinfo)
                    ->where('f_sid',$shopid)
                    ->where('f_deskid',$deskid)
                    ->setInc('f_orderamount');
                Log::record('wayde-orderid='.$orderid,'error');
                Log::record('wayde-f_sid='.$shopid,'error');
                Log::record('wayde-f_deskid='.$deskid,'error');
                Db::commit();
                return $orderid;
            }
            Db::rollback();
            return false;
        }catch (Exception $e){
            Db::rollback();
            return false;
        }
    }
    
    /**
     * 完成订单
     */
    public function finishOrder($userid, $orderid, $paymoney)
    {
        $Account = new AccountModel();
        //冻结
        $tradetype = 2002;  //订单支付冻结
        $tradenote = $Account->tradetype_config[$tradetype];
        $freeze = $Account->freeze($userid,$paymoney,$tradetype,$tradenote);
        if($freeze){
            //更新订单状态
            $uporder = self::updateTradeOrderInfo($userid,$orderid,$this->status_pay_suc,$paymoney);
            if($uporder){
                //解冻扣款
                $tradetype = 2102;  //订单支付(解冻扣款)
                $tradenote = $Account->tradetype_config[$tradetype];
                if($Account->unfreeze($userid,$paymoney,$tradetype,$tradenote)){
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * 检测订单是否已经存在
     */
    public function checkOrder($userid, $shopid, $orderdetail, $ordertype)
    {
        $table_name = 'orders';
        $check = Db::name($table_name)
            ->field('f_oid orderid')
            ->where('f_shopid', $shopid)
            ->where('f_userid', $userid)
            ->where('f_orderdetail', $orderdetail)
            ->where('f_type', $ordertype)
            ->where('f_addtime', '>', (date("Y-m-d H:i:s",time()-10)))
            ->find();
        return $check?$check['orderid']:false;
    }

    /**
     * 获取订单列表
     */
    public function getOrderlist($userid, $ordertype = 1, $page = 1, $pagesize = 20)
    {
        $where = array(
            'a.f_userid' => $userid,
            'a.f_type' => $ordertype
        );
        $allnum = Db::table('t_orders')->alias('a')->join('t_dineshop b','a.f_shopid = b.f_sid','left')->where($where)->count();
        $orderlist = Db::table('t_orders')
            ->alias('a')
            ->field('a.f_oid orderid,a.f_shopid shopid,a.f_userid userid,a.f_type ordertype,a.f_status status,a.f_orderdetail orderdetail,a.f_ordermoney ordermoney,a.f_deliverymoney deliverymoney,a.f_allmoney allmoney,a.f_paymoney paymoney,a.f_paytype paytype,a.f_mealsnum mealsnum,a.f_startime startime,a.f_endtime endtime,a.f_deliveryid deliveryid,a.f_deliverytime deliverytime,a.f_addressid addressid,a.f_addtime addtime,b.f_shopname shopname')
            ->join('t_dineshop b','a.f_shopid = b.f_sid','left')
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
            ->field('a.f_oid orderid,a.f_shopid shopid,b.f_shopname shopname,a.f_userid userid,a.f_type ordertype,a.f_status status,a.f_orderdetail orderdetail,a.f_ordermoney ordermoney,a.f_deliverymoney deliverymoney,a.f_allmoney allmoney,a.f_paymoney paymoney,a.f_paytype paytype,a.f_mealsnum mealsnum,a.f_servicemoney servicemoney,a.f_deskid deskid,a.f_startime startime,a.f_endtime endtime,c.f_name recipientname,c.f_mobile recipientmobile,d.f_username deliveryname,d.f_mobile deliveryphone,a.f_deliverytime deliverytime,CONCAT(c.f_province,c.f_city,c.f_address) deliveryaddress,a.f_addtime addtime,a.f_hassuborder hassuborder')
            ->join('t_dineshop b','a.f_shopid = b.f_sid','left')
            ->join('t_user_address_info c', 'a.f_addressid = c.f_id','left')
            ->join('t_dineshop_distripersion d', 'a.f_deliveryid = d.f_id','left')
            ->where($where)
            ->find();
        return $orderinfo?$orderinfo:false;
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
     * 更新订单信息
     * @param $uid
     * @param $orderid
     * @param $new_orderinfo
     * @return bool
     */
    public function updateOrderInfo($uid, $orderid, $new_orderinfo){
        $table_name = 'orders';
        //获取更新前订单信息
        $ori_orderinfo = self::getOrderinfo($uid,$orderid);
        $orderinfo = array(
            'f_paytype' => isset($new_orderinfo['paytype']) ? $new_orderinfo['paytype'] : $ori_orderinfo['paytype'],
        );
        $retup = Db::name($table_name)
            ->where('f_uid',$uid)
            ->where('f_oid',$orderid)
            ->update($orderinfo);
        if($retup !== false){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 新增堂食子订单
     */
    public function addEatinSubOrders($userid,$parentid,$orderdetail,$ordermoney,$paytype)
    {
        $table_order = 'orders';
        $table_suborder = 'sub_orders';
        $data = array(
            'f_userid' => $userid,
            'f_parentid' => $parentid,
            'f_orderdetail' => $orderdetail,
            'f_ordermoney' => $ordermoney,
            'f_paytype' => $paytype,
            'f_addtime' => date("Y-m-d H:i:s"),
        );
        Db::startTrans();
        try{
            $sub_orderid = Db::name($table_suborder)->insertGetId($data);
            $p_orderinfo = array(
                'f_hassuborder' => 1,
            );
            Db::name($table_order)
                ->where('f_userid',$userid)
                ->where('f_oid',$parentid)
                ->update($p_orderinfo);
            Db::commit();
            return $sub_orderid;
        }catch (Exception $e){
            Db::rollback();
            return false;
        }
    }

    /**
     * 更新子交易订单信息
     * @param $uid
     * @param $orderid
     * @param $status
     * @param $paymoney
     * @return bool
     */
    public function updateTradeSubOrderInfo($uid, $orderid, $status, $paymoney=0){
        $sql = "update t_sub_orders set f_status = :status, f_paymoney = f_paymoney + :paymoney where f_userid = :uid and f_oid = :orderid";
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
     * 更新订单信息
     * @param $uid
     * @param $orderid
     * @param $new_orderinfo
     * @return bool
     */
    public function updateSubOrderInfo($uid, $orderid, $new_orderinfo){
        $table_name = 'sub_orders';
        //获取更新前订单信息
        $ori_orderinfo = self::getSubOrderinfo($uid,$orderid);
        $orderinfo = array(
            'f_paytype' => isset($new_orderinfo['paytype']) ? $new_orderinfo['paytype'] : $ori_orderinfo['paytype'],
        );
        $retup = Db::name($table_name)
            ->where('f_userid',$uid)
            ->where('f_oid',$orderid)
            ->update($orderinfo);
        if($retup !== false){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 完成子订单
     */
    public function finishSubOrder($userid, $orderid, $paymoney)
    {
        $Account = new AccountModel();
        //冻结
        $tradetype = 2002;  //订单支付冻结
        $tradenote = $Account->tradetype_config[$tradetype];
        $freeze = $Account->freeze($userid,$paymoney,$tradetype,$tradenote);
        if($freeze){
            //更新订单状态
            $uporder = self::updateTradeSubOrderInfo($userid,$orderid,$this->status_pay_suc,$paymoney);
            if($uporder){
                //解冻扣款
                $tradetype = 2102;  //订单支付(解冻扣款)
                $tradenote = $Account->tradetype_config[$tradetype];
                if($Account->unfreeze($userid,$paymoney,$tradetype,$tradenote)){
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 获取子订单订单详情
     */
    public function getSubOrderinfo($userid, $orderid)
    {
        $where = array(
            'a.f_userid' => $userid,
            'a.f_oid' => $orderid
        );
        $orderinfo = Db::table('t_sub_orders')
            ->alias('a')
            ->field('a.f_oid orderid,a.f_userid userid,a.f_status status,a.f_orderdetail orderdetail,a.f_ordermoney ordermoney,a.f_ordermoney allmoney,a.f_paytype paytype,a.f_addtime addtime')
            ->where($where)
            ->find();
        return $orderinfo?$orderinfo:false;
    }

    /**
     * 获取交易子订单信息
     * @param $uid
     * @param $orderid
     * @return array|false|\PDOStatement|string|Model
     */
    public function getTradeSubOrderInfo($uid, $orderid){
        $table_name = 'sub_orders';
        $orderinfo = Db::name($table_name)
            ->where('f_userid',$uid)
            ->where('f_oid',$orderid)
            ->field('f_status as status')
            ->field('f_ordermoney as allmoney')
            ->field('f_paymoney as paymoney')
            ->find();
        return $orderinfo;
    }

    /**
     * 根据父订单ID获取子订单列表
     */
    public function getSubOrderList($userid, $parentid)
    {
        $where = array(
            'a.f_userid' => $userid,
            'a.f_parentid' => $parentid
        );
        $orderlist = Db::table('t_sub_orders')
            ->alias('a')
            ->field('a.f_oid orderid,a.f_parentid parentid,a.f_userid userid,a.f_status status,a.f_orderdetail orderdetail,a.f_ordermoney ordermoney,a.f_addtime addtime')
            ->where($where)
            ->find();
        return $orderlist;
    }

    /**
     * 获取用户扫码用餐订单信息
     */
    public function getScanOrderInfo($userid,$shopid,$deskid){
        $table_name = "orders";
        $orderinfo = Db::name($table_name)
            ->where('f_userid',$userid)
            ->where('f_shopid',$shopid)
            ->where('f_deskid',$deskid)
            ->where('f_type',2)
            ->where('f_status',$this->status_pay_suc)
            ->field('f_oid as orderid')
            ->order('f_startime','desc')
            ->limit(1)
            ->find();
        return $orderinfo;
    }
}