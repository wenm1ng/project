<?php
namespace app\api\controller;
use \base\Baseapi;
use think\Db;
use \app\api\model\DineshopModel;
use \app\api\model\DishesModel;

class Shop extends Baseapi
{
    /**
     * 添加店铺
     */
    public function addDineshop(){
        $info = array();
        $list = array();
        //获取添加店铺信息
        $shopid = input('shopid');
        $adduser = input('adduser');
        if(empty($adduser)){
            return json($this->errjson(-20001));
        }
        $shopname = input('shopname');
        if(empty($shopname)){
            return json($this->errjson(-80007));
        }
        $shopdesc = input('shopdesc');
        $shopicon = input('shopicon');
        if(empty($shopicon)){
            return json($this->errjson(-80008));
        }
        if(strstr($shopicon,'upload/') && is_file(ROOT_PATH.$shopicon)){
            $shopiconurl = str_replace("upload","public/static/images", $shopicon);
            try{
                copy(ROOT_PATH.$shopicon, ROOT_PATH.$shopiconurl); //拷贝到新目录
            }catch (\Exception $e) {
                return json($this->errjson("文件传输错误"));
            }
        }else{
            $shopiconurl = $shopicon;
        }
        $cuisineid = input('cuisineid');
        if(empty($cuisineid)){
            return json($this->errjson(-80009));
        }
        $maplon = input('maplon');
        $maplat = input('maplat');
        $sales = input('sales');
        $deliveryfee = input('deliveryfee');
        $minprice = input('minprice');
        $minconsume = intval(input('minconsume',0));
        $servicecharge = intval(input('servicecharge',0));
        $preconsume = input('preconsume');
        $isbooking = input('isbooking');
        $isaway = input('isaway');
        $opentime = input('opentime');
        $shophone = input('shophone');
        if(empty($shophone)){
            return json($this->errjson(-80010));
        }
        $address = input('address');
        if(empty($address)){
            return json($this->errjson(-80011));
        }
        //判断登录
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        if($shopid){
            $res = $DineshopModel->modDineshop($shopid, $shopname, $shopdesc, $shopiconurl, $cuisineid, $maplon, $maplat, $sales, $deliveryfee, $minprice, $preconsume, $isbooking, $isaway, $opentime, $shophone, $address, $minconsume, $servicecharge);
        }else{
            //店铺重复添加判断
            $shopinfo = $DineshopModel->getDineshopInfoByadduser($adduser);
            if($shopinfo) {
                return json($this->erres('您已经添加店铺不能重复添加'));
            }
            $res = $DineshopModel->addDineshop($shopname, $shopdesc, $shopiconurl, $cuisineid, $maplon, $maplat, $sales, $deliveryfee, $minprice, $preconsume, $isbooking, $isaway, $opentime, $shophone, $address, $adduser, $minconsume, $servicecharge);
        }
        if($res){
            return json($this->sucjson($info, $list));
        }else{
            return json($this->errjson(-1));
        }
    }
    /**
     * 修改店铺状态
     */
    public function modDineshopStatus(){
        //获取添加店铺信息
        $shopid = input('shopid');
        $key = input('key');
        if(empty($shopid) || empty($key)){
            return json($this->erres('参数错误'));
        }
        //判断登录
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        //查询店铺信息
        $DineshopModel = new DineshopModel();
        $shopinfo = $DineshopModel->getDineshopInfo($shopid);
        $shopstatus = $shopinfo['status'];
        $status = '';
        if($key == '审核'){
            if($shopstatus != 0){
                return json($this->erres('店铺状态错误'));
            }
            $status = 1;
        }else if($key == '通过审核'){
            if($shopstatus != 1){
                return json($this->erres('店铺状态错误'));
            }
            $status = 100;
        }else if($key == '审核不通过'){
            if($shopstatus != 1){
                return json($this->erres('店铺状态错误'));
            }
            $status = -100;
        }else if($key == '下架'){
            if($shopstatus != 100 && $shopstatus != -100){
                return json($this->erres('店铺状态错误'));
            }
            $status = -300;
        }else if($key == '重新提交审核'){
            if($shopstatus != -300){
                return json($this->erres('店铺状态错误'));
            }
            $status = 0;
        }
        $res = false;
        if($status !== ''){
            $res = $DineshopModel->modDineshopStatus($shopid, $status);
        }
        if($res){
            return json($this->sucjson());
        }else{
            return json($this->errjson(-1));
        }
    }
    /**
     * 获取推荐店铺信息列表
     */
    public function getRecomDineshopList(){
        $info = array();
        $list = array();
        $page = input('page',1); //页码
        $pagesize = input('pagesize',20); //每页显示数
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        $res = $DineshopModel->getRecomDineshopList($page, $pagesize);
        $info['allnum'] = $res['allnum'];
        if($res['dineshoplist']) {
            $list = $res['dineshoplist'];
        }
        return json($this->sucjson($info, $list));
    }
    /**
     * 获取可推荐店铺信息列表
     */
    public function getCanRecomDineshopList(){
        $info = array();
        $list = array();
        $page = input('page',1); //页码
        $pagesize = input('pagesize',20); //每页显示数
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        $res = $DineshopModel->getCanRecomDineshopList($page, $pagesize);
        $info['allnum'] = $res['allnum'];
        if($res['dineshoplist']) {
            $list = $res['dineshoplist'];
        }
        return json($this->sucjson($info, $list));
    }
    /**
     * 添加推荐
     */
    public function addRecomDineshop(){
        $info = array();
        $list = array();
        $shopid = input('shopid'); //店铺ID
        //判断登录
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        $res = $DineshopModel->addRecomDineshop($shopid);
        if($res){
            return json($this->sucjson());
        }else{
            return json($this->errjson(-1));
        }
    }
    /**
     * 删除推荐
     */
    public function delRecomDineshop(){
        $info = array();
        $list = array();
        $shopid = input('shopid'); //店铺ID
        //判断登录
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        $res = $DineshopModel->delRecomDineshop($shopid);
        if($res){
            return json($this->sucjson());
        }else{
            return json($this->errjson(-1));
        }
    }
    /**
     * 获取店铺信息列表
     */
    public function getDineshopList(){
        $info = array();
        $list = array();
        $page = input('page',1); //页码
        $pagesize = input('pagesize',20); //每页显示数
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        $res = $DineshopModel->getDineshopList($page, $pagesize);
        $info['allnum'] = $res['allnum'];
        if($res['dineshoplist']) {
            $list = $res['dineshoplist'];
        }
        return json($this->sucjson($info, $list));
    }
    /**
     * 获取店铺信息
     */
    public function getDineshopInfo(){
        $info = array();
        $list = array();
        $shopid = input('shopid',1); //店铺ID
        if(empty($shopid)){
            return json($this->errjson(-20001));
        }
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $disheslist = array();
        $DineshopModel = new DineshopModel();
		if (is_numeric($shopid)){
			$info = $DineshopModel->getDineshopInfo($shopid);
		}else{
			//按用户名模糊搜索
			$shopname = $shopid;
			$info = $DineshopModel->getDineshopInfoByName($shopname);
			if (isset($info['id'])){
				$shopid = $info['id'];
			}
		}
        if(isset($info['fontshopid']) && $info['fontshopid']){
            $DishesModel = new DishesModel();
            $dishlist = $DishesModel->getDishesListBysidNoPage($info['fontshopid']);
            if($dishlist){
                $disheslist = $dishlist;
            }
        }
        $info['disheslist'] = $disheslist;
        return json($this->sucjson($info, $list));
    }
    /**
     * 新增折扣时间段
     */
    public function addDiscountTimeslot(){
        $info = array();
        $list = array();
        $timeslot = input('timeslot'); //时间段
        $arr = explode('-', $timeslot);
        $startime = $arr[0];
        $endtime = $arr[1];
        if(!check_datetime($startime, 'hh:ii') || !check_datetime($endtime, 'hh:ii')) {
            return json($this->errjson(-20002)); exit;
        }
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        $slotid = $DineshopModel->addDiscountTimeslot($startime,$endtime);
        if($slotid){
            return json($this->sucjson(array("slotid" => $slotid)));
        }else{
            return json($this->errjson(-1)); 
        }
    }
    /**
     * 删除折扣时间段
     */
    public function delDiscountTimeslot(){
        $info = array();
        $list = array();
        $slotid = input('slotid'); //时间段id
        if(empty($slotid)){
            return json($this->errjson(-20001));
        }
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        $res = $DineshopModel->delDiscountTimeslot($slotid);
        if($res){
            return json($this->sucjson());
        }else{
            return json($this->errjson(-1)); 
        }
    }
    
