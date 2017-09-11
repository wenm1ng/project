<?php
namespace app\data\controller;

use base\Baseapi;
use \app\data\model\UserModel;
use \app\data\model\DineshopModel;
use \app\data\model\DishesModel;
use \app\data\model\OrderModel;
use think\Log;

class Order extends Baseapi
{
    /**
     * 新增订单
     * @return \think\response\Json
     */
    //http://shanwei.boss.com/data/order/createOrder?uid=10002&ck=ck_NGE5NJA5NWVMMTIYNWJKZMRLOWZKODFLNMM3YTVKZTU=&shopid=8&orderdetail=1|1@1,2|2@1,23|3@1&ordermoney=216&deliverymoney=9&allmoney=225&paytype=0&ordertype=1&deliverytime=2017-05-02%2012:00:00&addressid=1
    //http://shanwei.boss.com/data/order/createOrder?uid=10002&ck=ck_NGE5NJA5NWVMMTIYNWJKZMRLOWZKODFLNMM3YTVKZTU=&shopid=8&orderdetail=1|1@1,2|2@1,23|3@1&ordermoney=216&deliverymoney=9&allmoney=225&paytype=0&ordertype=2&mealsnum=2&startime=2017-05-02%2012:00:00&endtime=2017-05-02%2012:00:00
    public function createOrder()
    {
        $uid = input('uid'); //用户ID
        $shopid = input('shopid'); //店铺ID
        $orderdetail = input('orderdetail'); //订单明细
        $ordermoney = floatval(input('ordermoney','0')); //订单金额
        $deliverymoney = floatval(input('deliverymoney','0')); //配送费
        $allmoney = floatval(input('allmoney','0')); //订单总金额
        $paytype = input('paytype'); //支付方式
        $ordertype = input('ordertype'); //订单类型（1,外卖订单  2,食堂订单）
        $deliverytime = input('deliverytime'); //外卖 配送时间
        $addressid = input('addressid'); //外卖 配送地址ID
        $mealsnum = input('mealsnum'); //食堂就餐 就餐人数
        $deskid = input('deskid',0); //食堂就餐 预订桌型ID
        $servicemoney = input('servicemoney',0); //食堂就餐 服务费
        $startime = input('startime'); //食堂订餐 开始时间
        $endtime = input('endtime'); //食堂订餐 结束时间
        $date = input('date'); //折扣日期
        $slotid = input('slotid'); //折扣时间段
        //判断用户登录
        if($this->checkLogin() === false) return json($this->errjson(-10001));
        //判断参数
        if(!$shopid) return json($this->errjson(-30001));
        if(!$orderdetail) return json($this->errjson(-30002));
        if($ordermoney == 0 || $allmoney == 0 || $ordermoney + $deliverymoney + $servicemoney != $allmoney){
            return json($this->errjson(-30003)); 
        }
        if($paytype == '') return json($this->errjson(-30004));
        if(!in_array($ordertype, array('1','2'))) return json($this->errjson(-30005));
        if($ordertype == 1){
            if(!$deliverytime) return json($this->errjson( -30006));
            if(!check_datetime($deliverytime)) return json($this->errjson(-30007));
            if(!$addressid) return json($this->errjson(-30008));
        }else if($ordertype == 2){
            if(!$mealsnum) return json($this->errjson(-30009));
            if(!$startime) return json($this->errjson(-30010));
            if(!check_datetime($startime)) return json($this->errjson(-30011));
            if(!$endtime) return json($this->errjson(-30012));
            if(!check_datetime($endtime)) return json($this->errjson(-30013));
        }
        //验证用户
        $UserModel = new UserModel();
        $userinfo = $UserModel->getUserInfoByUid($uid);
        if(empty($userinfo)) return json($this->errjson(-30014));
        //验证店铺
        $DineshopModel = new DineshopModel();
        $shopinfo = $DineshopModel->getShopInfo($shopid);
        if(empty($shopinfo)) return json($this->errjson(-30015));
        //验证订单金额
        $DishesModel = new DishesModel();
        $_orderinfo = array();
        foreach(explode(',', $orderdetail) as $key=>$val){
            preg_match('/(\d+)\|(\d+)\@(\d+)/i', $val, $match);
            $_orderinfo[$match[1]] = $match[3];
        }
        //获取折扣信息
        $_discount = array();
        $res = $DineshopModel->getDineshopDiscount($shopid, $slotid, $date);
        if($res && $res['discount']){
            foreach(explode('$', $res['discount']) as $key=>$val){
                preg_match('/(\d+)\|(\d+)\@(([1-9]\d*|0)(\.\d{1,2})?)/i', $val, $match);
                $_discount[$match[1]] = array(
                    "type" => $match[2],
                    "discount" => $match[3]
                );
            }
        }
        $list = $DishesModel->getDishesList(implode(',', array_keys($_orderinfo)));
        $priceinfo = array();
        if(count($list) > 0){
            for($i = 0; $i < count($list); $i++){
                $_dishid = $list[$i]['id'];
                $_price = floatval($list[$i]['price']);
                if(isset($_discount[$_dishid])){
                    if($_discount[$_dishid]['type'] == 1){
                        $_price = $_price * $_discount[$_dishid]['discount'];
                    }else if($_discount[$_dishid]['type'] == 2){
                        $_price = $_price - $_discount[$_dishid]['discount'];
                    }
                }else{
                    $_price = $_price*floatval($list[$i]['discount']);
                }
                $priceinfo[$_dishid] = $_price;
            }
        }
        $_ordermoney = 0;
        foreach(explode(',', $orderdetail) as $key=>$val){
            if(preg_match('/^(\d+)\|(\d+)\@(\d+)$/i', $val)){
                preg_match('/(\d+)\|(\d+)\@(\d+)/i', $val, $match);
                $_dishid = $match[1];
                $_dishnum = $match[3];
                if(!isset($priceinfo[$_dishid])){
                    return json($this->erres('['.$_dishid.']菜肴信息不存在'));
                }
                $_ordermoney += $priceinfo[$_dishid] * $_dishnum;
            }else{
                return json($this->erres('订单格式不正确'));
            }
        }
        /*if($_ordermoney != $ordermoney){
            return json($this->errjson(-30017));
        }*/
        //验证外卖配送地址
        if($ordertype == 1){
            $addressinfo = $UserModel->getAddressInfo($addressid);
            if(empty($addressinfo)) return json($this->errjson(-30018));
        }
        //创建订单
        $OrderModel = new OrderModel();
        //先验证订单是否已添加
        $orderid = $OrderModel->checkOrder($uid, $shopid, $orderdetail, $ordertype);
        if($orderid){
            return json($this->sucjson(array('orderid' => $orderid)));
        }else{
            if($ordertype == 1){
                $orderid = $OrderModel->addTakeoutOrders($uid, $shopid, $orderdetail, $ordermoney, $deliverymoney, $allmoney, $paytype, $deliverytime, $addressid);
            }else{
                $orderid = $OrderModel->addEatinOrders($uid, $shopid, $orderdetail, $ordermoney, $deliverymoney, $allmoney, $paytype, $mealsnum, $startime, $endtime, $servicemoney, $deskid);
            }
            if($orderid){
                if($_ordermoney != $ordermoney){
                    Log::record('wayde-orderid='.$orderid,'error');
                    Log::record('wayde-orderid='.$orderdetail,'error');
                    Log::record($priceinfo,'error');
                    Log::record('wayde-'.$_ordermoney.'---'.$ordermoney,'error');
                    Log::record('error-30017,订单金额不正确','error');
                }
                return json($this->sucjson(array('orderid' => $orderid)));
            }else{
                return json($this->errjson(-30019));
            }
        }
    }
    
