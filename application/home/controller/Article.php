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
				$keyArr = explode(',',$info['key']);
				$info['key'] = $keyArr;

				//上一篇
				$last_info = Db::name('article')->where("article_id < '{$info['article_id']}'")->order('article_id DESC')->find();
				//下一篇
				$next_info = Db::name('article')->where("article_id > '{$info['article_id']}'")->order('article_id')->find();

				$view = new View();
				return $view->fetch('viewinfo',['info'=>$info,'last_info'=>$last_info,'next_info'=>$next_info]);
			}
		}
	}
?>