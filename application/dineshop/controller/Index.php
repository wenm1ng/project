<?php
namespace app\dineshop\controller;

use \base\Baseshop;
use think\Db;
class Index extends Baseshop
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
    public function index()
    {
        return view('index');
    }
    public function head()
    {
        return view('head');
    }
    public function menu()
    {
        return view('menu');
    }
    public function main()
    {
        return view('main');
    }
}
