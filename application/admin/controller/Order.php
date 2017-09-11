<?php
namespace app\admin\controller;

use app\data\model\AccountModel;
use base\Base;
use \app\admin\model\OrderModel;
use \app\admin\model\DishesModel;
use \app\admin\model\TastesModel;
use \app\data\controller\User as FrontUser;
use third\Alipay;
use think\Session;
use think\Db;

use PHPExcel_IOFactory;
use PHPExcel;

class Order extends Base
{
    /**
     * 后台查询订单
     * 
     */
    public function getOrderlist(){
        $orders = new OrderModel();
        $orderslist = $orders->getAllOrderList();
        session::set('uu','name=&dataDay=&dataDay1=&timeDay=&timeDay1=');
        return view('orders/index',['orders'=>$orderslist,'id'=>1]);
    }
    
    /*
     *  查询菜肴详情
     **/
    public function getDetails()
    {
        $oid = $_POST['oid'];
        $orders = new OrderModel();
        $orderslist = $orders->getAllDetails($oid);
        echo json_encode($orderslist);
    }

    /*
     *  模糊搜索菜肴
     **/
    public function getOneOrder()
    {
        $name = $_POST['name'];
        $dataDay = $_POST['dataDay'];
        $dataDay1 = $_POST['dataDay1'];
        $timeDay = $_POST['timeDay'];
        $timeDay1 = $_POST['timeDay1'];
        $orders = new OrderModel();
        $orderslist = $orders->findOne($name,$dataDay,$dataDay1,$timeDay,$timeDay1);
        // $orderslist[0]['id'] = 1;
        if(!empty($orderslist[0]['f_oid'])){
            echo json_encode($orderslist);
        }else{
            echo 0;
        }
     
    }

    /*
     *  预售菜肴统计
     **/
    public function getFood()
    {
        $orders = new OrderModel();
        $foodlist = $orders->getAllAdvance();
        return view('orders/food',['foodlist'=>$foodlist]);
    }

    /**
     *  预售菜肴搜索店铺
     */
    public function searchAdvance()
    {
        $dineshop = $_POST['dineshop'];
        $foodname = $_POST['foodname'];
        $orders = new OrderModel();
        $foodlist = $orders->getSearchAdvance($dineshop,$foodname);
        if(!empty($foodlist)){
            echo json_encode($foodlist);
        }else{
            echo 0;
        }
    }


    /*
     *  时间预售菜肴统计
     **/
    public function getTime()
    {
        return view('orders/time');
    }

    /*
     *  时间段预售菜品
     **/
    public function getSearchTime()
    {
        $dataDay = $_POST['dataDay'];
        $dataDay1 = $_POST['dataDay1'];
        $timeDay = $_POST['timeDay'];
        $timeDay1 = $_POST['timeDay1'];
        $foodname = $_POST['foodname'];
        $dineshop = $_POST['dineshop'];
        $order = new OrderModel();
        $foodlist = $order->getSearchTimer($dataDay,$dataDay1,$timeDay,$timeDay1,$foodname,$dineshop);
        echo json_encode($foodlist);
    }

    /*
     *  外卖订单
     **/
    public function getTakeOut()
    {
        $order = new OrderModel();
        $list = $order->getAllTakeOut();
        // echo "<pre>";
        // var_dump($list);exit;
        return view('orders/takeout',['list'=>$list]);
    }

    public function geturl(){
        // print_r(input('uu'));
        session::set('uu',input('uu'));
        echo session::get('uu');
    }

