<?php 
	namespace app\home\controller;

	use think\Db;
	use think\Controller;
	use think\Session;
	use think\Request;
	use think\View;
	use base\Base_2;

	class Chat extends Base_2
	{
		public function index(){
			$page = Db::name('chat')->order('create_time DESC')->paginate(10);
			$list = $this->obj_to_array($page);
			$meta_title = '碎言碎语';
			return view('index',['meta_title'=>$meta_title,'_page'=>$page,'list'=>$list]);
		}

		//关于我
		public function about(){
			$meta_title = '关于我';
			return view('about',['meta_title'=>$meta_title]);
		}
	}
 ?>