    /**
     * 完成订单
     * (前端仅余额支付方式时调用)
     */
    public function finishOrder(){
        $uid = input('uid'); //用户ID
        $orderid = input('orderid'); //用户ID
        //判断用户登录
        if($this->checkLogin() === false) return json($this->errjson(-10001));
        //获取订单信息
        $OrderModel = new OrderModel();
        $orderinfo = $OrderModel->getOrderinfo($uid, $orderid);
        if(!$orderinfo)  return json($this->errjson(-30020));
        $status = intval($orderinfo['status']); //订单支付状态 1为未付款
        $allmoney = floatval($orderinfo['allmoney']);
        $userid = $orderinfo['userid'];
        if($status == $OrderModel->status_waiting_pay){
            //检查用户余额
            if(!$this->checkMoneyEnough($userid,$allmoney)) return json($this->errjson(-10002));
            //扣款完成订单
            $ret = $OrderModel->finishOrder($userid, $orderid, $allmoney);
            if(!$ret){
                return json($this->errjson(-30021));
            }
        }
        return json($this->sucres());
    }
    
    /**
     * 验证用户金额时候充足
     */
    public function checkMoneyEnough($userid, $allmoney){
        $UserModel = new UserModel();
        $userinfo = $UserModel->getUserInfoByUid($userid);
        $usermoney = floatval($userinfo['usermoney']);
        return $usermoney >= $allmoney;
    }
    
