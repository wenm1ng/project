<?php
namespace app\admin\controller;

use base\Base;
use think\Log;
use think\Request;
use think\Session;
use think\Db;
use \app\admin\model\PushModel;

class Push extends Base
{

	// 推送列表
   public function index()
   {
   		$PushModel =new PushModel();
   		$list = $PushModel->getPushList();
   		$status = array(0=>'未推送',1=>'已推送');
        return view('index',['list'=>$list,'status'=>$status]);
   }

   // 添加推送页面
   public function add()
   {
        return view('add');
   }

   // 添加推送
   public function doadd()
   {
   		if(empty(input('title'))){
   			return $this->error('标题不能为空');
   		}
   		$data['title'] = input('title');
   		if(empty(input('content'))){
   			return $this->error('内容不能为空');
   		}
   		$data['content'] = input('content');
   		$data['status'] = 0;
   		$data['del'] = 0;
   		$data['addtime'] = time();
   		$PushModel =new PushModel();
   		$suc = $PushModel->addPush($data);
   		if(empty($suc)){
   			return $this->error('添加失败');
   		}else{
   			return $this->success('添加成功','push/index');
   		}
   }

   //  推送详情
   public function details()
   {
   		$id = $_GET['id'];
   		$PushModel = new PushModel();
   		$list = $PushModel->getDetails($id);
        return view('details',['list'=>$list]);
   }

   public function dodetails()
   {
   		$id = $_GET['id'];
   		$PushModel = new PushModel();
   		$list = $PushModel->doDetails($id,$_POST);
   		if(empty($list)){
   			return $this->error('修改失败');
   		}else{
   			return $this->success('修改成功','push/index');
   		}
   }

   // 管理员删除推送
   public function del()
   {
   		$id = $_POST['id'];
   		$PushModel = new PushModel();
   		$list = $PushModel->doDel($id);
   		if(empty($list)){
   			echo 2;
   		}else{
   			echo 1;
   		}
   }

   // 管理员推送
   public function push()
   {
   		$id = $_POST['id'];
   		$PushModel = new PushModel();
   		$list = $PushModel->doPush($id);
   		if(empty($list)){
   			echo 2;
   		}else{
   			echo 1;
   		}
   }

   // 用户读取推送
   public function read()
   {
   		$id = $_POST['id']; // 推送ID
   		$uid = $_POST['uid'];// 用户ID
   		$PushModel = new PushModel();
   		$list = $PushModel->doRead($id,$uid);
   }

   // 用户未读取提示
   public function noread()
   {
   		// 用户ID
   		// $uid = $_POST['id'];
   		$PushModel = new PushModel();
   		$list = $PushModel->showMe();
   		$count = 0;
   		// 判断用户是否存在字段中
   		foreach($list as $key => $val){
   			if(!in_array(10001,explode(',', $val['uid']))){
   				++$count;
   			}
   		}
   		// 返回未读条数
   		return $count;
   }
}