    /**
     * 获取折扣时间段
     */
    public function getDiscountTimeslot(){
        $info = array();
        $list = array();
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        $list = $DineshopModel->getDiscountTimeslot();
        return json($this->sucjson($info, $list));
    }
    /**
     * 获取店铺折扣信息
     */
    public function getDineshopDiscount(){
        $info = array();
        $list = array();
        $shopid = input('shopid',1); //店铺ID
        if(empty($shopid)){
            return json($this->errjson(-20003));
        }
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $startdate = Date('Y-m-d');
        $endate = Date('Y-m-d', strtotime('+7 days'));
        $DineshopModel = new DineshopModel();
        $shopinfo = $DineshopModel->getDineshopInfo($shopid);
        $dishinfo = array();
        if($shopinfo){
            $info['shopid'] = $shopinfo['id'];
            $info['shopname'] = $shopinfo['shopname'];
            $info['shopicon'] = $shopinfo['shopicon'];
            $info['shopaddress'] = $shopinfo['address'];
            $fontshopid = $shopinfo['fontshopid'];
            $DishesModel = new DishesModel();
            $dishlist = $DishesModel->getDishesListBysidNoPage($fontshopid);
            
            if($dishlist){
                foreach($dishlist as $key => $val){
                    $dishinfo[$val['id']] = $val['dishesname'];
                }
            }
            $discountlist = $DineshopModel->getDineshopDiscount($fontshopid, $startdate, $endate);
            $discountimeslot = $DineshopModel->getDiscountTimeslot();
            foreach($discountimeslot as $key=>$val){
                $slotid = $val['id'];
                $timeslot = $val['timeslot'];
                $discid_list = array();
                $discount_list = array();
                foreach($discountlist as $k=>$v){
                    if($v['timeslot'] == $timeslot){
                        $discid_list[$v['date']] = $v['id'];
                        $discount = array();
                        foreach(explode('$', $v['discount']) as $_k=>$_v){
                            preg_match('/(\d+)\|(\d+)\@(([1-9]\d*|0)(\.\d{1,2})?)/i', $_v, $match);
                            if($match[1]){
                                $discount[$_k]['dishid'] = $match[1];
                                $discount[$_k]['dishname'] = isset($dishinfo[$match[1]])?$dishinfo[$match[1]]:'';
                                $discount[$_k]['type'] = $match[2];
                                $discount[$_k]['num'] = $match[3]*10;
                            }
                        }
                        $discount_list[$v['date']] = $discount;
                    }
                }
                $discountdata = array();
                for($i=0;$i<7;$i++){
                    $date = Date('Y-m-d', strtotime('+'.$i.' days'));
                    $discountdata[] = array(
                        'date' => $date,
                        'discid' => isset($discid_list[$date])?$discid_list[$date]:'',
                        'discount' => isset($discount_list[$date])?$discount_list[$date]:array()
                    );
                }
                $list[$key]['slotid'] = $slotid;
                $list[$key]['timeslot'] = $timeslot;
                $list[$key]['discountdata'] = $discountdata;
            }
        }
        
        return json($this->sucjson($info, $list));
    }
    /**
     * 添加店铺折扣信息
     */
    public function addDineshopDiscount(){
        $info = array();
        $list = array();
        $shopid = input('shopid'); //折扣信息
        if(empty($shopid)){
            return json($this->errjson(-20003));
        }
        $date = input('date'); //折扣日期
        if(empty($date)){
            return json($this->errjson(-80001));
        }
        $slotid = input('slotid'); //折扣时间段
        if(empty($slotid)){
            return json($this->errjson(-80002));
        }
        $discount = input('discount'); //折扣信息
        if(empty($discount)){
            return json($this->errjson(-80003));
        }
        foreach(explode('$', $discount) as $key=>$val){
            if(!preg_match( '/^\d+\|\d+\@([1-9]\d*|0)(\.\d{1,2})?$/i' , $val, $result)){
                return json($this->errjson(-80004)); exit;
            }
            preg_match('/(\d+)\|(\d+)\@(([1-9]\d*|0)(\.\d{1,2})?)/i', $val, $match);
            if($match[2]==1 && floatval($match[3]) > 1) {
                return json($this->errjson(-80004)); exit;
            }
        }
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        $discountinfo = $DineshopModel->getDiscount($shopid, $date, $slotid);
        //已有数据则修改
        if($discountinfo){
            $id = $discountinfo['id'];
            if(strstr($discountinfo['discount'], $discount)){
                $discount = $discountinfo['discount'];
            }else{
                $discount = $discountinfo['discount']."$".$discount;
            }
            $res = $DineshopModel->modDineshopDiscount($id, $discount);
        }else{
            $res = $DineshopModel->addDineshopDiscount($shopid, $date, $slotid, $discount);
        }
        if($res){
            return json($this->sucjson());
        }else{
            return json($this->errjson(-1)); 
        }
    }
    /**
     * 修改店铺折扣信息
     */
    public function modDineshopDiscount(){
        $id = input('id'); //折扣信息ID
        if(empty($id)){
            return json($this->errjson(-20001));
        }
        $discount = input('discount'); //折扣信息
        if(!empty($discount)){
            foreach(explode('$', $discount) as $key=>$val){
                if(!preg_match( '/^\d+\|\d+\@([1-9]\d*|0)(\.\d{1,2})?$/i' , $val)){
                    return json($this->errjson(-80004)); exit;
                }
                preg_match('/(\d+)\|(\d+)\@(([1-9]\d*|0)(\.\d{1,2})?)/i', $val, $match);
                if($match[2]==1 && floatval($match[3]) > 1) {
                    return json($this->errjson(-80004)); exit;
                }
            }
        }
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        if(!empty($discount)){
            $res = $DineshopModel->modDineshopDiscount($id, $discount);
        }else{
            $res = $DineshopModel->delDineshopDiscount($id);
        }
        if($res){
            return json($this->sucjson());
        }else{
            return json($this->errjson(-1)); 
        }
    }
    /**
     * 删除店铺折扣信息
     */
    public function delDineshopDiscount(){
        $id = input('id'); //折扣信息ID
        if(empty($id)){
            return json($this->errjson(-20001));
        }
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        $res = $DineshopModel->delDineshopDiscount($id);
        if($res){
            return json($this->sucjson());
        }else{
            return json($this->errjson(-1)); 
        }
    }
    /**
     * 获取店铺放号信息
     */
    public function getDineshopSell(){
        $info = array();
        $list = array();
        $shopid = input('shopid',1); //店铺ID
        if(empty($shopid)){
            return json($this->errjson(-20003));
        }
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $startdate = Date('Y-m-d');
        $endate = Date('Y-m-d', strtotime('+7 days'));
        $DineshopModel = new DineshopModel();
        $deskinfo = array();
        $desklist = $DineshopModel->getDesklist($shopid);
        foreach($desklist as $key=>$val){
            $deskinfo[$val['deskid']] = $val;
        }
        $sellist = $DineshopModel->getDineshopSell($shopid, $startdate, $endate);
        $timeslotlist = $DineshopModel->getDiscountTimeslot();
        foreach($timeslotlist as $key=>$val){
            $slotid = $val['id'];
            $timeslot = $val['timeslot'];
            $sellid_list = array();
            $sellinfo_list = array();
            foreach($sellist as $k=>$v){
                if($v['timeslot'] == $timeslot){
                    $sellid_list[$v['date']] = $v['id'];
                    $sellinfo = array();
                    foreach(explode('$', $v['sellinfo']) as $_k=>$_v){
                        preg_match('/(\w+)\@(\d+)/i', $_v, $match); 
                        if($match[1]){
                            $sellinfo[$_k]['tableid'] = $match[1];
                            $desknum = 0;
                            $ordernum = 0;
                            $deskname = '';
                            if(isset($deskinfo[$match[1]])){
                                $deskname = isset($deskinfo[$match[1]]['seatnum'])?$deskinfo[$match[1]]['seatnum'].'人桌':'';
                                $desknum = isset($deskinfo[$match[1]]['desknum'])?$deskinfo[$match[1]]['desknum']:0;
                                $ordernum = isset($deskinfo[$match[1]]['ordernum'])?$deskinfo[$match[1]]['ordernum']:0;
                            }
                            $sellinfo[$_k]['deskname'] = $deskname;
                            $sellinfo[$_k]['desknum'] = $desknum;
                             $sellinfo[$_k]['ordernum'] = $ordernum;
                            $sellinfo[$_k]['openum'] = $match[2];
                        }
                    }
                    $sellinfo_list[$v['date']] = $sellinfo;
                }
            }
            $selldata = array();
            for($i=0;$i<7;$i++){
                $date = Date('Y-m-d', strtotime('+'.$i.' days'));
                $selldata[] = array(
                    'date' => $date,
                    'sellid' => isset($sellid_list[$date])?$sellid_list[$date]:'',
                    'sellinfo' => isset($sellinfo_list[$date])?$sellinfo_list[$date]:array()
                );
            }
            $list[$key]['slotid'] = $slotid;
            $list[$key]['timeslot'] = $timeslot;
            $list[$key]['selldata'] = $selldata;
        }
        return json($this->sucjson($info, $list));
    }
    /**
     * 添加店铺放号信息
     */
    public function addDineshopSell(){
        $info = array();
        $list = array();
        $shopid = input('shopid'); //折扣信息
        if(empty($shopid)){
            return json($this->errjson(-20003));
        }
        $date = input('date'); //折扣日期
        if(empty($date)){
            return json($this->errjson(-80001));
        }
        $slotid = input('slotid'); //折扣时间段
        if(empty($slotid)){
            return json($this->errjson(-80002));
        }
        $sellinfo = input('sellinfo'); //折扣信息
        if(empty($sellinfo)){
            return json($this->errjson(-80003));
        }
        foreach(explode('$', $sellinfo) as $key=>$val){
            if(!preg_match( '/^\w+\@\d+$/i' , $val, $result)){
                return json($this->erres('放号信息格式错误'));
            }
        }
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        $info = $DineshopModel->getSellinfo($shopid, $date, $slotid);
        //已有数据则修改
        if($info){
            $id = $info['id'];
            if(strstr($info['sellinfo'], $sellinfo)){
                $sellinfo = $info['sellinfo'];
            }else{
                $sellinfo = $info['sellinfo']."$".$sellinfo;
            }
            $res = $DineshopModel->modDineshopSell($id, $sellinfo);
        }else{
            $res = $DineshopModel->addDineshopSell($shopid, $date, $slotid, $sellinfo);
        }
        if($res){
            return json($this->sucjson());
        }else{
            return json($this->errjson(-1)); 
        }
    }
    /**
     * 修改店铺折扣信息
     */
    public function modDineshopSell(){
        $id = input('id'); //折扣信息ID
        if(empty($id)){
            return json($this->errjson(-20001));
        }
        $sellinfo = input('sellinfo'); //折扣信息
        if(!empty($sellinfo)){
            foreach(explode('$', $sellinfo) as $key=>$val){
                if(!preg_match( '/^\w+\@\d+$/i' , $val)){
                    return json($this->errjson('放号信息格式错误'));
                }
            }
        }
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        if(!empty($sellinfo)){
            $res = $DineshopModel->modDineshopSell($id, $sellinfo);
        }else{
            $res = $DineshopModel->delDineshopSell($id);
        }
        if($res){
            return json($this->sucjson());
        }else{
            return json($this->errjson(-1)); 
        }
    }
    /**
     * 删除店铺折扣信息
     */
    public function delDineshopSell(){
        $id = input('id'); //折扣信息ID
        if(empty($id)){
            return json($this->errjson(-20001));
        }
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        $res = $DineshopModel->delDineshopSell($id);
        if($res){
            return json($this->sucjson());
        }else{
            return json($this->errjson(-1)); 
        }
    }
    /**
     * 添加店铺桌型
     */
    public function addDesk(){
        $info = array();
        $list = array();
        $shopid = input('shopid'); //店铺ID
        $deskid = input('deskid'); //桌型ID
        $seatnum = input('seatnum'); //就餐人数
        $desknum = input('desknum'); //数量
        if(empty($shopid)) return json($this->errjson(-20003));
        if(empty($deskid)) return json(self::erres("桌型编号不能为空"));
        if(empty($seatnum) || empty($desknum)) {
            return json($this->errjson(-20001));
        }
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        //检查桌型编号是否已经存在
        $deskinfo = $DineshopModel->getDeskinfo($shopid,$deskid);
        if(!empty($deskinfo)){
            return json(self::erres("桌型编号已存在"));
        }
        if($seatnum == $deskinfo['seatnum']){
            return json(self::erres("已有该座位数的桌型信息"));
        }
        if($DineshopModel->addDesk($shopid, $deskid, $seatnum, $desknum)){
            return json($this->sucjson(array('deskid' => $deskid)));
        }else{
            return json($this->erres('添加桌型信息失败！')); 
        }
    }
    /**
     * 修改店铺桌型
     */
    public function modDesk(){
        $shopid = input('shopid');
        $deskid = input('deskid'); //桌型ID
        $desknum = intval(input('desknum',-1)); //数量
        $status = intval(input('status',1)); //桌型状态
        if(empty($shopid) || empty($deskid)) {
            return json($this->errjson(-20001));
        }     
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        $deskinfo = $DineshopModel->getDeskinfo($shopid,$deskid);
        if(empty($deskinfo)){
            return json(self::erres("桌型信息不存在"));
        }
        $desknum = $desknum > 0 ? $desknum : $deskinfo['desknum'];
        $info = $DineshopModel->modDesk($shopid, $deskid, $desknum, $status);
        if($info){
           return json($this->sucjson()); 
        }else{
           return json($this->errjson($this->erres('修改桌型信息失败！'))); 
        }
    }