    /*
    **订单导出
    */
    public function exportexcel(){
        // print_r(input());
        // print_r(session::get('uu'));
        // name=&dataDay=2017-07-18&dataDay1=2017-07-28&timeDay=&timeDay1=
        $width_arr = array(array(30,30,30,30,30,30,30,30,30)); // 设置列宽
        $excel_file = "预售订单报表";
        //name=&dataDay=2017-07-17&dataDay1=2017-07-31&timeDay=&timeDay1=
        // $url = session::get('uu');
        $name = input('name');
        $dataDay = input('dataDay');
        $dataDay1 = input('dataDay1');
        $timeDay = input('timeDay');
        $timeDay1 = input('timeDay1');

        $where = "f_foodname like '%".$name."%'";
        $menu = Db::table('t_food_menu');
        $list = $menu->field('f_oid,f_foodname,f_foodprice,f_foodnum')->where("$where")->select();
        // error_lo('xixi',Db::getlastsql());
        // print_r($list);
        $oid = '';
        foreach($list as $key => $val){
          $oid .= $val['f_oid'].',';
        }
        $order = Db::table('t_orders');
        $oid = rtrim($oid,',');
        $where1 = 'f_type = 2';
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
        
        $list = $order->field('f_oid,f_userid,f_mealsnum,f_deskid,f_ordermoney,f_status,f_endtime,f_startime')->where($where1)->select();
        error_lo('new2',Db::getlastsql());
         // print_r($list);exit;
        foreach($list as $key => $val){
           $list[$key]['f_eattime'] = $val['f_startime']." - ".date("H:i:s",strtotime($val['f_endtime']));
           unset($list[$key]['f_endtime']);
           unset($list[$key]['f_startime']);
           $list[$key]['f_status'] = $status[$val['f_status']];

           $menulist = Db::table('t_food_menu')->where("f_oid = {$val['f_oid']}")->select();
           // error_lo('haha',Db::getlastsql());
           $str = '';
            foreach ($menulist as $k => $v) {
                
                $menulist[$k]['f_foodprice'] = $v['f_foodprice']*$v['f_foodnum'];
                if($val['f_oid'] == $v['f_oid']){
                    $str.= $v['f_foodname'].' ×'.$v['f_foodnum'].' ￥'.$v['f_foodprice'].' / ';
                }
            }
            $list[$key]['menu'] = $str;
        }
        
        // echo Db::getlastsql();
        // print_r($list);exit;
            $ceils = $this->excelDataFormat($list);
            $excel_content[0] = array(
                'sheet_name' => '预售报表',
                'sheet_title' => array('订单号','用户账号','就餐人数','桌号','价格','状态','就餐时间段','菜肴详细信息'),
                'ceils' => $ceils['excel_ceils'],
                'freezePane' => 'B2',
                'headerColor' => getCssClass("header"),
                'headerColumnCssClass' => array(
                    'id' => getCssClass('blue'),
                    'Status_Description' => getCssClass('grey'),
                ),
                'oddCssClass' => getCssClass("odd"),
                'evenCssClass' => getCssClass("even")
            );

        
        exportExcel($width_arr,$excel_content, $excel_file);
    }

    public function newOrder(){
        $count = Db::table('t_orders')->count();
        $result = array();
        $result['count'] = $count;
        $result['code'] = 0;
        $result['msg']= 'success';
        echo json_encode($result);
    }

    //用户消费报表
    public function report(){
        Db::table('t_orders')->where("f_status in (4,100)")->select();
    }
    // /**
    //  * 订单处理
    //  */
    // public function processOrder(){
    //     $info = array();
    //     $list = array();
    //     $uid = input('uid');
    //     $userid = input('userid');
    //     $orderid = input('orderid');
    //     $status = intval(input('status',-1));
    //     $distripid = input('distripid'); //配送员ID;
    //     if(!$this->checkAdminLogin()){
    //         return json($this->errjson(-10001));
    //     }
    //     $OrderModel = new OrderModel();
    //     $orderinfo =$OrderModel->getOrderinfo($userid, $orderid);
    //     if(empty($orderinfo)){
    //         return json($this->erres("订单信息不存在"));
    //     }
    //     $order_status = $orderinfo['status'];
    //     $order_type = $orderinfo['ordertype'];
    //     $order_startime = $orderinfo['startime'];
    //     $order_endtime = $orderinfo['endtime'];
    //     if($order_type==1 && !in_array($status,array(2,3))){
    //         return json(self::erres("外卖订单状态错误"));
    //     }elseif($order_type==2 && !in_array($status,array(2,5,6))){
    //         return json(self::erres("堂食订单状态错误"));
    //     }
    //     //判断当前传入订单状态与数据库订单状态是否一致
    //     if($status != $order_status){
    //         return json($this->erres("订单状态不一致，请刷新页面重试！"));
    //     }
    //     $data = array();
    //     switch($status){
    //         case 2: //当前已付款，需设置成配送中/就餐中
    //             if($order_type == 1){
    //                 if(empty($distripid)) return json($this->erres("缺少参数"));
    //                 $data['status'] = 3;
    //                 $data['distripid'] = $distripid;
    //             }else{
    //                 if(strtotime($order_startime) < time()){
    //                     return json(self::erres("当前堂食订单尚未到就餐开始时间!"));
    //                 }
    //                 $data['status'] = 5;
    //             }
    //             break;
    //         case 3: //当前配送中，需设置成配送完成
    //             $data['status'] = 100;
    //             break;
    //         case 5: //当前用餐中，需设置成用餐结束
    //             $data['status'] = 100;
    //             if(strtotime($order_endtime) < time()){
    //                 return json(self::erres("当前堂食订单尚未到就餐截止时间!"));
    //             }
    //             break;
    //         case 6: //当前申请打包中，需设置成打包完成
    //             $data['status'] = 90;
    //             break;
    //         default:
    //             return json($this->erres("参数错误"));
    //     }
    //     if($order_status == $data['status']){
    //         return json(self::sucjson());
    //     }
    //     $info = $OrderModel->processOrder($orderid, $data);
    //     if(!$info) return json($this->erres("更新失败"));
    //     return json($this->sucjson($info));
    // }

