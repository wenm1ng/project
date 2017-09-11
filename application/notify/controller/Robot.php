<?php
/**
 * 脚步自动调用入口
 */

namespace app\notify\controller;

use app\admin\model\OrderModel as AdminOrderModel;
use app\data\model\OrderModel as OrderModel;
use base\Base;


class Robot extends Base
{
    private $order_type_takeout = 1;
    private $order_type_eatin = 2;
    private $limit_num = 100;
    private $final_status_list = array(-1000,-900,-300,90,100);
    private $pending_status_list = array();

    /**
     * 控制器初始化
     */
    public function __construct(){
        parent::__construct();

        $OrderModel = new OrderModel();
        array_push($this->pending_status_list,$OrderModel->status_waiting_pay);
        array_push($this->pending_status_list,$OrderModel->status_pay_suc);
        array_push($this->pending_status_list,$OrderModel->status_overtime_repast);
    }

    /**
     * 订单自动处理
     */
    public function order(){
        //查询待处理订单
        $OrderModel = new OrderModel();
        $AdminOrderModel = new AdminOrderModel();
        $order_list = $AdminOrderModel->getPendingOrderList($this->pending_status_list,$this->limit_num);
        if(empty($order_list)){
            return json(self::sucjson());
        }

        $overtime_closed = config('order.overtime_closed');
        $overtime_repast = config('order.overtime_repast');


        foreach($order_list as $order){
            $order_id = $order['orderid'];
            $order_userid = $order['userid'];
            $order_type = $order['ordertype'];
            $order_status = $order['status'];
            $order_addtime = $order['addtime'];
            $order_startime = $order['startime'];
            $order_endtime = $order['endtime'];
            $order_deskid = $order['deskid'];
            $order_shopid = $order['shopid'];

            //超时未付款关闭
            if($order_status == $OrderModel->status_waiting_pay){
                if(time() > strtotime($order_addtime) + $overtime_closed){
                    //设置订单状态
                    $OrderModel->updateTradeOrderInfo($order_userid,$order_id,$OrderModel->status_final_closed);
                    //堂食订单、则释放桌型
                    if($order_type == $this->order_type_eatin){
                        $AdminOrderModel->releaseDesk($order_shopid,$order_deskid);
                    }
                }
            }

            //堂食订单
            if($order_type == $this->order_type_eatin){
                //逾期未就餐
                if($order_status == $OrderModel->status_pay_suc){
                    if(time() > strtotime($order_startime) + $overtime_repast){
                        //设置订单状态
                        $OrderModel->updateTradeOrderInfo($order_userid,$order_id,$OrderModel->status_overtime_repast);
                    }
                }
                //逾期未就餐 且当天未打包
                if($order_status == $OrderModel->status_overtime_repast){
                    $order_create_date = date('Y-m-d',strtotime($order_addtime));
                    $closed_time = strtotime(date('Y-m-d',strtotime("$order_create_date +1 day")));
                    if(time() > $closed_time){
                        //设置订单状态
                        $OrderModel->updateTradeOrderInfo($order_userid,$order_id,$OrderModel->status_overtime_closed);
                    }
                }
            }
        }
        return json(self::sucjson());
    }
}