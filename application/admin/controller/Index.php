<?php
namespace app\admin\controller;

use \base\Base;
use think\Db;
class Index extends Base
{
	
    public function login()
    {
        return view('login');
    }
    public function test()
    {
        
        var_dump($this->res);
        return json(['data'=>'sss','code'=>1,'message'=>'操作完成']);
    }

    public function index(){
        return view('index');
    }
}
