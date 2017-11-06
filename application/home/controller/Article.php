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
				
				if(empty($this->uid)){
					$data['user_id']	= 0;
					$data['user_name'] 	= '游客'.mt_rand(99999,999999);
				}else{
					$userinfo = Db::name('user_home')->where("user_id = '{$this->uid}'")->find();
					$data['user_id']	= $this->uid;
					$data['user_name'] 	= $userinfo['user_name'];
				}
				$data['create_time'] 	= date('Y-m-d H:i:s');
				//别人评论了
				if(Db::name('article_comment')->insert($data)){
					$this->success('评论成功');
				}else{
					$this->success('评论失败');
				}
			}else{
				//页面
				// echo session::get('home_uid');exit;
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
				$article_page = Db::name('article_comment')->where("article_id = '{$id}' and is_reply = 0")->order('create_time DESC')->paginate(10);
				
				$article_list = $this->obj_to_array($article_page);

				foreach ($article_list['data'] as $key => $val) {
					$user_info = Db::name("user_home")->where("user_id = '{$val['user_id']}'")->find();
					if(empty($user_info['img'])){
						$article_list['data'][$key]['img'] = '';
					}else{
						$article_list['data'][$key]['img'] = $user_info['img'];
					}
					

					$child = Db::name('article_comment')->where("link_comment_id = '{$val['comment_id']}' and is_reply = 1")->order('create_time')->select();
					foreach ($child as $k => $v) {
						$user_info_child = Db::name('user_home')->where("user_id = '{$v['user_id']}'")->find();
						$child[$k]['img'] = $user_info_child['img'];
					}
					
					// echo Db::getlastsql();
					$article_list['data'][$key]['child'] = $child;
				}
				// print_r($article_list);exit;
				$view = new View();
				//热门推荐
				$article_hot = Db::name('article')->field('title,read_num,article_id')->order('read_num DESC')->limit(5)->select();
				// var_dump($username);exit;
				
				return $view->fetch('viewinfo',['info'=>$info,'last_info'=>$last_info,'next_info'=>$next_info,'article_page'=>$article_page,'article_list'=>$article_list,'article_hot'=>$article_hot]);
			}
		}

		public function reply(){
			if(!empty(session::get('home_username'))){
				$data = input();
				$data['user_id'] = $this->uid;
				$data['user_name'] = $this->username;
				$data['create_time'] = date('Y-m-d H:i:s');
				// print_r($data);exit;
				//别人评论了
				if(Db::name('article_comment')->insert($data)){
					$this->success('回复成功');
				}else{
					$this->success('回复失败');
				}
			}else{
				$this->error('您还未登录，请登录后再回复评论');
			}
		}

		// public function recursion($child){
		// 	// $child = Db::name('article_comment')->where("link_comment_id = '{$id}' and is_reply = 1")->order('create_time DESC')->select();

		// 	if(!empty($child)){
		// 		foreach ($child as $k => $val) {
		// 			$user_info_child = Db::name("user_home")->where("user_id = '{$val['user_id']}'")->find();
		// 			if(empty($user_info_child)){
		// 				$child[$k]['img'] = '';
		// 			}else{
		// 				$child[$k]['img'] = $user_info_child['img'];
		// 			}

		// 			$info = Db::name('article_comment')->where("link_comment_id = '{$val['comment_id']}'")->select();
		// 			if(empty($info)){
		// 				return $child;
		// 			}else{
		// 				self::recursion($child);
		// 			}
		// 		}
		// 	}
		// }


	}
?>