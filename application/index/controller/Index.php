<?php
namespace app\index\controller;
use base\Base_2;
use \app\admin\model\UserModel;
use \app\admin\model\AdminUserModel;
use think\Log;
use think\Request;
use think\Db;
use think\Session;
// namespace base;
class Index extends Base_2
{
    public $uid;
    public $uname;
    //前台
    public function index(){
        
        // echo Db::getlastsql();exit;
        // $this->view->info = $userinfo;
        //获取文章信息
        $article_list = Db::name('article')->where("status = 1")->order('create_time desc')->select();
        foreach ($article_list as $key => $val) {
        	//过滤掉Img标签
        	$content = preg_replace('/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i', '', $val['content']);
        	$article_list[$key]['content'] = mb_strimwidth($content, 0 , 100 ,'...');
        }
        $name = session::get('home_username','home');

    	return view('index',['name'=>$name,'article_list'=>$article_list]);
    }
    
}
