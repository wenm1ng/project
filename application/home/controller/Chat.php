<?php 
	namespace app\home\controller;

	use think\Db;
	use think\Controller;
	use think\Session;
	use think\Request;
	use think\View;
	use base\Base_2;

	//关于我、碎言碎语、学无止境、留言板集成控制器
	class Chat extends Base_2
	{
		public function index(){
			header('content-type:text/html;charset:utf-8');
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

		//学无止境
		public function study(){
			//获取文章信息
	        $page = Db::name('article')->where("status = 1")->order('create_time desc')->paginate(10,false,['type'=>'BootstrapDetailed']);
	        $article_list = obj_to_array($page);
	        foreach ($article_list['data'] as $key => $val) {
	        	//过滤掉Img标签
	        	$content = preg_replace('/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i', '', $val['content']);
	        	$article_list['data'][$key]['content'] = mb_strimwidth(strip_tags($content), 0 , 100 ,'...');

	            //获取评论个数
	            $count = Db::name('article_comment')->where("article_id = '{$val['article_id']}'")->count();
	            $article_list['data'][$key]['comment_count'] = $count;
	        }

	        //热门推荐
	        $article_hot = Db::name('article')->field('title,read_num,article_id')->order('read_num DESC')->limit(5)->select();

	        $name = session::get('home_username');
			$meta_title = '学无止境';
			// print_r($article_list);
	    	return view('study',['name'=>$name,'article_list'=>$article_list,'article_hot'=>$article_hot,'_page'=>$page,'meta_title'=>$meta_title]);
		}

		//留言板
		public function board(){
			if(Request::instance()->isPost()){
				$data = input();
				
				if(empty($this->uid)){
					$this->error('请登录后再留言');
				}else{
					$userinfo = Db::name('user_home')->where("user_id = '{$this->uid}'")->find();
					$data['user_id']	= $this->uid;
					$data['user_name'] 	= $userinfo['user_name'];
				}
				$data['create_time'] 	= date('Y-m-d H:i:s');
				//别人评论了
				if(Db::name('board')->insert($data)){
					$this->success('留言成功');
				}else{
					$this->success('留言失败');
				}
			}else{
				//获取留言
				$page = Db::name('board')->where("is_reply = 0")->order("create_time DESC")->paginate(10);

				$board_list = obj_to_array($page);
				// print_r($board_list);exit;
				foreach ($board_list['data'] as $key => $val) {
					//获取头像
					$user_info = Db::name('user_home')->field('img')->where("user_id = '{$val['user_id']}'")->find();
					if(empty($user_info)){
						$board_list['data'][$key]['img'] = '';
					}else{
						$board_list['data'][$key]['img'] = $user_info['img'];
					}

					//获取回复
					$reply_list = Db::name('board')->where("is_reply = 1 and link_board_id = '{$val['board_id']}'")->select();
					$board_list['data'][$key]['child'] = $reply_list;
				}
				return view('board',['board_list'=>$board_list,'_page'=>$page]);
			}
			
		}

		//回复留言
		public function reply(){
			if(!empty(session::get('home_username'))){
				$data = input();
				$data['user_id'] = $this->uid;
				$data['user_name'] = $this->username;
				$data['create_time'] = date('Y-m-d H:i:s');
				// print_r($data);exit;
				//别人评论了
				if(Db::name('board')->insert($data)){
					$this->success('回复成功');
				}else{
					$this->success('回复失败');
				}
			}else{
				$this->error('您还未登录，请登录后再回复评论');
			}
		}
	}
 ?>