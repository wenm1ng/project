<?php
namespace app\data\controller;

use base\Baseapi;
use \app\data\model\UserModel;
use \app\data\model\AccountModel;
use app\data\model\OrderModel;
use think\Log;
use third\Sms;
use third\Alipay;

class User extends Baseapi
{
    public $allow_paytype = array();        //目前支持的充值类型
    public $allow_paychannel = array();     //目前支持的充值渠道
    public $allow_drawtype = array();       //目前支持的提款类型
    public $allow_drawchannel =array();     //目前支持的提款渠道

    /**
     * 控制器初始化
     */
    public function __construct(){
        parent::__construct();

        //初始化目前支持的充值类型
        array_push($this->allow_paytype,config("paytype.balance"));
        //array_push($this->allow_paytype,config("paytype.deposit"));
        array_push($this->allow_paytype,config("paytype.order"));

        //初始化目前支持的充值渠道
        array_push($this->allow_paychannel,config("paychannel.alipay"));
        //array_push($this->allow_paychannel,config("paychannel.wechat"));

        //初始化目前支持的提款类型
        //array_push($this->allow_drawtype,config("drawtype.deposit"));
        array_push($this->allow_drawtype,config("drawtype.order"));

        //初始化目前支持的提款渠道
        array_push($this->allow_drawchannel,config("drawchannel.alipay"));
        //array_push($this->allow_drawchannel,config("drawchannel.wechat"));
    }

    /**
     * 发生短信验证码接口
     * @return \think\response\Json
     */
    public function sendSms()
    {
        $mobile = input('mobile');
        //检查手机号码格式
        if (!check_mobile($mobile)) {
            return json(self::erres("手机号码格式错误"));
        }

        $UserModel = new UserModel();

        //检查该手机号是否已注册，如无则注册
        $uid = $UserModel->checkMobile($mobile);
        if ($uid === false) {
            $uid = $UserModel->addUser($mobile);
            if ($uid === false) {
                return json(self::erres("注册用户失败"));
            }
        }

        //检查记录短信发送日志
        if (!$UserModel->checkSmslog($uid, $mobile)) {
            return json(self::erres("短信发送太频繁了"));
        }

        //发送短信验证码，并更新短信发送日志
        $Sms = new Sms();
        $ret = $Sms->sendsms($mobile);
        if ($ret['code'] > 0) {
            if (!$UserModel->updateSmslog($uid, $mobile)) {
                return json(self::erres("更新短信发送日志失败"));
            }
        }
        return json($ret);
    }

    /**
     * 手机号码 + 短信验证码 登录接口
     * @return \think\response\Json
     */
    public function login()
    {
        $mobile = input('mobile');
        $vcode = input('vcode');
        $deviceid = trim(input('deviceid'));
        $platform = input('platform');
        $ip = input('ip');
        $remark = input('remark');

        //设备号不能为空
        // if (empty($deviceid)) {
        //     return json(self::erres("设备号不能为空"));
        // }

        //检查手机号有无注册
        $UserModel = new UserModel();
        $uid = $UserModel->checkMobile($mobile);
        if($uid === false){
            return json(self::erres("用户不存在"));
        }

        //检查短信验证码是否正确
        $Sms = new Sms();
        $ret = $Sms->checksms($mobile, $vcode);
        if ($ret['code'] <= 0) {
            return json($ret);
        }

        //写登录信息
        $ck = 'ck_' . strtoupper(base64_encode(md5($uid.$mobile.time())));
        $platform = intval($platform);
        $ret_login = $UserModel->addUserLogin($ck, $uid, $deviceid, $platform, $ip, $remark);
        if ($ret_login === false) {
            return json(self::erres("写登录信息失败"));
        }
        $resinfo = array(
            'ck' => $ret_login['ck'],
            'uid' => $ret_login['uid'],
        );
        return json(self::sucres($resinfo));
    }

    /**
     * 退出登录
     * @return \think\response\Json
     */
    public function logout()
    {
        $ck = input('ck');
        $uid = input('uid');
        if(!self::checkLogin($uid,$ck)){
            return json($this->errjson(-10001));
        }
        $UserModel = new UserModel();
        if ($UserModel->setCkExpired($ck)) {
            return json(self::sucres());
        } else {
            return json(self::erres("退出登录失败"));
        }
    }

