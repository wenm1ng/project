<?php
namespace app\data\controller;

use \base\Baseapi;

class Index extends Baseapi
{
    public function Index()
    {
        return json(['data'=>'sss','code'=>1,'message'=>'操作完成']);
    }
    public function test()
    {
        var_dump($this->res);
        return json(['data'=>'sss','code'=>1,'message'=>'操作完成']);
    }
}