    /**
     * 获取订单列表
     */
    public function getOrderlist(){
        $info = array();
        $list = array();
        $uid = input('uid'); //用户ID
        $ordertype = input('ordertype', 1); //订单类型（1,外卖订单  2,食堂订单）
        $page = input('page', 1); //页数
        $pagesize = input('pagesize', 20); //每页显示条数
        //判断用户登录
        if($this->checkLogin() === false) return json($this->errjson(-10001));
        $OrderModel = new OrderModel();
        $res = $OrderModel->getOrderlist($uid, $ordertype, $page, $pagesize);
        if(!empty($res) && !empty($res['orderlist'])){
            $DineshopModel = new DineshopModel();
            foreach($res['orderlist'] as $k=>$value){
                $shopid = $value['shopid'];
                $dineshop_info = $DineshopModel->getShopInfo($shopid);
                $res['orderlist'][$k]['shopicon'] = $dineshop_info['shopicon'];
                $res['orderlist'][$k]['address'] = $dineshop_info['address'];
            }
        }
        $info["allnum"] = $res["allnum"];
        $info["totalpage"] = ceil($res["allnum"]/$pagesize);
        if($res["orderlist"]) {
            $list = $res["orderlist"];
            $orderlist = array();
            $dishid = array();
            foreach($list as $key=>$val){
                $orderdetail = $val['orderdetail'];
                preg_match_all('/(\d+)\|(\d+)\@(\d+)/i', $orderdetail, $match);
                if($match){
                    $orderlist = array_combine($match[1], $match[0]);
                    $dishid = array_merge($dishid, $match[1]);
                }
                $list[$key]['orderlist'] = $orderlist;
            }
            $DishesModel = new DishesModel();
            $dishlist = $DishesModel->getDishesList(implode(',', array_unique($dishid)));
            $dishinfo = array();
            if($dishlist){
                foreach($dishlist as $key => $val){
                    $dishinfo[$val['id']] = $val;
                }
            }
            foreach($list as $key => $val){
                $orderlist = array();
                foreach($val['orderlist'] as $k => $v){
                    preg_match('/(\d+)\|(\d+)\@(\d+)/i', $v, $match);
                    $tastesid = $match[2];
                    $num = $match[3];
                    $orderinfo = isset($dishinfo[$k])?$dishinfo[$k]:array();
                    $orderinfo['num'] = $num;
                    array_push($orderlist, $orderinfo);
                }
                $list[$key]['orderlist'] = $orderlist;
            }
        }
        return json($this->sucres($info, $list));
    }
    
    /**
     * 获取订单详情
     */
    public function getOrderinfo(){
        $info = array();
        $list = array();
        $orderid = input('orderid'); 
        $uid = input('uid');
        //判断用户登录
        if($this->checkLogin() === false) return json($this->errjson(-10001));
        $OrderModel = new OrderModel();
        $res = $OrderModel->getOrderinfo($uid, $orderid);
        $dishid = array();
        $orderlist = array();
        if($res){
            $info = $res;
            $orderdetail = $res['orderdetail'];
            preg_match_all('/(\d+)\|(\d+)\@(\d+)/i', $orderdetail, $match);
            preg_match_all('/(\d+)\|(\d+)\@(\d+)/i', $orderdetail, $match);
            if($match){
                $orderlist = array_combine($match[1], $match[0]);
                $dishid = array_merge($dishid, $match[1]);
            }
            $DishesModel = new DishesModel();
            $dishlist = $DishesModel->getDishesList(implode(',', array_unique($dishid)));
            $dishinfo = array();
            if($dishlist){
                foreach($dishlist as $key => $val){
                    $dishinfo[$val['id']]['icon'] = $val['icon'];
                    $dishinfo[$val['id']]['dishesname'] = $val['dishesname'];
                    $dishinfo[$val['id']]['price'] = $val['price'];
                }
            }
            foreach($orderlist as $k => $v){
                preg_match('/(\d+)\|(\d+)\@(\d+)/i', $v, $match);
                $num = $match[3];
                $orderlist[$k] = isset($dishinfo[$k])?$dishinfo[$k]:array();
                $orderlist[$k]['num'] = $num;
            }
            $info['orderlist'] = $orderlist;
            $suborder_list = array();
            if($res['hassuborder'] != 0){
                $suborder_list = $OrderModel->getSubOrderList($uid,$orderid);
            }
            $info['suborderlist'] = $suborder_list;
        }
        return json($this->sucres($info, $list));
    }