    /**
     * 获取地址列表
     * @return \think\response\Json
     */
    public function getAddressList()
    {
        $uid = input('uid');

        //判断用户登录
        if($this->checkLogin($uid) === false) return json($this->errjson(-10001));
        
        $UserModel = new UserModel();
        //检查该手机号是否已注册，如无则注册
        $res = $UserModel->getAddressList($uid);
        if(empty($res)) {
            return json(self::sucjson());
        }
        return json(self::sucres(array("num"=>count($res)), $res));
    }

    /**
     * 获取地址信息
     * @return \think\response\Json
     */
    public function getAddressInfo()
    {
        $addressid = input('addressid');
        if(empty($addressid)) return json($this->errjson(-20002));

        //判断用户登录
        if($this->checkLogin() === false) return json($this->errjson(-10001));
        
        $UserModel = new UserModel();
        //检查该手机号是否已注册，如无则注册
        $info = $UserModel->getAddressInfo($addressid);
        if($info) {
            return json($this->sucres($info));
        }else{
            return json($this->erres('获取用户地址信息失败'));
        }
    }
    
    /**
     * 删除地址
     * @return \think\response\Json
     */
    public function delAddress()
    {
        $addressid = input('addressid');
        if(empty($addressid)) return json($this->erres('参数错误'));

        //判断用户登录
        if($this->checkLogin() === false) return json($this->errjson(-10001));
        
        $UserModel = new UserModel();
        //检查该手机号是否已注册，如无则注册
        $info = $UserModel->delAddress($addressid);
        if($info) {
            return json($this->sucres());
        }else{
            return json($this->erres('删除地址失败'));
        }
    }

    /**
     * 设置默认地址
     * @return \think\response\Json
     */
    public function setDefAddress()
    {
        $uid = input('uid');
        if(empty($uid)) return json($this->erres('用户id为空'));
        $addressid = input('addressid');
        if(empty($addressid)) return json($this->erres('参数错误'));

        //判断用户登录
        if($this->checkLogin() === false) return json($this->errjson(-10001));
        
        $UserModel = new UserModel();
        //检查该手机号是否已注册，如无则注册
        $info = $UserModel->setDefAddress($uid, $addressid);
        if($info) {
            return json($this->sucres());
        }else{
            return json($this->erres('设置默认地址失败'));
        }
    }

    /**
     * 新增地址
     * @return \think\response\Json
     */
    public function addAddress()
    {
        $uid = input('uid');
        if(empty($uid)) return json($this->erres('用户id为空'));
        $province = input('province');
        if(empty($province)) return json($this->erres('请传入省份地址'));
        $city = input('city');
        if(empty($city)) return json($this->erres('请传入省份地址'));
        $address = input('address');
        if(empty($address)) return json($this->erres('请传入详细地址'));
        $name = input('name');
        if(empty($name)) return json($this->erres('请传入收件人'));
        $mobile = input('mobile');
        if(empty($mobile)) return json($this->erres('请传入用户手机号'));
        $sex = input('male',1);
    
        //判断用户登录
        if($this->checkLogin() === false) return json($this->errjson(-10001));
        
        $UserModel = new UserModel();
        //检查该手机号是否已注册，如无则注册
        $addressid = $UserModel->checkAddress($uid, $province, $city, $address);
        if ($addressid === false) {
            $addressid = $UserModel->addAddress($uid, $province, $city, $address, $name, $mobile, $sex);
            if ($addressid === false) {
                return json($this->erres('新增地址失败'));
            }else{
                $UserModel->setDefAddress($uid, $addressid);
                return json($this->sucres(array("addressid" => $addressid)));
            }
        }else{
            return json($this->sucres(array("addressid" => $addressid)));
        }
    }

    /**
     * 修改地址
     * @return \think\response\Json
     */
    public function modAddress()
    {
        $uid = input('uid');
        if(empty($uid)) return json($this->erres('用户id为空'));
        $addressid = input('addressid');
        if(empty($addressid)) return json($this->erres('参数错误'));
        $province = input('province');
        $city = input('city');
        $address = input('address');
        $mobile = input('mobile');
        $name = input('name');
        $sex = input('male');
        if(empty($province)&&empty($city)&&empty($address)&&empty($name)&&empty($mobile)&&empty($sex)){
            return json($this->erres('请传入要修改的值'));
        }
        
        //判断用户登录
        if($this->checkLogin() === false) return json($this->errjson(-10001));
        
        $params = array(
            "province" => $province,
            "city" => $city,
            "address" => $address,
            "name" => $name,
            "mobile" => $mobile,
            "sex" => $sex,
        );
        $UserModel = new UserModel();
        //检查该手机号是否已注册，如无则注册
        $res = $UserModel->updateAddress($addressid, $params);
        if($res) {
            return json($this->sucres());
        }else{
            return json($this->erres('更新地址失败'));
        }
    }

