<?php 
	namespace app\admin\controller;

	use \base\Base_1;
	use think\Db;
	use think\Controller;
	use think\Session;
	use think\Request;

	class Article extends Base_1
	{
		public $username ;
		public $userid;

		public function __construct(){
			parent::__construct();
			$this->username = session::get('username');
			$this->uid = session::get('uid');

		}

		//文章首页
		public function index(){
			$table_name = 'article';
			$list = Db::name($table_name)->paginate(10);
            //把分页数据赋值给模板变量
            // $p = new \think\Page($list,2);
            $this->assign('page',$list);
            $list =obj_to_array($list);
            $this->assign('list',$list);
            $this->assign('meta_title','文章列表');

            // print_r($list);exit;
            return $this->fetch();
		}


		public function addinfo(){
			if(Request::instance()->isPost()){
				$data = input();
				$data['create_userid'] 		= $this->uid;
				$data['create_username'] 	= $this->username;
				$data['create_time'] 		= date('Y-m-d H:i:s');

				if(Db::name('article')->insert($data)){
					$this->success('添加成功','index');
				}else{
					$this->error('添加失败');
				}
			}else{
				return view('addinfo',['meta_title'=>'新增文章']);
			}
		}

		public function delinfo(){
			if(Request::instance()->isPost()){
				//多选删除
				$ids = implode(',', input());
				$map['article_id'] = array('in' , $ids); 
			}else{
				//单删除
				$map['article_id'] = input('id');
			}

			if(Db::name('article')->where($map)->delete()){
				$this->success('删除成功');
			}else{
				log_error('fail_sql'.date('Y-m-d'), Db::getlastsql());
				$this->success('删除失败');
			}
		}
	}

 ?>