    /**
     * 取消订单(退款)
     */
    public function cancelOrder()
    {
        $uid = input('uid'); //用户ID
        $orderid = input('orderid'); //订单号
        //判断用户登录
        if ($this->checkLogin() === false) return json($this->errjson(-10001));

        //检查订单状态
        $OrderModel = new OrderModel();
        $orderinfo  = $OrderModel->getOrderinfo($uid,$orderid);
        if(empty($orderinfo)){
            return json(self::errjson(-30020));
        }
        $ordertype = intval($orderinfo['ordertype']); //1,外卖订单  2,食堂订单
        $orderstatus = intval($orderinfo['status']);  //订单状态（0,初始 1,未付款 2,已付款 3,配送中 4,配送完成 5,用餐中 100,已完成 -100逾期 -110退款待审核 -120退款审核通过 -130退款审核不通过  -200退款中 -300退款完成， -400已取消）
        if($ordertype == 1){
            if($orderstatus != 2){
                return json(self::errjson(-30022));
            }
        }elseif($ordertype == 2){
            $startime = $orderinfo['startime']; //用餐开始时间
            if($orderstatus !=2 || strtotime($startime)-time() < 24*3600){
                return json(self::errjson(-30022));
            }
        }

        //更新订单状态为退款待审核状态
        $wait_checkup = $OrderModel->updateTradeOrderInfo($uid,$orderid,$OrderModel->status_waiting_checkup_refund);
        if(!$wait_checkup){
            return json(self::errjson(-30023));
        }
        return json(self::sucjson());
    }

    /**
     * 修改订单信息
     */
    public function updateOrderInfo()
    {
        $uid = input('uid'); //用户ID
        $orderid = input('orderid'); //订单号
        $paytype = intval(input('paytype',-1)); //支付方式
        //判断用户登录
        if ($this->checkLogin() === false) return json($this->errjson(-10001));
        if(!in_array($paytype,array(0,1,2))){
            return json(self::errjson(-30004));
        }

        //检查订单状态
        $OrderModel = new OrderModel();
        $orderinfo  = $OrderModel->getOrderinfo($uid,$orderid);
        if(empty($orderinfo)){
            return json(self::errjson(-30020));
        }
        $ori_paytype = $orderinfo['paytype'];
        $ori_status = $orderinfo['status'];
        if($ori_status != $OrderModel->status_waiting_pay){
            return json(self::errjson(-30024));
        }
        if($ori_paytype == $paytype){
            return json(self::sucjson());
        }
        $orderinfo = array(
            'paytype' => $paytype,
        );
        if($OrderModel->updateOrderInfo($uid,$orderid,$orderinfo)){
            return json(self::sucjson());
        }else{
            return json(self::errjson());
        }
    }