    /**
     * 用户提款接口
     * @return \think\response\Json
     */
    public function draw(){
        //获取参数
        $ck = input('ck');
        $uid = input('uid');
        $drawtype = intval(input('drawtype',0));
        $drawmoney = floatval(input('drawmoney',0));
        $describle = input('describle');
        $suborder = intval(input('suborder',0));

        //检查用户是否登录
        if(!self::checkLogin($uid,$ck)){
            return json($this->errjson(-10001));
        }

        //校验参数
        if(!in_array($drawtype,$this->allow_drawtype)){
            return json(self::erres("提款类型错误"));
        }
        if($drawmoney <= 0){
            return json(self::erres("提款金额不能小于0"));
        }

        //获取用户信息
        $UserModel = new UserModel();
        $userinfo = $UserModel->getUserInfoByUid($uid);
        $usermoney = $userinfo['usermoney'];
        $depositmoney = $userinfo['depositmoney'];
        $freezemoney = $userinfo['freezemoney'];

        //押金退款(清户)
        if($drawtype == config("drawtype.deposit")){
            $tradetype = 2001;
            $tradenote = "押金退款冻结";
            if(!$UserModel->checkUserStatus($uid,'draw')){
                return json(self::erres("用户状态异常,当前不能退押金"));
            }
            if($usermoney > 0){
                return json(self::erres("您账户还有余额未消费完"));
            }
            if($freezemoney > 0){
                return json(self::erres("您还有未完成交易"));
            }
            if($depositmoney < $drawmoney){
                return json(self::erres("押金余额不足"));
            }
            //获取该用户最近一笔押金的充值信息,核对充值金额是否与押金退款金额一致
            $AccountModel = new AccountModel();
            $rechargeinfo = $AccountModel->getUserLatestDepositInfo($uid);
            if(empty($rechargeinfo)){
                return json(self::erres("查无用户押金充值信息,无法退款"));
            }
            if($drawmoney != $rechargeinfo['paymoney']){
                Log::record("退款金额[".$drawmoney."]与押金充值金额[".$rechargeinfo['paymoney']."]不一致,无法退款","error");
                return json(self::erres("退款金额与押金充值金额不一致,无法退款"));
            }

        }else if($drawtype == config("drawtype.order")){
            $tradetype = 2003;
            $tradenote = "订单退款冻结";
            if($suborder <= 0){
                return json(self::erres("订单退款子订单号不能为空"));
            }
            //检查该笔订单状态及查询对应充值订单信息
            $OrderModel = new OrderModel();
            $trade_orderinfo = $OrderModel->getTradeOrderInfo($uid,$suborder);
            if(empty($trade_orderinfo)){
                return json(self::erres("待退款交易订单不存在"));
            }
            $order_status = $trade_orderinfo['status'];
            if($order_status != $OrderModel->status_checkup_suc_refund){
                return json(self::erres("该交易订单状态非未退款审核通过状态"));
            }
            $AccountModel = new AccountModel();
            $rechargeinfo = $AccountModel->getTradeOrderRechargeInfo($uid,$suborder);
            if(empty($rechargeinfo)){
                return json(self::erres("查不到该交易订单对应充值信息"));
            }
            $paystatus = $rechargeinfo['status'];
            $paymoney = $rechargeinfo['paymoney'];
            if($paystatus != $AccountModel->paysuc){
                return json(self::erres("该交易订单未充值成功"));
            }
            if($drawmoney > $paymoney){
                return json(self::erres("退款金额不能超过该订单充值金额"));
            }

        }else if($drawtype == config("drawtype.balance") && $usermoney < $drawmoney){
            return json(self::erres("账户余额不足"));
        }else{
            return json(self::errjson());
        }

        //冻结
        $AccountModel = new AccountModel();
        $freeze = $AccountModel->freeze($uid,$drawmoney,$tradetype,$tradenote);
        if(!$freeze){
            return json(self::erres("用户提款冻结失败"));
        }

        $payorderid = $rechargeinfo['orderid'];
        $paybankorderid = $rechargeinfo['bankorderid'];
        $drawchannel = $rechargeinfo['channel'];
        $draworderid = $AccountModel->addDrawOrderInfo($uid,$drawmoney,$drawtype,$drawchannel,$suborder,$payorderid,$paybankorderid);
        if($draworderid === false){
            return json(self::erres("创建提款订单失败"));
        }

        if($drawchannel == config("drawchannel.alipay")){
            $Alipay = new Alipay();
            $ret = $Alipay->toRefund($draworderid,$drawmoney,$rechargeinfo,$describle);
            if($ret['code'] > 0){
                return json(self::sucjson());
            }else{
                return json(self::errjson());
            }
        }

        return json(self::errjson());
    }