    /**
     * 获取店铺桌型
     */
    public function getDesklist(){
        $info = array();
        $list = array();
        $shopid = input('shopid'); //店铺ID
        if(empty($shopid)){
            return json($this->errjson(-20001));
        }        
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
		$DineshopModel = new DineshopModel();
		if (is_numeric($shopid)){
			$shopinfo = $DineshopModel->getDineshopInfo($shopid);
		}else{
			//非数字的话，认为是字符串店名
			$shopname = $shopid;
			$shopinfo = $DineshopModel->getDineshopInfoByName($shopname);
			if (isset($shopinfo['id'])) $shopid = $shopinfo['id'];
		}
        if($shopinfo){
            $info['shopid'] = $shopinfo['id'];
            $info['shopname'] = $shopinfo['shopname'];
            $info['shopicon'] = $shopinfo['shopicon'];
            $info['shopaddress'] = $shopinfo['address'];
        }
        $list = $DineshopModel->getDesklist($shopid);
        return json($this->sucjson($info, $list));
    }
    /**
     * 获取店铺桌型信息
     */
    public function getDeskinfo(){
        $info = array();
        $list = array();
        $shopid = input('shopid'); //店铺ID
        $deskid = input('deskid'); //桌型编号
        if(empty($deskid) || empty($shopid)){
            return json($this->errjson(-20001));
        }        
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        $info = $DineshopModel->getDeskinfo($shopid,$deskid);
        return json($this->sucjson($info, $list));
    }
    /**
     * 获取店铺对应的配送员信息
     */
    public function getDistripList(){
        $info = array();
        $list = array();
        $shopid = input('shopid');
        if(empty($shopid)){
            return json($this->errjson(-20001));
        }
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        $list = $DineshopModel->getDistripList($shopid);
        
        return json($this->sucjson($info, $list));
    }
    /**
     * 获取菜系列表
     */
    public function getCuisineList(){
        $info = array();
        $list = array();
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        $list = $DineshopModel->getCuisineList();
        return json($this->sucjson($info, $list));
    }
    /**
     * 根据店铺信息获取菜肴列表
     */
    public function getDishesList(){
        $info = array();
        $list = array();
        $shopid = input('shopid');
        $page = input('page',1); //页码
        $pagesize = input('pagesize',20); //每页显示数
        if(empty($shopid)){
            return json($this->errjson(-20001));
        }
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DishesModel = new DishesModel();
        $res = $DishesModel->getDishesListBysid($shopid, $page, $pagesize);
        $info['allnum'] = $res['allnum'];
        if($res['disheslist']) {
            $list = $res['disheslist'];
        }
        return json($this->sucjson($info, $list));
    }
    
    /**
     * 根据店铺信息获取可推荐菜肴列表
     */
    public function getCanRecomDishesList(){
        $info = array();
        $list = array();
        $shopid = input('shopid');
        $page = input('page',1); //页码
        $pagesize = input('pagesize',20); //每页显示数
        if(empty($shopid)){
            return json($this->errjson(-20001));
        }
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DishesModel = new DishesModel();
        $res = $DishesModel->getRecomDishesListBysid($shopid, $page, $pagesize);
        $info['allnum'] = $res['allnum'];
        if($res['disheslist']) {
            $list = $res['disheslist'];
        }
        return json($this->sucjson($info, $list));
    }

    /**
     * 获取指定用户的店铺信息
     */
    public function getUserDineshopInfo(){
        $info = array();
        $list = array();
        $userid = input('userid');
        if(!$this->checkAdminLogin()){
            return json($this->errjson(-10001));
        }
        $DineshopModel = new DineshopModel();
        $info = $DineshopModel->getUserDineshopInfo($userid);
        return json($this->sucjson($info, $list));
    }
}