    // /**
    //  * 审核退款订单
    //  */
    // public function checkupCancelOrder(){
    //     $userid = input('userid');
    //     $orderid = input('orderid');
    //     $checkupstatus = input('checkupstatus',1);    //审核结果 0-不通过，1-通过
    //     if(!$this->checkAdminLogin()){
    //         return json($this->errjson(-10001));
    //     }
    //     $OrderModel = new OrderModel();
    //     $orderinfo =$OrderModel->getOrderinfo($userid, $orderid);
    //     if(empty($orderinfo)){
    //         return json($this->erres("订单信息不存在"));
    //     }
    //     $order_status = $orderinfo['status'];
    //     $order_paytype = $orderinfo['paytype'];
    //     $order_paymoney = $orderinfo['paymoney'];
    //     $order_type = $orderinfo['ordertype'];
    //     $order_deskid = $orderinfo['deskid'];
    //     if($order_status != $OrderModel->status_waiting_checkup_refund){
    //         return json($this->erres("订单非待审核退款状态"));
    //     }
    //     if($checkupstatus == 1){
    //         //审核通过
    //         if(!$OrderModel->updateTradeOrderInfo($userid,$orderid,$OrderModel->status_checkup_suc_refund)){
    //             return json(self::erres("退款订单审核失败"));
    //         }
    //         if($order_type == 2){
    //             $OrderModel->cancelTradeOrderDeskOrdernum($order_deskid);
    //         }

    //         if($order_paytype == 0){
    //             //余额支付，撤单返款即完成
    //             //撤单返款
    //             $Account = new AccountModel();
    //             $tradetype = 1004;
    //             $deposit = $Account->deposit($userid,$order_paymoney,$tradetype,$orderid);
    //             if(!$deposit){
    //                 return json(self::erres("撤单返款失败"));
    //             }
    //             if($OrderModel->updateTradeOrderInfo($userid,$orderid,$OrderModel->status_refund_suc)){
    //                 return json(self::sucjson());
    //             }
    //         }else{
    //             //支付宝支付or微信支付
    //             //检查订单对应充值信息
    //             $AccountModel = new AccountModel();
    //             $rechargeinfo = $AccountModel->getTradeOrderRechargeInfo($userid,$orderid);
    //             if(empty($rechargeinfo)){
    //                 return json(self::erres("查不到该交易订单对应充值信息"));
    //             }
    //             $paystatus = $rechargeinfo['status'];
    //             $paymoney = $rechargeinfo['paymoney'];
    //             if($paystatus != $AccountModel->paysuc){
    //                 return json(self::erres("该交易订单未充值成功"));
    //             }
    //             if($order_paymoney > $paymoney){
    //                 return json(self::erres("退款金额不能超过该订单充值金额"));
    //             }
    //             $payorderid = $rechargeinfo['orderid'];
    //             $paybankorderid = $rechargeinfo['bankorderid'];
    //             $paychannel = $rechargeinfo['channel'];

    //             //检查当前充值渠道是否可退
    //             $FrontUser = new FrontUser();
    //             if(!in_array($paychannel,$FrontUser->allow_drawchannel)){
    //                 return json(self::erres("该支付订单当前不支持原路退回"));
    //             }

    //             //撤单返款
    //             $Account = new AccountModel();
    //             $tradetype = 1004;
    //             $deposit = $Account->deposit($userid,$order_paymoney,$tradetype,$orderid);
    //             if(!$deposit){
    //                 return json(self::erres("撤单返款失败"));
    //             }