    /**
     * 获取登录用户信息
     */
    public function getUserInfo(){
        //获取参数
        $ck = input('ck');
        $uid = input('uid');

        //检查用户是否登录
        if(!self::checkLogin($uid,$ck)){
            return json($this->errjson(-10001));
        }

        //获取用户信息
        $UserModel = new UserModel();
        $userinfo = $UserModel->getUserInfoByUid($uid);
        if(empty($userinfo)){
            return json(self::erres("用户信息不存在"));
        }

        $resinfo = $userinfo;
        return json(self::sucres($resinfo));
    }


    /**
     * 更新用户信息
     * @return \think\response\Json
     */
    public function updateUserInfo(){
        //获取参数
        $ck = input('ck');
        $uid = input('uid');
        $userinfo = array(
            'nickname' => input('nickname'),
            'mobile' => input('mobile'),
            'realname' => input('realname'),
            'sex' => intval(input('sex',0)),
            'idcard' => input('idcard'),
        );
        //检查参数
        if (!empty($userinfo['mobile']) && !check_mobile($userinfo['mobile'])) {
            return json(self::erres("手机号码格式错误"));
        }
        if (!empty($userinfo['sex']) && !in_array($userinfo['sex'],array(0,1,2))) {
            return json(self::erres("性别类型错误"));
        }
        if (!empty($userinfo['idcard']) && !check_idcode($userinfo['idcard'])) {
            return json(self::erres("身份证号码格式错误"));
        }

        //检查用户是否登录
        if(!self::checkLogin($uid,$ck)){
            return json($this->errjson(-10001));
        }

        $UserModel = new UserModel();
        $ori_userinfo = $UserModel->getUserInfoByUid($uid);

        //用户已通过实名认证时,不允许更新真实姓名和身份证号码
        if((!empty($userinfo['realname']) && $userinfo['realname'] != $ori_userinfo['realname']) || (!empty($userinfo['idcard']) && $userinfo['idcard'] != $ori_userinfo['idcard'])){
            if($ori_userinfo['auth_status'] == 100){
                return json(self::erres("已通过实名认证,不允许修改真实姓名和身份证号码"));
            }
        }

        //检查该手机号是否已注册
        if(!empty($userinfo['mobile']) && $ori_userinfo['mobile'] != $userinfo['mobile']){
            if($UserModel->checkMobile($userinfo['mobile'])){
                return json(self::erres("该手机号码已被注册"));
            }
        }

        //更新
        if($UserModel->updateUserInfo($uid,$userinfo)){
            return json(self::sucres());
        }else{
            return json(self::erres("用户信息更新失败"));
        }
    }

    /**
     * 实名认证
     * @return \think\response\Json
     */
    public function auth(){
        //获取参数
        $ck = input('ck');
        $uid = input('uid');
        $realname = input('realname');
        $idcard = input('idcard');

        //检查用户是否登录
        if(!self::checkLogin($uid,$ck)){
            return json($this->errjson(-10001));
        }

        //进行实名认证
        $auth = true;
        if(!$auth){
            return json(self::erres("实名认证失败"));
        }else{
            $UserModel = new UserModel();
            if(!$UserModel->updateUserInfo($uid,array('auth_status'=>100))){
                return json(self::erres("实名认证成功,更新用户信息失败"));
            }
        }
        return json(self::sucres());
    }

