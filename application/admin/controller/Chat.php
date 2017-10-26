<?php 
	namespace app\admin\controller;

	use think\Db;
	use think\Controller;
	use think\Session;
	use think\Request;
	use think\View;
	use base\Base_2;

	class Chat extends Base_2
	{
		public function index(){
			$page = Db::name('chat')->paginate(10);
			$list = $this->obj_to_array($page);
			$meta_title = '日记列表';
			return view('index',['meta_title'=>$meta_title,'_page'=>$page,'list'=>$list]);
		}
	}
 ?>