<?php
namespace app\index\controller;
use base\Base;
use \app\index\model\ClassifyModel;
// namespace base;
class Classify extends Base
{
	 private $model = null;
	/*
	* 	初始化控制器
	*/
	public function __construct(){
        parent::__construct();
        $this->model = new ClassifyModel();
    }
	/*
	* 显示分类页
	*/
	public function index()
	{
		return view('index');

	}


	/*
	* 添加分类
	*/
	public function addClassify()
	{
		$yiji = input('yiji/a');
		$erji = input('erji/a');
		// $data = array_merge($yiji,$erji);
		// var_dump(empty($erji));

		$err = $this->model->addClassify($yiji,$erji);
		echo $err;
		
	}
}
