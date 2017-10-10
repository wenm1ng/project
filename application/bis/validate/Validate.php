<?php 
	namespace app\bis\validate;

	use think\Validate;

	class Deal extends Validate(){
		public $rule = [
			['loginName','regex:/^\w{4,16}$/','用户名必须为4-16位任意的数字字母下划线'],
			['password','regex:/^\w{6,16}$/','用户名必须为6-16位任意的数字字母下划线'],
			['phone','regex:/^1[34578][0-9]{9}$/','电话格式错误'],
			['email','email','邮箱格式错误']
		];
		
		//场景
		public $scene = [
			'add' => ['loginName','password','phone','email']
		]; 

	}
 ?>