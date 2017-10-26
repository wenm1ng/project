<?php
namespace base;

use base\Errcode;
use \app\data\model\UserModel;
use \app\admin\model\AdminUserModel;
use \think\Request;
use \think\Session;
use \think\Jump;
use think\Controller;
use app\index\model\User;
use think\Db;

class Base_2 extends Controller
{
	public $uid;
	public $username;
	public $ip;

	public function __construct(){
		parent::__construct();
		$this->uid = !empty(session::get('home_uid'))?session::get('home_uid'):"";
		$this->username = !empty(session::get('home_username'))?session::get('home_username'):"";
		$this->ip = $_SERVER['REMOTE_ADDR'];

		//进行访客登记
		$ipInfo = Db::name('ip')->where("ip_address = '{$this->ip}'")->find();

		if(empty($ipInfo)){
			//添加ip访客记录
			$data['ip_address'] 	= $this->ip;
			$data['last_time']		= date('Y-m-d H:i:s');
			$data['visitor_num'] 	= 1;
			$data['create_time']	= date('Y-m-d H:i:s');
			$data['update_time']	= date('Y-m-d H:i:s');

			Db::name('ip')->insert($data);
		}else{
			//再次访问网站
			//5分钟内的访问不算访问次数，但是算最后一次访问时间
			if($ipInfo['update_time'] < date('Y-m-d H:i:s',time()-5*60)){
				//可以加访问次数
				$data_yes['visitor_num'] = $ipInfo['visitor_num'] + 1;
				$data_yes['update_time'] = date('Y-m-d H:i:s');

				Db::name('ip')->where("ip_address = '{$this->ip}'")->update($data_yes);
			}else{
				//只能算最后访问时间
				$data_no['update_time'] = date('Y-m-d H:i:s');
				Db::name('ip')->where("ip_address = '{$this->ip}'")->update($data_no);
			}
		}
	}

	public function obj_to_array($list){
		return json_decode(json_encode($list),true);
	}
}
?>