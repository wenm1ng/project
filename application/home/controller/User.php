<?php 
	namespace app\home\controller;

	use think\Db;
	use think\Controller;
	use think\Session;
	use think\Validate;
	use PHPMailer\PHPMailer;
	use PHPMailer\Exception;
	use base\Base_2;

	Class User extends Base_2{
		

		//前台登录
		public function login(){
			if(empty(input('loginName'))){
	            $this->error('请填写登录账号！');exit;
	        }
	        if(empty(input('password'))){
	            $this->error('请填写登录密码！');exit;
	        }
	        // print_r(111);exit;
	        $table_name = 'user_home';
	        $where = array(
	            'loginName' => input('loginName'),
	            );
	        $info = Db::name($table_name)->where($where)->find();
	        if(!empty($info)){
                if($info['password'] == input('password')){
                    session::set('home_uid',$info['user_id'],'home');
                    session::set('home_loginName',$info['loginName'],'home');
                    session::set('home_username',$info['user_name'],'home');
                    session::set('home_sign',sha1(md5($info['loginName']).'wenminghenshuai'),'home');
                    

                    $this->success('登录成功','index/index/index');
                }else{
                    $this->error('密码错误');
                }
	        }else{
	            $this->error('该用户不存在！');
	        }
		}

		//前台退出登录
		public function logout(){
		 	// $this->model = new AdminUserModel();
	        session(null,'home');
	        if (!session::has('home_uid','home')) {
	            // return json(self::sucres());
	            $this->success('退出登录成功!',url('index/index/index'));
	        } else {
	            // return json(self::erres("退出登录失败"));
	            $this->error('退出登录失败!');
	        }
		}

		//前台注册用户
		public function register(){
			// $rule = [
			//     'loginName'  	=> 'regex:/^\w{4,16}$/',
			//     'password'   	=> 'regex:/^\w{6,16}$/',
			//     'email' 		=> 'email',
			//     'phone' 		=> 'regex:/^1[34578][0-9]{9}$/'
			// ];
			// $msg = [
			//     'loginName.regex' 	=> '用户名必须为4-16位任意的数字字母下划线',
			//     'password.regex'   	=> '密码必须为6-16位任意的数字字母下划线',
			//     'phone.regex'  		=> '电话格式错误',
			//     'email'        		=> '邮箱格式错误',
			// ];
			$data = [
			    'loginName' 	=> input('loginName'),
			    'password'   	=> input('password'),
			    'email' 		=> input('email'),
			    'phone' 		=> input('phone'),
			];

			$validate = new Validate();
			// $validate->scene('edit', ['loginName', 'password','email','phone']);
			if(!$result = $validate->scene('add')->check($data)){
				$this->error($validate->getError());
			}

			$data['user_name']   = input('user_name');
			$data['create_time'] = date('Y-m-d');

			if(Db::name('user_home')->insert($data)){
				//第一种 失败
				// //进行邮件发送
				// $message = '你好';

				// $title = '文明博客注册激活邮件';

				// \phpmailer\Email::send($data['email'],$title,$message);exit;

				//第二种失败
				// $toemail='736038880@qq.com';
		  //       $name='文明博库';
		  //       $subject='QQ邮件发送测试';
		  //       $content='恭喜你，邮件测试成功。';
		  //       dump(send_mail($toemail,$name,$subject,$content));exit;

				//第三种



	        // $toemail = $data['email'];//定义收件人的邮箱

	        // $mail = new PHPMailer();

	        // $mail->isSMTP();// 使用SMTP服务
	        // $mail->CharSet = "utf8";// 编码格式为utf8，不设置编码的话，中文会出现乱码
	        // $mail->Host = "smtp.163.com";// 发送方的SMTP服务器地址
	        // $mail->SMTPAuth = true;// 是否使用身份验证
	        // $mail->Username = "736038880@qq.com";// 发送方的QQ邮箱用户名，就是自己的邮箱名
	        // $mail->Password = "xxxx";// 发送方的邮箱密码，不是登录密码,是qq的第三方授权登录码,要自己去开启,在邮箱的设置->账户->POP3/IMAP/SMTP/Exchange/CardDAV/CalDAV服务 里面
	        // $mail->SMTPSecure = "ssl";// 使用ssl协议方式,
	        // $mail->Port = 465;// QQ邮箱的ssl协议方式端口号是465/587

         //    $mail->setFrom("xxxxx@qq.com","xxxx");// 设置发件人信息，如邮件格式说明中的发件人,
         //    $mail->addAddress($toemail,'xxxxx');// 设置收件人信息，如邮件格式说明中的收件人
         //    $mail->addReplyTo("xxxxx@qq.com","Reply");// 设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址
         //    //$mail->addCC("xxx@163.com");// 设置邮件抄送人，可以只写地址，上述的设置也可以只写地址(这个人也能收到邮件)
         //    //$mail->addBCC("xxx@163.com");// 设置秘密抄送人(这个人也能收到邮件)
         //    //$mail->addAttachment("bug0.jpg");// 添加附件


         //    $mail->Subject = "这是一个测试邮件";// 邮件标题
         //    $mail->Body = "邮件内容是 <b>我就是玩玩</b>，哈哈哈！";// 邮件正文
         //    //$mail->AltBody = "This is the plain text纯文本";// 这个是设置纯文本方式显示的正文内容，如果不支持Html方式，就会用到这个，基本无用

         //    if(!$mail->send()){// 发送邮件
         //        echo "Message could not be sent.";
         //        echo "Mailer Error: ".$mail->ErrorInfo;// 输出错误信息
         //    }else{
         //        echo '发送成功';
         //    }

				$this->success('注册成功');
				
			}else{
				log_error('fail_sql',Db::getlastsql());
				$this->error('注册失败！');
			}
		}

		//ajax验证是否存在
		public function ajax(){
			if(input('username')){
				$info = Db::name('user_home')->where('loginName','=',input('username'))->find();
				if(!empty($info)){
					return 1;
				}else{
					return 0;
				}
			}elseif(input('email')){
				$info = Db::name('user_home')->where('email','=',input('email'))->find();
				if(!empty($info)){
					return 1;
				}else{
					return 0;
				}
			}else{
				return 1;
			}
			
		}
	}
 ?>