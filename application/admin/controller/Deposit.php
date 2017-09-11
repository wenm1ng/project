<?php 
namespace app\admin\controller;

use base\Base;
use think\Session;
use think\Db;
class Deposit extends Base
{
	public function index(){
		$list = Db::table('t_user_info')->field("f_uid uid,f_mobile mobile,f_depositmoney depositmoney,f_usermoney usermoney")
		->where("f_auth_status",100)->select();

		$depositall = 0;//押金总额
		$moneyall = 0;  //余额总额
		foreach ($list as $key => $val) {
			$depositall += $val['depositmoney'];
			$moneyall   += $val['usermoney'];
		}
		return view('index',['list'=>$list,'depositall'=>$depositall,'moneyall'=>$moneyall]);
	}

	public function time(){
		$data = array(
			'f_deposit'=>input('time'),
			'f_modtime'=>date('Y-m-d H:i:s')
			);
		if(Db::table('t_admin_dineshop')->where("f_sid <> 0")->update($data)){
			// error_lo('sql',Db::getlastsql());
			return $this->success('修改成功');
		}
	}

	public function income(){
		// print_r(input('datetime'));
		if(!empty(input('datetime'))){
			$datearr = explode('to',input('datetime'));
			$where['o.f_modtime'] = array('between',"$datearr[0],$datearr[1]"); 
		}else{
			$date = date('Y-m-d');
			$where['o.f_modtime'] = array('like',"%{$date}%");
		}
		
		$where['o.f_status'] = 100;
		

		$list = Db::table('t_orders')
		->alias('o')
		->field('f_allmoney allmoney,f_shopid shopid,f_shopname shopname,f_oid oid,o.f_modtime modtime')
		->join('t_admin_dineshop s','o.f_shopid = s.f_sid')
		->where($where)
		->order('o.f_modtime')
		// ->group('f_shopid')
		->select();

		$count = 0;
		foreach ($list as $key => $val) {
			$time = date('Y-m-d',strtotime($val['modtime']));
			$list[$key]['modtime'] = $time;
			$count += $val['allmoney'];
			// $arr[$time][] = $list[$key];
		}
		// echo Db::getlastsql();
		// print_r($list);
		return view('income',['list'=>$list,'count'=>$count]);
	}

	// 导出
	public function exportexcel(){
		$width_arr = array(array(30,30,30,30,30)); // 设置列宽
		$excel_file = "门店收益金";

		// print_r(input('datetime'));
		if(!empty(input('datetime'))){
			$datetime = str_replace('+','',input('datetime'));
			$datearr = explode('to',$datetime);
			$where['o.f_modtime'] = array('between',"$datearr[0],$datearr[1]"); 
		}else{
			$date = date('Y-m-d');
			$where['o.f_modtime'] = array('like',"%{$date}%");
		}
		
		$where['o.f_status'] = 100;
		
		$list = array();
		$list = Db::table('t_orders')
		->alias('o')
		->field('f_oid oid,f_shopname shopname,f_allmoney allmoney,o.f_modtime modtime')
		->join('t_admin_dineshop s','o.f_shopid = s.f_sid')
		->where($where)
		->order('o.f_modtime')
		// ->group('f_shopid')
		->select();

		if(!empty($list)){
			$count = 0;
			foreach ($list as $key => $val) {
				$time = date('Y-m-d',strtotime($val['modtime']));
				$list[$key]['modtime'] = $time;
				$list[$key]['allmoney'] = $val['allmoney'].'元';
				// $list[$key][] = '';
				$count += $val['allmoney'];
			}
			$count = $count.'元';

			array_push($list[0],$count);
		}
		
		// echo Db::getlastsql();
		// print_r($list);exit;
    		$ceils = $this->excelDataFormat($list);
	    	$excel_content[0] = array(
	            'sheet_name' => '门店收益金',
	            'sheet_title' => array('序号','门店','收益金','日期','总额'),
	            'ceils' => $ceils['excel_ceils'],
	            'freezePane' => 'B2',
	            'headerColor' => getCssClass("header"),
	            'headerColumnCssClass' => array(
	                'id' => getCssClass('blue'),
	                'Status_Description' => getCssClass('grey'),
	            ),
	            'oddCssClass' => getCssClass("odd"),
	            'evenCssClass' => getCssClass("even")
	        );

		
        exportExcel($width_arr,$excel_content, $excel_file);
	}
}
 ?>