<?php
namespace app\index\controller;
use base\Base_2;
use \app\admin\model\UserModel;
use \app\admin\model\AdminUserModel;
use think\Log;
use think\Request;
use think\Db;
use think\Session;
use think\Verify;

// namespace base;
class Index extends Base_2
{
    public $uid;
    public $uname;
    //前台
    public function index(){
        
        // echo Db::getlastsql();exit;
        // $this->view->info = $userinfo;
        // $limit = input('limit');
        // echo session::get('home_uid');exit;
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

    	return view('index',['name'=>$name,'article_list'=>$article_list,'article_hot'=>$article_hot,'_page'=>$page]);
    }

    
}