    //             //冻结
    //             $tradetype = 2003;
    //             $tradenote = "订单退款冻结";
    //             $freeze = $AccountModel->freeze($userid,$order_paymoney,$tradetype,$tradenote);
    //             if(!$freeze){
    //                 return json(self::erres("订单退款冻结失败"));
    //             }

    //             $refundid = $AccountModel->addDrawOrderInfo($userid,$order_paymoney,config("drawtype.order"),$paychannel,$orderid,$payorderid,$paybankorderid);
    //             if($refundid === false){
    //                 return json(self::erres("创建退款订单失败"));
    //             }

    //             $describle = "订单退款";
    //             if($paychannel == config("drawchannel.alipay")){
    //                 $Alipay = new Alipay();
    //                 $ret = $Alipay->toRefund($refundid,$order_paymoney,$rechargeinfo,$describle);
    //                 if($ret['code'] > 0){
    //                     //将订单状态更新为退款中
    //                     if($OrderModel->updateTradeOrderInfo($userid,$orderid,$OrderModel->status_waiting_refund)){
    //                         return json(self::sucjson());
    //                     }
    //                 }else{
    //                     return json(self::erres("退款请求提交第三方失败"));
    //                 }
    //             }
    //         }
    //     }else{
    //         if($OrderModel->updateTradeOrderInfo($userid,$orderid,$OrderModel->status_checkup_fail_refund)){
    //             return json(self::sucjson());
    //         }
    //     }
    //     return json(self::errjson());
    // }
    
    // /**
    // * 报表下载
    // */
    // public function getOrderDownlist(){
    //     $info = array();
    //     $list = array();
    //     $shopid = input('shopid');
    //     $ordertype = input('ordertype', 1);
    //     $startime = input('startime');
    //     $endtime = input('endtime');
    //     $checkupstatus = input('checkupstatus',1);    //审核结果 0-不通过，1-通过
    //     if(!$this->checkAdminLogin()){
    //         return json($this->errjson(-10001));
    //     }
    //     $exceltitle = "订单报表";
    //     $res = array();
    //     $OrderModel = new OrderModel();
    //     if($ordertype == 1){
    //         $exceltitle = "外卖订单报表";
    //         $celtitle = array('序号','订单号','店铺名称','菜肴名称','订单时间','价格(元)','配送信息','状态');
    //         $res = $OrderModel->getTakeoutlist($startime, $endtime, $shopid, '', 1, 1000);
    //     }else{
    //         $exceltitle = "预售订单报表";
    //         $celtitle = array('序号','订单号','店铺名称','用户账户','就餐信息','桌号','菜肴名称','价格(元)','状态');
    //         $res = $OrderModel->getEatinlist($startime, $endtime, $shopid, '', 1, 1000);
    //     }
    //     if($res['orderlist']) {
    //         $list = $res['orderlist'];
    //         $orderlist = array();
    //         $tastid = array();
    //         $dishid = array();
    //         foreach($list as $key=>$val){
    //             $orderdetail = $val['orderdetail'];
    //             preg_match_all('/(\d+)\|(\d+)\@(\d+)/i', $orderdetail, $match);
    //             if($match){
    //                 $orderlist = array_combine($match[1], $match[0]);
    //                 $dishid = array_merge($dishid, $match[1]);
    //                 $tastid = array_merge($tastid, $match[2]);
    //             }
    //             $list[$key]['orderlist'] = $orderlist;
    //             if(isset($list[$key]['deliveryname']) && $list[$key]['deliveryname'] == null){
    //                 $list[$key]['deliveryname'] = '';
    //             }
    //             if(isset($list[$key]['deliverymobie']) && $list[$key]['deliverymobie'] == null){
    //                 $list[$key]['deliverymobie'] = '';
    //             }
    //         }
    //         $DishesModel = new DishesModel();
    //         $dishlist = $DishesModel->getDishesList(implode(',', array_unique($dishid)));
    //         $dishinfo = array();
    //         if($dishlist){
    //             foreach($dishlist as $key => $val){
    //                 $dishinfo[$val['id']] = $val;
    //             }
    //         }
    //         $TastesModel = new TastesModel();
    //         $tasteslist = $TastesModel->getTastesList(implode(',', array_unique($tastid)));
    //         $tastesinfo = array();
    //         if($tasteslist){
    //             foreach($tasteslist as $key => $val){
    //                 $tastesinfo[$val['id']] = $val['tastes'];
    //             }
    //         }
    //         foreach($list as $key => $val){
    //             $orderlist = array();
    //             foreach($val['orderlist'] as $k => $v){
    //                 preg_match('/(\d+)\|(\d+)\@(\d+)/i', $v, $match);
    //                 $tastesid = $match[2];
    //                 $num = $match[3];
    //                 $orderinfo = isset($dishinfo[$k])?$dishinfo[$k]:array();
    //                 $orderinfo['num'] = $num;
    //                 $orderinfo['tastes'] = isset($tastesinfo[$tastesid])?$tastesinfo[$tastesid]:'';
    //                 array_push($orderlist, $orderinfo);
    //             }
    //             $list[$key]['orderlist'] = $orderlist;
    //         }
    //     }
    //     //获取下载数据
    //     $status_arr = array(
    //         "0"=>"初始",
    //         "1"=>"未付款",
    //         "2"=>"已付款",
    //         "3"=>"配送中",
    //         "4"=>"配送完成",
    //         "5"=>"就餐中",
    //         "6"=>"申请打包",
    //         "90"=>"已打包",
    //         "100"=>"订单完成",
    //         "-100"=>"逾期",
    //         "-200"=>"退款中",
    //         "-300"=>"退款完成",
    //         "-400"=>"逾期未就餐",
    //         "-110"=>"退款待审核",
    //         "-120"=>"退款审核通过",
    //         "-130"=>"退款审核不通过",
    //         "-200"=>"退款中",
    //         "-300"=>"退款完成",
    //         "-900"=>"逾期关闭",
    //         "-1000"=>"已关闭"
    //     );
        