    /**
     * 创建堂食子订单信息
     */
    public function createSubOrder(){
        $uid = input('uid'); //用户ID
        $parentidid = input('parentid'); //父订单ID
        $orderdetail = input('orderdetail'); //订单明细
        $ordermoney = floatval(input('ordermoney','0')); //订单金额
        $paytype = input('paytype'); //支付方式
        $date = input('date'); //折扣日期
        $slotid = input('slotid'); //折扣时间段
        //判断用户登录
        if($this->checkLogin() === false) return json($this->errjson(-10001));
        //判断参数
        if(empty($parentidid)) return json($this->errjson(-30025));
        if(!$orderdetail) return json($this->errjson(-30002));
        if($ordermoney == 0){
            return json($this->errjson(-30003));
        }
        if($paytype == '') return json($this->errjson(-30004));
        //验证用户
        $UserModel = new UserModel();
        $userinfo = $UserModel->getUserInfoByUid($uid);
        if(empty($userinfo)) return json($this->errjson(-30014));

        //验证父订单是否存在
        $OrderModel = new OrderModel();
        $parent_orderinfo = $OrderModel->getOrderinfo($uid,$parentidid);
        if(empty($parent_orderinfo)){
            return json(self::errjson(-30026));
        }
        $p_endtime = $parent_orderinfo['endtime'];
        $p_orderstatus = $parent_orderinfo['status'];
        $p_shopid = $parent_orderinfo['shopid'];
        //检查当前订单是否允许添加子订单
        if(config('suborder.endtime')+time() > strtotime($p_endtime)){
            return json(self::errjson(-30027));
        }
        if(!in_array($p_orderstatus,array(2))){
            return json(self::errjson(-30027));
        }

        $_orderinfo = array();
        foreach(explode(',', $orderdetail) as $key=>$val){
            preg_match('/(\d+)\|(\d+)\@(\d+)/i', $val, $match);
            $_orderinfo[$match[1]] = $match[3];
        }
        //获取折扣信息
        $DineshopModel = new DineshopModel();
        $_discount = array();
        $res = $DineshopModel->getDineshopDiscount($p_shopid, $slotid, $date);
        if($res && $res['discount']){
            foreach(explode('$', $res['discount']) as $key=>$val){
                preg_match('/(\d+)\|(\d+)\@(([1-9]\d*|0)(\.\d{1,2})?)/i', $val, $match);
                $_discount[$match[1]] = array(
                    "type" => $match[2],
                    "discount" => $match[3]
                );
            }
        }
        $DishesModel = new DishesModel();
        $list = $DishesModel->getDishesList(implode(',', array_keys($_orderinfo)));
        $priceinfo = array();
        if(count($list) > 0){
            for($i = 0; $i < count($list); $i++){
                $_dishid = $list[$i]['id'];
                $_price = floatval($list[$i]['price']);
                if(isset($_discount[$_dishid])){
                    if($_discount[$_dishid]['type'] == 1){
                        $_price = $_price * $_discount[$_dishid]['discount'];
                    }else if($_discount[$_dishid]['type'] == 2){
                        $_price = $_price - $_discount[$_dishid]['discount'];
                    }
                }
                $priceinfo[$_dishid] = $_price;
            }
        }
        $_ordermoney = 0;
        foreach(explode(',', $orderdetail) as $key=>$val){
            preg_match('/(\d+)\|(\d+)\@(\d+)/i', $val, $match);
            $_dishid = $match[1];
            $_dishnum = $match[3];
            $_ordermoney += $priceinfo[$_dishid] * $_dishnum;
        }
        /*if($_ordermoney != $ordermoney){
            return json($this->errjson(-30017));
        }*/

        //创建订单
        $OrderModel = new OrderModel();
        $orderid = $OrderModel->addEatinSubOrders($uid,$parentidid,$orderdetail,$ordermoney,$paytype);
        if($orderid){
            $orderinfo = array(
                'parentid' => $parentidid,
                'orderid' => $orderid,
            );
            if($_ordermoney != $ordermoney){
                Log::record('wayde-parentid='.$parentidid,'error');
                Log::record('wayde-orderid='.$orderid,'error');
                Log::record('wayde-orderdetail='.$orderdetail,'error');
                Log::record($priceinfo,'error');
                Log::record('wayde-'.$_ordermoney.'---'.$ordermoney,'error');
                Log::record('error-30017,suborder订单金额不正确','error');
            }
            return json($this->sucjson($orderinfo));
        }else{
            return json($this->errjson(-30019));
        }
    }