    /**
     * 创建充值订单
     * @return \think\response\Json
     */
    public function createRechargeOrder(){
        //获取参数
        $ck = input('ck');
        $uid = input('uid');
        $paytype = intval(input('paytype',0));
        $paymoney = floatval(input('paymoney',0));
        $paychannel = intval(input('channel',0));
        $subject = input('subject');
        $describle = input('describle');
        $suborder = intval(input('suborder',0));
        $ordertype = intval(input('ordertype',0));

        //检查用户是否登录
        if(!self::checkLogin($uid,$ck)){
            return json($this->errjson(-10001));
        }

        if(!in_array($paytype,$this->allow_paytype)){
            return json(self::erres("充值类型错误"));
        }
        if(!in_array($paychannel,$this->allow_paychannel)){
            return json(self::erres("充值渠道错误"));
        }
        if($paymoney <= 0){
            return json(self::erres("充值金额不能小于0"));
        }
        if(empty($subject)){
            return json(self::erres("商品标题不能为空"));
        }

        if($paytype == config("paytype.order")){
            if($suborder <= 0){
                return json(self::erres("订单充值子订单号不能为空"));
            }else{
                //查询该笔订单信息，检查是否存在，是否已交易成功
                $OrderModel = new OrderModel();
                if($ordertype == 0){
                    $orderinfo = $OrderModel->getTradeOrderInfo($uid,$suborder);
                }else{
                    $orderinfo = $OrderModel->getTradeSubOrderInfo($uid,$suborder);
                }

                if(empty($orderinfo)){
                    return json(self::erres("待支付交易订单信息不存在"));
                }
                $order_status = $orderinfo['status'];
                $order_allmoney = $orderinfo['allmoney'];
                $order_paymoney = $orderinfo['paymoney'];
                if($order_status != $OrderModel->status_waiting_pay){
                    return json(self::erres("该交易订单非待付款状态"));
                }
                if($order_allmoney - $order_paymoney < $paymoney){
                    return json(self::erres("充值金额超过该交易订单未付款金额"));
                }
            }
        }

        //必须实名认证后方可充值
        /*$UserModel = new UserModel();
        if(!$UserModel->checkUserStatus($uid,'charge')){
            return json(self::erres("实名认证后方可充值"));
        }*/

        $AccountModel = new AccountModel();
        $paymoney = number_format($paymoney,2,'.','');
        $orderid = $AccountModel->addRechargeOrderInfo($uid,$paymoney,$paytype,$paychannel,$suborder,$ordertype);
        if($orderid === false){
            return json(self::erres("创建充值订单失败"));
        }

        //返回支付宝APP支付所需参数
        $Alipay = new Alipay();
        $retinfo['orderid'] = $orderid;
        $retinfo['paymoney'] = $paymoney;
        $retinfo['request'] = $Alipay->AlipayTradeAppPayRequest($subject,$describle,$orderid,$paymoney);;

        Log::record(self::sucjson($retinfo),'debug');
        return json(self::sucjson($retinfo));
    }

    /**
     * 充值订单同步通知处理接口
     * @return \think\response\Json
     */
    public function finishRechargeOrder(){
        //获取参数
        $ck = input('ck');
        $uid = input('uid');
        $orderid = intval(input('orderid'));

        //检查用户是否登录
        if(!self::checkLogin($uid,$ck)){
            return json($this->errjson(-10001));
        }

        //检查该笔充值订单是否存在以及当前状态
        $AccountModel = new AccountModel();
        $orderinfo = $AccountModel->getRechargeOrderInfo($orderid);
        if(empty($orderinfo)){
            return json(self::erres("充值订单不存在"));
        }
        if($orderinfo['status'] == $AccountModel->paysuc){
            return json(self::sucjson());
        }elseif($orderinfo['status'] == $AccountModel->payfail){
            return json(self::erres("充值失败,订单已处理"));
        }

        $paychannel = $orderinfo['channel'];
        if(!in_array($paychannel,$this->allow_paychannel)){
            return json(self::erres("该充值渠道暂不支持"));
        }

        if($paychannel == config("paychannel.alipay")){
            //支付宝充值结束，开始订单状态反查
            $Alipay = new Alipay();
            $result = $Alipay->handlerRechargeOrder($orderid,$orderinfo);
            if($result['code'] > 0 && $result['status'] == 'success'){
                //充值成功且充值订单处理成功
                return json(self::sucjson());
            }else{
                return json(self::erres("充值状态未知,请稍后查看订单"));
            }
        }

    }

    /**
     * 获取用户充值记录
     */
    public function getUserRechargeList(){
        //TODO
    }

    /**
     * 获取用户提款记录
     */
    public function getUserDrawList(){
        //TODO
    }
}
