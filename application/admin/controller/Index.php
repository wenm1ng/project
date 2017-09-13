<?php
namespace app\admin\controller;

use \base\Base_1;
use think\Db;
use think\Controller;
class Index extends Controller
{
	//后台
    public function login()
    {
        return view('login');
    }
    public function test()
    {
        
        var_dump($this->res);
        return json(['data'=>'sss','code'=>1,'message'=>'操作完成']);
    }

    //后台首页
    public function index(){
        return view('index');
    }
}