    /**
     * 修改子订单信息
     */
    public function updateSubOrderInfo()
    {
        $uid = input('uid'); //用户ID
        $orderid = input('orderid'); //订单号
        $paytype = intval(input('paytype',-1)); //支付方式
        //判断用户登录
        if ($this->checkLogin() === false) return json($this->errjson(-10001));
        if(!in_array($paytype,array(0,1,2))){
            return json(self::errjson(-30004));
        }

        //检查订单状态
        $OrderModel = new OrderModel();
        $orderinfo  = $OrderModel->getSubOrderinfo($uid,$orderid);
        if(empty($orderinfo)){
            return json(self::errjson(-30020));
        }
        $ori_paytype = $orderinfo['paytype'];
        $ori_status = $orderinfo['status'];
        if($ori_status != $OrderModel->status_waiting_pay){
            return json(self::errjson(-30024));
        }
        if($ori_paytype == $paytype){
            return json(self::sucjson());
        }
        $orderinfo = array(
            'paytype' => $paytype,
        );
        if($OrderModel->updateSubOrderInfo($uid,$orderid,$orderinfo)){
            return json(self::sucjson());
        }else{
            return json(self::errjson());
        }
    }

    /**
     * 完成子订单
     * (前端仅余额支付方式时调用)
     */
    public function finishSubOrder(){
        $uid = input('uid'); //用户ID
        $orderid = input('orderid'); //用户ID
        //判断用户登录
        if($this->checkLogin() === false) return json($this->errjson(-10001));
        //获取订单信息
        $OrderModel = new OrderModel();
        $orderinfo = $OrderModel->getSubOrderinfo($uid, $orderid);
        if(!$orderinfo)  return json($this->errjson(-30020));
        $status = intval($orderinfo['status']); //订单支付状态 1为未付款
        $allmoney = floatval($orderinfo['allmoney']);
        $userid = $orderinfo['userid'];
        if($status == $OrderModel->status_waiting_pay){
            //检查用户余额
            if(!$this->checkMoneyEnough($userid,$allmoney)) return json($this->errjson(-10002));
            //扣款完成订单
            $ret = $OrderModel->finishSubOrder($userid, $orderid, $allmoney);
            if(!$ret){
                return json($this->errjson(-30021));
            }
        }
        return json($this->sucres());
    }

    /**
     * 设置订单状态
     */
    public function setOrderStatus(){
        $uid = input('uid'); //用户ID
        $orderid = input('orderid'); //用户ID
        $orderstatus = intval(input('status',-1));

        //判断用户登录
        if($this->checkLogin() === false) return json($this->errjson(-10001));
        if(!in_array($orderstatus,array(5,6))){
            return json($this->errjson(-30028));
        }

        //获取订单信息
        $OrderModel = new OrderModel();
        $orderinfo = $OrderModel->getOrderinfo($uid, $orderid);
        if(!$orderinfo)  return json($this->errjson(-30020));
        $order_status = intval($orderinfo['status']);
        $order_type = intval($orderinfo['ordertype']);
        $order_starttime = $orderinfo['startime'];

        //堂食订单方可设置
        if($order_type == 2){
            //设置用餐中
            if($orderstatus == $OrderModel->status_start_eat){
                if($order_status == $OrderModel->status_pay_suc && time() > strtotime($order_starttime)){
                    if($OrderModel->updateTradeOrderInfo($uid,$orderid,$orderstatus)){
                        return json(self::sucjson());
                    }
                }
            }

            //设置申请打包
            if($orderstatus == $OrderModel->status_apply_packing){
                if($order_status == $OrderModel->status_pay_suc && time() > strtotime($order_starttime)){
                    if($OrderModel->updateTradeOrderInfo($uid,$orderid,$orderstatus)){
                        return json(self::sucjson());
                    }
                }
            }
        }

        return json(self::errjson());
    }

    /**
     * 扫码获取用户某店铺某桌型最近一笔未完成堂食订单ID
     */
    public function scan(){
        //获取参数
        $ck = input('ck');
        $uid = input('uid');
        $shopid = intval(input('shopid',-1));
        $deskid = intval(input('deskid',-1));

        if($shopid < 0 || $deskid < 0){
            return json(self::errjson(-20001));
        }

        //检查用户是否登录
        if(!self::checkLogin($uid,$ck)){
            return json($this->errjson(-10001));
        }

        //获取订单信息
        $OrderModel = new OrderModel();
        $orderinfo = $OrderModel->getScanOrderInfo($uid,$shopid,$deskid);
        if(!empty($orderinfo)){
            return json(self::sucjson(array("orderid"=>$orderinfo['orderid'])));
        }
        return json(self::sucjson());
    }
}
