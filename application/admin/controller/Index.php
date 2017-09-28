<?php
namespace app\admin\controller;

use \base\Base_1;
use think\Db;
use think\Controller;
class Index extends Base_1
{

    public function test()
    {
        
        var_dump($this->res);
        return json(['data'=>'sss','code'=>1,'message'=>'操作完成']);
    }

    //后台首页
    public function index(){
        return view('base');
    }
}
