<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 17-4-25
 * Time: 下午9:28
 */
namespace app\dineshop\model;

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
     * 获取订单列表
     */
    public function getAllOrderList($sid)
    {   
        $field = array(
            'f_oid',
            'f_userid',
            'f_startime',
            'f_endtime',
            'f_mealsnum',
            'f_deskid',
            'f_orderdetail',
            'f_ordermoney',
            'f_status'
            );
        $status = array(
            '0'=>'初始',
            '1'=>'未付款',
            '2'=>'已付款',
            '3'=>'配送中',
            '4'=>'配送完成',
            '5'=>'用餐中',
            '6'=>'申请打包',
            '90'=>'已打包',
            '100'=>'已完成',
            '-100'=>'逾期',
            '-110'=>'退款待审核',
            '-120'=>'退款审核通过',
            '-130'=>'退款审核不通过',
            '-200'=>'退款中',
            '-300'=>'退款完成',
            '-400'=>'已取消'
            );
        $where = "f_type = 2 and f_shopid = '$sid'";
        $orders = Db::table('t_orders')->where($where)->select();
        foreach($orders as $key => $val){
          $orders[$key]['f_eattime'] = $val['f_startime']." - ".date("H:i:s",strtotime($val['f_endtime']));
          $orders[$key]['f_status'] = $status[$val['f_status']];
        }
        return $orders;
    }

    /**
     *  查看订单
     **/
    public function getAllDetails($oid)
    {   
        $where = "f_oid = '$oid'";
        $foodnum = Db::table('t_food_menu');
        $list = $foodnum->field('f_foodname as foodname,f_foodnum as foodnum,f_foodprice as foodprice')->where($where)->select();
        return $list;
    }

    /**
     *  搜索订单
     **/
    public function findOne($name,$dataDay,$dataDay1,$timeDay,$timeDay1,$sid)
    {   
        $status = array(
            '0'=>'初始',
            '1'=>'未付款',
            '2'=>'已付款',
            '3'=>'配送中',
            '4'=>'配送完成',
            '5'=>'用餐中',
            '6'=>'申请打包',
            '90'=>'已打包',
            '100'=>'已完成',
            '-100'=>'逾期',
            '-110'=>'退款待审核',
            '-120'=>'退款审核通过',
            '-130'=>'退款审核不通过',
            '-200'=>'退款中',
            '-300'=>'退款完成',
            '-400'=>'已取消'
            );
        $where = "f_foodname like '%".$name."%'";
        $menu = Db::table('t_food_menu');
        $list = $menu->field('f_oid')->where("$where")->select();
        $oid = '';
        foreach($list as $key => $val){
          $oid .= $val['f_oid'].',';
        }
        $order = Db::table('t_orders');
        $oid = rtrim($oid,',');
        $where1 = "f_shopid = '$sid' and f_type = 2";
        if(!empty($oid)){
          $where1 .= ' and f_oid in ('. $oid .')';
          if(!empty($timeDay) && !empty($timeDay1)){
                $where1 .= " and f_addtime >='".$dataDay.' '.$timeDay."' and f_addtime <='".$dataDay1.' '.$timeDay1."'";
          }else{
            if(!empty($dataDay) && !empty($dataDay1)){
              if(strtotime($dataDay) > strtotime($dataDay1)){
                $dataDay2 = $dataDay1;
                $dataDay1 = $dataDay;
                $dataDay = $dataDay2;
                $where1 .= " and f_addtime >='".$dataDay."' and f_addtime <='".$dataDay1."'";
              }else{
                $where1 .= " and f_addtime >='".$dataDay."' and f_addtime <='".$dataDay1."'";
              }
            }else if(!empty($dataDay)){
              $where1 .= " and f_addtime >='".$dataDay."'";
            }else if(!empty($dataDay1)){
              $where1 .= " and f_addtime >='".$dataDay1."'";
            }
          }
        }
        
        // return $where1;
        
        $list = $order->where($where1)->select();
        foreach($list as $key => $val){
          $list[$key]['f_eattime'] = $val['f_startime']." - ".date("H:i:s",strtotime($val['f_endtime']));
          $list[$key]['f_status'] = $status[$val['f_status']];
        }
        return $list;
    }


    /**
     *  查询菜肴统计
     */
    public function getAllAdvance($sid)
    {
      $where = "a.f_sid = '$sid' and a.f_state = 1";
      $advance = Db::table('t_food_dishes');
      $list = $advance->alias('a')->field('a.f_name,a.f_salenum,b.f_shopname')->join('t_dineshop b','a.f_sid=b.f_sid','left')->where($where)->select();
      return $list;
    }

    /**
     *  模糊查询菜肴统计
     */
    public function getSearchAdvance($foodname='',$sid)
    {
      $where = "a.f_state = 1 and a.f_sid = '$sid'";
      if(!empty($foodname)){
        $where .= " and a.f_name like '%".$foodname."%'";
      }
      $advance = Db::table('t_food_dishes');
      $list = $advance->alias('a')->field('a.f_name,a.f_salenum,b.f_shopname')->join('t_dineshop b','a.f_sid=b.f_sid','left')->where($where)->select();
      return $list;
    }

    /**
     *  时间查询菜肴统计
     */
    public function getSearchTimer($dataDay='',$dataDay1='',$timeDay='',$timeDay1='',$foodname='',$sid='')
    {
      $where = "a.f_sid = '$sid' and a.f_state = 1";
      if(!empty($timeDay) && !empty($timeDay1)){
              $where .= " and f_lasttime >='".$dataDay.' '.$timeDay."' and f_lasttime <='".$dataDay1.' '.$timeDay1."'";
        }else{
          if(!empty($dataDay) && !empty($dataDay1)){
            if(strtotime($dataDay) > strtotime($dataDay1)){
              $dataDay2 = $dataDay1;
              $dataDay1 = $dataDay;
              $dataDay = $dataDay2;
              $where .= " and f_lasttime >='".$dataDay."' and f_lasttime <='".$dataDay1."'";
            }else{
              $where .= " and f_lasttime >='".$dataDay."' and f_lasttime <='".$dataDay1."'";
            }
          }else if(!empty($dataDay)){
            $where .= " and f_lasttime >='".$dataDay."'";
          }else if(!empty($dataDay1)){
            $where .= " and f_lasttime >='".$dataDay1."'";
          }
        }
        if(!empty($foodname)){
          $where .= " and a.f_name like '%".$foodname."%'";
          if(!empty($dineshop)){
            $where .= " and b.f_shopname like '%".$dineshop."%'";
          }
        }else if(!empty($dineshop)){
          $where .= " and b.f_shopname like '%".$dineshop."%'";
          if(!empty($foodname)){
            $where .= " and b.f_shopname like '%".$foodname."%'";
          }
        }
      $advance = Db::table('t_food_dishes');
      $list = $advance->alias('a')->field('a.f_name,a.f_salenum,b.f_shopname')->join('t_dineshop b','a.f_sid=b.f_sid','left')->where($where)->select();
      return $list;
    }

    /**
     *  获取所有外卖订单
     */
    public function getAllTakeOut($sid)
    {
        $status = array(
            '0'=>'初始',
            '1'=>'未付款',
            '2'=>'已付款',
            '3'=>'配送中',
            '4'=>'配送完成',
            '5'=>'用餐中',
            '6'=>'申请打包',
            '90'=>'已打包',
            '100'=>'已完成',
            '-100'=>'逾期',
            '-110'=>'退款待审核',
            '-120'=>'退款审核通过',
            '-130'=>'退款审核不通过',
            '-200'=>'退款中',
            '-300'=>'退款完成',
            '-400'=>'已取消'
            );
        $res = array(
          '1'=>array('status'=>'','name'=>''),
          '2'=>array('status'=>'3','name'=>'配送'),
          '3'=>array('status'=>'100','name'=>'配送完成')
          );
        $where = "f_shopid = '$sid' and f_type = 1";
        $orders = Db::table('t_orders')->alias('a')->field('a.f_oid,a.f_ordermoney,a.f_status,b.f_mobile,c.f_name,c.f_province,c.f_city,c.f_address,c.f_mobile as f_phone')->join('t_user_info b','a.f_userid=b.f_uid','left')->join('t_user_address_info c','b.f_uid=c.f_uid','left')->where($where)->select();
        foreach($orders as $key => $val){
          // $orders[$key]['f_eattime'] = $val['f_startime']." - ".date("H:i:s",strtotime($val['f_endtime']));
          $f_oid = $val['f_oid'];
          if($orders[$key]['f_status'] == 2 || $orders[$key]['f_status'] == 3){
            $orders[$key]['f_ysname'] = $res[$val['f_status']];
          }else{
            $orders[$key]['f_ysname'] = $res[1];
          }
          $orders[$key]['f_status'] = $status[$val['f_status']];
          $orders[$key]['f_foodname'] = Db::table('t_food_menu')->where("f_oid='$f_oid'")->select();
        }
        return $orders;
    }


    /**
     * 修改外卖订单状态
     */
    public function editStatus($sid,$oid,$status)
    {
        $where = "f_shopid = '$sid' and f_oid = '$oid'";
        $update = array();
        $update['f_status'] = $status;
        $res = Db::table('t_orders')->where($where)->update($update);
      
        return $res;
    }

    /**
     * 当天列表
     */
    public function getTodayList($sid)
    {
        $where = "f_shopid = '$sid' and f_type = 2";
        $list = Db::table('t_orders')
        ->field('f_deskid','deskid')
        ->field('f_oid','oid')
        ->field('f_startime','startime')
        ->field('f_endtime','endtime')
        ->field('f_userid','userid')
        ->field('f_shopid','shopid')
        ->field('f_mealsnum','mealsnum')
        ->field('f_mealsnum','mealsnum')
        ->where($where)->select(false);
    }


    /**
     *  模糊查询菜肴
     */
    // public function SearchFoodName($foodsname)
    // {
    //    $where = "f_foodname like '%".$foodsname."%'";
    //    $foodmenu = Db::table('t_food_menu');
    //    $list = $foodmenu->where($where)->select();
    //    return $list;
    // }
  //   public function getTakeoutlist($startime, $endtime, $shopid = '', $orderid = '', $page = 1, $pagesize = 20)
  //   {
  //       $where = array(
  //           'a.f_type' => 1
  //       );
		// if (is_numeric($shopid)){
		// 	$where['a.f_shopid'] = $shopid;
  //       }else if(!empty($shopid)){
  //           $where['b.f_shopname'] = array('like','%'.$shopid.'%');
  //       }
  //       if(!empty($orderid)) {
  //           $where['a.f_oid'] = $orderid;
  //       }else{
  //           $where['a.f_addtime'] = array('between time', [$startime.' 00:00:00', $endtime.' 23:59:59']);
  //       }
  //       $allnum = Db::table('t_orders')->alias('a')->join('t_dineshop b','a.f_shopid = b.f_sid','left')->where($where)->count();
  //       $orderlist = Db::table('t_orders')
  //           ->alias('a')
  //           ->field('a.f_oid orderid,a.f_shopid shopid,b.f_shopname shopname,a.f_userid userid,a.f_type ordertype,a.f_status status,a.f_orderdetail orderdetail,a.f_ordermoney ordermoney,a.f_deliverymoney deliverymoney,a.f_allmoney allmoney,a.f_paymoney paymoney,a.f_paytype paytype,d.f_name recipientname,d.f_mobile recipientmobile,c.f_username deliveryname,c.f_mobile deliverymobie,a.f_deliverytime deliverytime,CONCAT(d.f_province,d.f_city,d.f_address) deliveryaddress,a.f_addtime addtime')
  //           ->join('t_dineshop b','a.f_shopid = b.f_sid','left')
  //           ->join('t_dineshop_distripersion c','a.f_deliveryid = c.f_id','left')
  //           ->join('t_user_address_info d','a.f_addressid = d.f_id','left')
  //           ->where($where)
  //           ->order('a.f_addtime desc')
  //           ->page($page, $pagesize)
  //           ->select();
  //       return array(
  //           "allnum" => $allnum,
  //           "orderlist" => $orderlist
  //       );
  //   }
  //   /**
  //    * 获取食堂订单列表
  //    */
  //   public function getEatinlist($startime, $endtime, $shopid = '', $orderid = '', $page = 1, $pagesize = 20)
  //   {
  //       $where = array(
  //           'a.f_type' => 2,
  //           'a.f_addtime' => array('between time', [$startime, $endtime])
  //       );
  //       if (is_numeric($shopid)){
		// 	$where['a.f_shopid'] = $shopid;
  //       }else if(!empty($shopid)){
  //           $where['b.f_shopname'] = array('like','%'.$shopid.'%');
  //       }
  //       if(!empty($orderid)) {
  //           $where['a.f_oid'] = $orderid;
  //       }
  //       $allnum = Db::table('t_orders')->alias('a')->join('t_dineshop b','a.f_shopid = b.f_sid','left')->where($where)->count();
  //       $orderlist = Db::table('t_orders')
  //           ->alias('a')
  //           ->field('a.f_oid orderid,a.f_shopid shopid,b.f_shopname shopname,a.f_userid userid,c.f_mobile usermobile,a.f_deskid deskid,d.f_seatnum seatnum,a.f_type ordertype,a.f_status status,a.f_orderdetail orderdetail,a.f_ordermoney ordermoney,a.f_deliverymoney deliverymoney,a.f_allmoney allmoney,a.f_paymoney paymoney,a.f_paytype paytype,a.f_mealsnum mealsnum,a.f_startime startime,a.f_endtime endtime,a.f_addtime addtime')
  //           ->join('t_dineshop b','a.f_shopid = b.f_sid','left')
  //           ->join('t_user_info c','a.f_userid = c.f_uid','left')
  //           ->join('t_dineshop_deskinfo d','a.f_deskid = d.f_id','left')
  //           ->where($where)
  //           ->order('a.f_addtime desc')
  //           ->page($page, $pagesize)
  //           ->select();
  //       return array(
  //           "allnum" => $allnum,
  //           "orderlist" => $orderlist
  //       );
  //   }  
  //   /**
  //    * 处理订单
  //    */
  //   public function processOrder($orderid, $data)
  //   {
  //       $res = array();
  //       $update = array();
  //       if(isset($data['status'])) $update['f_status'] = $data['status'];
  //       if(isset($data['distripid'])) $update['f_deliveryid'] = $data['distripid'];
  //       if(count($update) > 0){
  //           $res = Db::table('t_orders')->where('f_oid', $orderid)->update($update);
  //       }
  //       return $res;
  //   }
  //   /**
  //    * 获取订单详情
  //    */
  //   public function deliveryOrder($orderid, $distripid)
  //   {
  //       $res = Db::table('t_orders')->where('f_oid', $orderid)->update(array('f_status' => 3, 'f_deliveryid' => $distripid));
  //       return $res;
  //   }

  //   /**
  //    * 获取订单详情
  //    */
  //   public function getOrderinfo($userid, $orderid)
  //   {
  //       $where = array(
  //           'a.f_userid' => $userid,
  //           'a.f_oid' => $orderid
  //       );
  //       $orderinfo = Db::table('t_orders')
  //           ->alias('a')
  //           ->field('a.f_oid orderid,a.f_shopid shopid,b.f_shopname shopname,a.f_userid userid,a.f_type ordertype,a.f_status status,a.f_orderdetail orderdetail,a.f_ordermoney ordermoney,a.f_deliverymoney deliverymoney,a.f_allmoney allmoney,a.f_paymoney paymoney,a.f_paytype paytype,a.f_mealsnum mealsnum,a.f_servicemoney servicemoney,a.f_deskid deskid,a.f_startime startime,a.f_endtime endtime,c.f_name recipientname,c.f_mobile recipientmobile,d.f_username deliveryname,d.f_mobile deliveryphone,a.f_deliverytime deliverytime,CONCAT(c.f_province,c.f_city,c.f_address) deliveryaddress,a.f_addtime addtime')
  //           ->join('t_dineshop b','a.f_shopid = b.f_sid','left')
  //           ->join('t_user_address_info c', 'a.f_addressid = c.f_id','left')
  //           ->join('t_dineshop_distripersion d', 'a.f_deliveryid = d.f_id','left')
  //           ->where($where)
  //           ->find();
  //       return $orderinfo?$orderinfo:false;
  //   }

  //   /**
  //    * 更新交易订单信息
  //    * @param $uid
  //    * @param $orderid
  //    * @param $status
  //    * @param $paymoney
  //    * @return bool
  //    */
  //   public function updateTradeOrderInfo($uid, $orderid, $status, $paymoney=0){
  //       $sql = "update t_orders set f_status = :status, f_paymoney = f_paymoney + :paymoney where f_userid = :uid and f_oid = :orderid";
  //       $args = array(
  //           "uid" => $uid,
  //           "orderid" => $orderid,
  //           "status" => $status,
  //           "paymoney" => $paymoney
  //       );
  //       $ret = Db::execute($sql,$args);
  //       if($ret !== false){
  //           return true;
  //       }else{
  //           return false;
  //       }
  //   }

  //   /**
  //    * 获取交易订单信息(订单支付退款用)
  //    * @param $uid
  //    * @param $orderid
  //    * @return array|false|\PDOStatement|string|Model
  //    */
  //   public function getTradeOrderInfo($uid, $orderid){
  //       $table_name = 'orders';
  //       $orderinfo = Db::name($table_name)
  //           ->where('f_userid',$uid)
  //           ->where('f_oid',$orderid)
  //           ->field('f_status as status')
  //           ->field('f_allmoney as allmoney')
  //           ->field('f_paymoney as paymoney')
  //           ->find();
  //       return $orderinfo;
  //   }

  //   /**
  //    * 堂食订单取消后更新预订桌型数量
  //    */
  //   public function cancelTradeOrderDeskOrdernum($deskid){
  //       $table_name = 'dineshop_deskinfo';
  //       Db::name($table_name)
  //           ->where('f_id',$deskid)
  //           ->setDec('f_orderamount');
  //   }

  //   /**
  //    * 获取待处理订单列表
  //    */
  //   public function getPendingOrderList($pending_list,$limit_num=100){
  //       $table_name = "orders";
  //       $order_list = Db::name($table_name)
  //           ->where('f_status','in',$pending_list)
  //           ->field('f_oid as orderid')
  //           ->field('f_userid as userid')
  //           ->field('f_type as ordertype')
  //           ->field('f_status as status')
  //           ->field('f_shopid as shopid')
  //           ->field('f_deskid as deskid')
  //           ->field('f_addtime as addtime')
  //           ->field('f_startime as startime')
  //           ->field('f_endtime as endtime')
  //           ->order('f_addtime desc')
  //           ->limit($limit_num)
  //           ->select();
  //       return $order_list;
  //   }

  //   /**
  //    * 释放桌型
  //    * @param $shopid
  //    * @param $deskid
  //    * @return bool
  //    */
  //   public function releaseDesk($shopid, $deskid){
  //       $table_deskinfo = 'dineshop_deskinfo';
  //       $retup = Db::name($table_deskinfo)
  //           ->where('f_sid',$shopid)
  //           ->where('f_deskid',$deskid)
  //           ->setInc('f_orderamount');
  //       if($retup !== false){
  //           return true;
  //       }
  //       return false;
  //   }
}