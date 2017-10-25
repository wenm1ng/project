<?php 
	namespace app\home\controller;

	use think\Db;
	use think\Controller;
	use think\Session;
	use think\Request;
	use think\View;
	use base\Base_2;

	class Article extends Base_2
	{

		public function viewinfo(){

			if(Request::instance()->isPost()){
				$data = input();
				$user_id = input('user_id');
				$userinfo = Db::name('user_home')->where("user_id = '{$user_id}'")->find();
				if(empty($userinfo)){
					$data['user_name'] = '游客'.mt_rand(99999,999999);
				}else{
					$data['user_name'] = $userinfo['user_name'];
				}
				$data['create_time'] = date('Y-m-d H:i:s');
				//别人评论了
				if(Db::name('article_comment')->insert($data)){
					$this->success('评论成功');
				}else{
					$this->success('评论失败');
				}
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

				//获取评论
				$article_page = Db::name('article_comment')->where("article_id = '{$id}'")->paginate(10);
				
				$article_list = $this->obj_to_array($article_page);
				// print_r($article_list);exit;
				$view = new View();
				
				// var_dump($username);exit;
				if(!empty(session::get('home_uid'))){
					$userid = session::get('home_uid');
				}else{
					$userid = 0;
				}
				return $view->fetch('viewinfo',['info'=>$info,'last_info'=>$last_info,'next_info'=>$next_info,'userid'=>$userid,'article_page'=>$article_page,'article_list'=>$article_list]);
			}
		}
	}
?>