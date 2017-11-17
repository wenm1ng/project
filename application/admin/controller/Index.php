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

    //后台继承页面
    public function index(){
        return view('base');
    }

    //后台首页
    public function home(){
        // echo $_SERVER['REMOTE_ADDR'];exit;
        $first_time = date('Y-m-d',strtotime('-1 months'));
        $second_time = date('Y-m-d');
        $year_time = date('Y-m-d',strtotime('-1 years'));
        //30天内访问人次
        $person_time = Db::name('ip')->whereTime('create_time','between',["{$first_time}","{$second_time}"])->count();
        //1年内访问人次
        $person_time_year = Db::name('ip')->whereTime('create_time','between',["{$year_time}","{$second_time}"])->count();
        //历史访问人数
        $person_num = Db::name('ip')->count();


        return view('home',['meta_title'=>'后台首页','person_time'=>$person_time,'person_time_year'=>$person_time_year,'person_num'=>$person_num]);
    }

}
