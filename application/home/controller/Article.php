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

				//阅读数+1
				$readInfo = Db::name('article_read')->where("ip_address = '{$this->ip}' and article_id = '{$id}'")->find();
				if(empty($readInfo)){
					//本ip初次阅读该文章，直接添加
					$readData['ip_address'] = $_SERVER['REMOTE_ADDR'];
					$readData['article_id'] = $id;
					$readData['create_time'] = date('Y-m-d H:i:s');
					$readData['update_time'] = date('Y-m-d H:i:s');

					Db::name('article_read')->insert($readData);
				}else{
					//本ip再次阅读该文章，判断间断时间是否超过1分钟限制
					if($readInfo['update_time'] < date('Y-m-d H:i:s',time()-60)){
						//大于1分钟，可以增加阅读数
						$data['read_num'] = $info['read_num'] + 1;
						Db::name('article')->where("article_id = '{$id}'")->update($data);
						Db::name('article_read')->where("article_id = '{$id}' and ip_address = '{$this->ip}'")->update(array('update_time'=>date('Y-m-d H:i:s')));
					}else{
						//小于1分钟，不能增加阅读数
					}
				}
				

				$keyArr = explode(',',$info['key']);
				$info['key'] = $keyArr;

				//上一篇
				$last_info = Db::name('article')->where("article_id < '{$info['article_id']}'")->order('article_id DESC')->find();
				//下一篇
				$next_info = Db::name('article')->where("article_id > '{$info['article_id']}'")->order('article_id')->find();

				//获取评论
				$article_page = Db::name('article_comment')->where("article_id = '{$id}'")->order('create_time DESC')->paginate(10);
				
				$article_list = $this->obj_to_array($article_page);
				// print_r($article_list);exit;
				$view = new View();
				//热门推荐
				$article_hot = Db::name('article')->field('title,read_num,article_id')->order('read_num DESC')->limit(5)->select();
				// var_dump($username);exit;
				if(!empty(session::get('home_uid'))){
					$userid = session::get('home_uid');
				}else{
					$userid = 0;
				}
				return $view->fetch('viewinfo',['info'=>$info,'last_info'=>$last_info,'next_info'=>$next_info,'userid'=>$userid,'article_page'=>$article_page,'article_list'=>$article_list,'article_hot'=>$article_hot]);
			}
		}
	}
?>