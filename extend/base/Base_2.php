<?php
namespace base;

use base\Errcode;
use \app\data\model\UserModel;
use \app\admin\model\AdminUserModel;
use \think\Request;
use \think\Session;
use \think\Jump;
use think\Controller;
use app\index\model\User;
use think\Db;
use phpmailer\PHPMailer;

class Base_2 extends Controller
{
	public $uid;
	public $username;
	public $ip;
	public $key;

	public function __construct(){
		parent::__construct();
		$this->uid = !empty(session::get('home_uid'))?session::get('home_uid'):"";
		$this->username = !empty(session::get('home_username'))?session::get('home_username'):"";
		$this->ip = $_SERVER['REMOTE_ADDR'];
		$this->key = 'jfYnb51fubczY5Vkbnc';
		//进行访客登记
		$ipInfo = Db::name('ip')->where("ip_address = '{$this->ip}'")->find();

		if(empty($ipInfo)){
			//添加ip访客记录
			$data['ip_address'] 	= $this->ip;
			$data['last_time']		= date('Y-m-d H:i:s');
			$data['visitor_num'] 	= 1;
			$data['create_time']	= date('Y-m-d H:i:s');
			$data['update_time']	= date('Y-m-d H:i:s');

			Db::name('ip')->insert($data);
		}else{
			//再次访问网站
			//5分钟内的访问不算访问次数，但是算最后一次访问时间
			if($ipInfo['update_time'] < date('Y-m-d H:i:s',time()-5*60)){
				//可以加访问次数
				$data_yes['visitor_num'] = $ipInfo['visitor_num'] + 1;
				$data_yes['update_time'] = date('Y-m-d H:i:s');

				Db::name('ip')->where("ip_address = '{$this->ip}'")->update($data_yes);
			}else{
				//只能算最后访问时间
				$data_no['update_time'] = date('Y-m-d H:i:s');
				Db::name('ip')->where("ip_address = '{$this->ip}'")->update($data_no);
			}
		}
	}

	public function obj_to_array($list){
		return json_decode(json_encode($list),true);
	}

	//发送邮件
	public function send_mail($toemail,$operation,$userid,$username = ''){
		$mail = new phpmailer();

		if(empty($username)){
			$username = $toemail;
		}
        $mail->isSMTP();// 使用SMTP服务
        $mail->CharSet = "utf8";// 编码格式为utf8，不设置编码的话，中文会出现乱码
        $mail->Host = "smtp.163.com";// 发送方的SMTP服务器地址
        $mail->SMTPAuth = true;// 是否使用身份验证
        $mail->Username = "st_yanxin@163.com";// 发送方的QQ邮箱用户名，就是自己的邮箱名
        $mail->Password = "zhanshengziji99";// 发送方的邮箱密码，不是登录密码,是qq的第三方授权登录码,要自己去开启,在邮箱的设置->账户->POP3/IMAP/SMTP/Exchange/CardDAV/CalDAV服务 里面
        // $mail->SMTPSecure = "ssl";// 使用ssl协议方式,
        // $mail->Port = 465;// QQ邮箱的ssl协议方式端口号是465/587
        $mail->IsHTML(false);

        $mail->setFrom("st_yanxin@163.com","小明博客");// 设置发件人信息，如邮件格式说明中的发件人,
        $mail->addAddress($toemail, $username);// 设置收件人信息，如邮件格式说明中的收件人
        $mail->addReplyTo("st_yanxin@163.com","Reply（回复）");// 设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址
        //$mail->addCC("xxx@163.com");// 设置邮件抄送人，可以只写地址，上述的设置也可以只写地址(这个人也能收到邮件)
        //$mail->addBCC("xxx@163.com");// 设置秘密抄送人(这个人也能收到邮件)
        //$mail->addAttachment("bug0.jpg");// 添加附件

        //激活用的token
        $token = md5('home'.'User'.$operation.date('Y-m-d').$this->key);
        $user_id = $userid;
        $userid = base64_encode($userid).'gm5Bi';

        switch($operation){
        	case 'register'://注册
        		$content = url('User/active?token='.$token.'&userid='.$userid);

		        $mail->Subject = "小明博客激活";// 邮件标题
		        $mail->Body = "您好：".$toemail."! 
		        您需要点击以下链接来激活你的小明博客账户: ".$_SERVER['HTTP_HOST'].$content;// 邮件正文
        		break;
        	case 'forget'://找回密码
        		$content = url('User/reset?token='.$token.'&userid='.$userid);

	        	$mail->Subject = '小明博客密码找回';
	        	$mail->Body = "您好：".$toemail."! 
		        您需要点击以下链接来找回您的密码: ".$_SERVER['HTTP_HOST'].$content;// 邮件正文
        		break;
        	default://发送验证码
        		$code = '';
        		for ($i=0; $i < 6; $i++) { 
        			$code .= mt_rand(0,9);
        		}
        		//查询
        		$info = Db::name('code')->where("user_id = '{$user_id}'")->find();
        		if(empty($info)){
        			$data['code'] = $code;
        			$data['user_id'] = $user_id;
        			$data['start_time'] = date('Y-m-d H:i:s');
        			$data['end_time'] = date('Y-m-d H:i:s',time()+1800);

        			Db::name('code')->insert($data);
        		}else{
        			//修改
        			$data['code'] = $code;
        			$data['start_time'] = date('Y-m-d H:i:s');
        			$data['end_time'] = date('Y-m-d H:i:s',time()+1800);
        			Db::name('code')->where("user_id = '{$user_id}'")->update($data);
        		}

	        	$mail->Subject = '小明博客邮箱登录';
	        	$mail->Body = "您好：".$toemail."! 您的验证码：".$code;

        		break;
        }
        
        //$mail->AltBody = "This is the plain text纯文本";// 这个是设置纯文本方式显示的正文内容，如果不支持Html方式，就会用到这个，基本无用

        if(!$mail->send()){// 发送邮件
        	log_error('send_fail'.date('Y-m-d'),$mail->ErrorInfo);// 记录错误信息
        }
	}
}
?>