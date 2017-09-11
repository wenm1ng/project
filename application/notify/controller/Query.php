<?php
/**
 * 充值退款订单反查处理(给轮询服务调用)
 * User: Administrator
 * Date: 17-5-16
 * Time: 下午8:31
 */

namespace app\notify\controller;

use third\Alipay;
use app\data\model\AccountModel as Account;
use base\Base;
use app\data\controller\User;


class Query extends Base
{
    /**
     * 充值订单反查处理
     */
    public function recharge()
    {
        $orderid = intval(input('orderid'));

        //检查该笔充值订单是否存在以及当前状态
        $Account = new Account();
        $orderinfo = $Account->getRechargeOrderInfo($orderid);
        if(empty($orderinfo)){
            return json(self::erres("充值订单不存在"));
        }
        if($orderinfo['status'] != 0){
            return json(self::erres("该充值订单已处理"));
        }

        $paychannel = $orderinfo['channel'];
        $User = new User();
        if(!in_array($paychannel,$User->allow_paychannel)){
            return json(self::erres("该充值渠道暂不支持"));
        }

        if($paychannel == config("paychannel.alipay")){
            //支付宝充值结束，开始订单状态反查
            $Alipay = new Alipay();
            $result = $Alipay->handlerRechargeOrder($orderid,$orderinfo);
            if($result['code'] > 0 && $result['status'] == 'success'){
                //充值成功，且充值订单处理成功
                return json(self::sucjson());
            }else if($result['code'] <= 0 && $result['status'] == 'success'){
                //充值成功，且充值订单处理失败
                return json(self::erres("充值成功，但充值订单处理失败"));
            }else if($result['code'] > 0 && $result['status'] == 'fail'){
                //充值失败，且充值订单处理成功
                return json(self::sucjson());
            }else if($result['code'] > 0 && $result['status'] == 'fail'){
                //充值失败，且充值订单处理失败
                return json(self::erres("充值失败，且充值订单处理失败"));
            }else{
                //充值状态未决
                return json(self::erres("充值状态未决"));
            }
        }
        return json(self::errjson());
    }

    /**
     * 提款订单反查处理
     */
    public function draw()
    {
        $orderid = intval(input('orderid'));

        //检查该笔提款订单是否存在以及当前状态
        $Account = new Account();
        $orderinfo = $Account->getDrawOrderInfo($orderid);
        if(empty($orderinfo)){
            return json(self::erres("提款订单不存在"));
        }
        if($orderinfo['status'] != 0){
            return json(self::erres("该提款订单已处理"));
        }

        $paychannel = $orderinfo['channel'];
        $User = new User();
        if(!in_array($paychannel,$User->allow_drawchannel)){
            return json(self::erres("该提款渠道暂不支持"));
        }

        if($paychannel == config("paychannel.alipay")){
            //支付宝提款结束，开始订单状态反查
            $Alipay = new Alipay();
            $result = $Alipay->handlerRefundOrder($orderid,$orderinfo);
            if($result['code'] > 0 && $result['status'] == 'success'){
                //提款成功，且提款订单处理成功
                return json(self::sucjson());
            }else if($result['code'] <= 0 && $result['status'] == 'success'){
                //提款成功，且提款订单处理失败
                return json(self::erres("提款成功，但提款订单处理失败"));
            }else if($result['code'] > 0 && $result['status'] == 'fail'){
                //提款失败，且提款订单处理成功
                return json(self::sucjson());
            }else if($result['code'] > 0 && $result['status'] == 'fail'){
                //提款失败，且提款订单处理失败
                return json(self::erres("提款失败，且提款订单处理失败"));
            }else{
                //提款状态未决
                return json(self::erres("提款状态未决"));
            }
        }
        return json(self::errjson());
    }
}