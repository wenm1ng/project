<?php 
	namespace app\home\controller;

	use think\Db;
	use think\Controller;
	use think\Session;
	use think\Request;
	use think\View;

	class Article extends Controller
	{
		public function viewinfo(){
			if(Request::instance()->isPost()){
				//别人评论了
			}else{
				//页面
				$id = input('id');
				$info = Db::name('article')->where("article_id = '{$id}'")->find();
				$view = new View();
				return $view->fetch('viewinfo',['info'=>$info]);
			}
		}
	}
?>