<?php
namespace app\index\controller;
use base\Base;
use \app\index\model\GoodsModel;
// namespace base;
class Goods extends Base
{
	private $model = null;
	/**
     * 控制器初始化
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new GoodsModel();

    }

    public function index()
    {

        return view('index');
    }

    public function addCuishine()
    {
    	$cname = input('cname');

    }
}