    //     $downlist = array();
    //     array_push($downlist, $celtitle);
    //     if(count($list) > 0){
    //         $i = 1;
    //         foreach($list as $key => $val){
    //             $status = isset($status_arr[$val['status']])?$status_arr[$val['status']]:'';
    //             $dishinfo = "";
    //             if(isset($val['orderlist']) && count($val['orderlist']) > 0){
    //                 foreach($val['orderlist'] as $k=>$v){
    //                     if(isset($v['dishesname'])){
    //                         $tastes = '';
    //                         if($v['tastes']){
    //                             $tastes = '【'.$v['tastes'].'】';
    //                         }
    //                         $dishinfo .= $v['dishesname'].$tastes." x ".$v['num']."; ".chr(10);
    //                     } 
    //                 }
    //             }
    //             if($ordertype == 1){
    //                 $deliveryinfo = "";
    //                 if($val['recipientname'] || $val['recipientmobile'] || $val['deliveryaddress']){
    //                     $deliveryinfo = $val['recipientname'].'|'.$val['recipientmobile'].'|'.$val['deliveryaddress'];
    //                 }
                    
    //                 $celdata = array($i++, $val['orderid'], $val['shopname'], $dishinfo, $val['allmoney'], $deliveryinfo, $status);
    //             }else{
    //                 $celdata = array();
    //                 $eatinfo = "就餐人数：".$val['mealsnum']."; ".chr(10)."就餐时间：".$val['startime']." - ".$val['endtime'];
    //                 $celdata = array($i++, $val['orderid'], $val['shopname'], $val['usermobile'], $eatinfo, $val['deskid'], $dishinfo, $val['allmoney'], $status);
    //             }
    //             array_push($downlist, $celdata);
    //         }
    //     }
      
    //     $path = dirname(__FILE__); //找到当前脚本所在路径
    //     $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
    //     $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
    //     $PHPSheet->setTitle($exceltitle); //给当前活动sheet设置名称
    //     $PHPSheet->fromArray($downlist);
    //     //$PHPSheet->getAlignment()->setWrapText(true);
    //     $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，'Excel2007'表示生成2007版本的xlsx，'Excel5'表示生成2003版本Excel文件
    //     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
    //     //header('Content-Type:application/vnd.ms-excel');
    //     //告诉浏览器将要输出Excel03版本文件
    //     header('Content-Disposition: attachment;filename="'.$exceltitle.'.xlsx"');//告诉浏览器输出浏览器名称
    //     header('Cache-Control: max-age=0');//禁止缓存
    //     $PHPWriter->save("php://output");
        
    //     return json($this->sucjson($list[1], $downlist));
    // }
}
