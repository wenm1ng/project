<?php 
	namespace app\home\controller;

	use think\Db;
	use think\Controller;
	use think\Session;
	use think\Validate;
	use phpmailer\PHPMailer;
	use phpmailer\Exception;
	use base\Base_2;
	use think\Request;
	use think\Verify;

	Class User extends Base_2{

		public $uid;
		public $username;

		public function __construct(){
			parent::__construct();
			$this->uid = session::get('home_uid');
			$this->username = session::get('home_username');
		}

		//加载验证码
	    public function verify(){
	        //实例化验证码类
	        header('content-type:text/html;charset=utf-8');
	        $verify = new Verify();
	        // var_dump($verify);
	        $verify->fontSize=20;//字体大小
	        $verify->length=4;//验证码位数
	        $verify->useNoise=false;//验证码干扰素
	        //输出验证码
	        return $verify->entry();
	    }

	    //验证
	    public function ajax(){
	    	if(input('type')){
	    		switch (input('type')) {
	    			case '1'://验证用户名
	    				$loginName = input('loginName');
	    				$info = Db::name("user_home")->where("loginName = '{$loginName}'")->find();
	    				if(empty($info)){
	    					return 1;
	    				}else{
	    					return 0;
	    				}
	    				break;
	    			case '2'://验证邮箱
	    				$email = input('email');
	    				$info = Db::name("user_home")->where("email = '{$email}'")->find();
	    				if(empty($info)){
	    					return 1;
	    				}else{
	    					return 0;
	    				}
	    				break;
	    			case '3'://验证验证码
	    				$fcode = input('fcode');
	    				$verify = new Verify();
	    				$verify->reset = false;
	    				if($verify->check($fcode)){
	    					return 1;
	    				}else{
	    					return 0;
	    				}
	    				break;
	    			
	    			default:
	    				return 0;
	    				break;
	    		}
	    	}else{
	    		return 0;
	    	}
	    }

		//前台登录
		public function login(){
			if(session::has('home_uid')){
				$this->error('您已登录，请勿重复登录','index/index/index');
			}else{
				if(Request::instance()->isPost()){
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
			        	if($info['status'] == 0){
			        		$this->error('该账号还未激活！');exit;
			        	}

		                if($info['password'] == input('password')){
		                    session::set('home_uid',$info['user_id']);
		                    session::set('home_loginName',$info['loginName']);
		                    session::set('home_username',$info['user_name']);
		                    session::set('home_img',$info['img']);
		                    session::set('home_sign',sha1(md5($info['loginName']).'wenminghenshuai'));
		                    
		                    //登录访问记录
		                    $login_info = Db::name('login')->where("user_id = '{$info['user_id']}'")->find();

		                    if(empty($login_info)){
		                    	//第一次登录本博客
		                    	$data['user_id'] = $info['user_id'];
		                    	$data['user_name'] = $info['user_name'];
		                    	$data['create_time'] = date('Y-m-d H:i:s');
		                    	$data['update_time'] = date('Y-m-d H:i:s');
		                    	Db::name('login')->insert($data);
		                    }else{
		                    	//非第一次登录
		                    	$data['user_name'] = $info['user_name'];
		                    	$data['update_time'] = date('Y-m-d H:i:s');
		                    	Db::name('login')->where("user_id = '{$info['user_id']}'")->update($data);
		                    }
		                    $this->success('登录成功','index/index/index');
		                }else{
		                    $this->error('密码错误');
		                }
			        }else{
			            $this->error('该用户不存在！');
			        }
				}else{
					return view();
				}
			}
			
		}

		//前台退出登录
		public function logout(){
		 	// $this->model = new AdminUserModel();
	        session('home_uid',null);
    		session('home_loginName',null);
    		session('home_username',null);
    		session('home_sign',null);
			session('home_img',null);    		
	        if (!session::has('home_uid')) {
	            // return json(self::sucres());
	            $this->success('退出登录成功!');
	        } else {
	            // return json(self::erres("退出登录失败"));
	            $this->error('退出登录失败!');
	        }
		}

		
		public function email_login(){
			if(empty(input('email'))){
				$this->error('邮箱不能为空');
			}
			$email = input('email');
			//获取用户id
			$info = Db::name("user_home")->where("email = '{$email}'")->find();
			if(!empty($info)){
				switch(input('type')){
					case 'send'://发送邮箱验证码
						$this->send_mail($email,'send',$info['user_id'],$info['user_name']);
						$this->success('验证码发送成功，请登录邮箱查看');
						break;
					case 'login'://邮箱登录
						if(!empty(input('code'))){
							$code_info = Db::name('code')->where("user_id = '{$info['user_id']}'")->find();
							if(input('code') == $code_info['code']){
								if($code_info['end_time'] > date('Y-m-d H:i:s')){
									//登录并且登记session
									session::set('home_uid',$info['user_id']);
				                    session::set('home_loginName',$info['loginName']);
				                    session::set('home_username',$info['user_name']);
				                    session::set('home_img',$info['img']);
				                    session::set('home_sign',sha1(md5($info['loginName']).'wenminghenshuai'));
				                    
				                    //登录访问记录
				                    $login_info = Db::name('login')->where("user_id = '{$info['user_id']}'")->find();

				                    if(empty($login_info)){
				                    	//第一次登录本博客
				                    	$data['user_id'] = $info['user_id'];
				                    	$data['user_name'] = $info['user_name'];
				                    	$data['create_time'] = date('Y-m-d H:i:s');
				                    	$data['update_time'] = date('Y-m-d H:i:s');
				                    	Db::name('login')->insert($data);
				                    }else{
				                    	//非第一次登录
				                    	$data['user_name'] = $info['user_name'];
				                    	$data['update_time'] = date('Y-m-d H:i:s');
				                    	Db::name('login')->where("user_id = '{$info['user_id']}'")->update($data);
				                    }
				                    $this->success('登录成功','index/index/index');
								}else{
									$this->error('该验证码已过期，请重新发送验证码');
								}
							}else{
								$this->error('邮箱验证码输入错误');
							}
						}else{
							$this->error('邮箱验证码不能为空');
						}
						
						break;
					default:
						$this->error('参数错误');
						break;
				}
			}else{
				$this->error('该邮箱不存在');
			}
		}


		//前台注册用户
		public function register(){

			if(Request::instance()->isPost()){
				if(empty(input('loginName'))){
					$this->error('请输入用户名');
				}
				if(empty(input('password'))){
					$this->error('请输入密码');
				}
				if(empty(input('email'))){
					$this->error('请输入邮箱');
				}
				if(empty(input('repwd'))){
					$this->error('请输入确认密码');
				}

				$data = [
				    'loginName' 	=> input('loginName'),
				    'password'   	=> input('password'),
				    'email' 		=> input('email'),
				];

				// $validate = new Validate();
				// // $validate->scene('edit', ['loginName', 'password','email','phone']);
				// if(!$result = $validate->scene('add')->check($data)){
				// 	$this->error($validate->getError());
				// }

				$data['create_time'] = date('Y-m-d H:i:s');

				if($insertId = Db::name('user_home')->insertGetId($data)){
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

					//第三种 成功



		        $toemail = $data['email'];//定义收件人的邮箱

		        $mail = new phpmailer();

		        $mail->isSMTP();// 使用SMTP服务
		        $mail->CharSet = "utf8";// 编码格式为utf8，不设置编码的话，中文会出现乱码
		        $mail->Host = "smtp.163.com";// 发送方的SMTP服务器地址
		        $mail->SMTPAuth = true;// 是否使用身份验证
		        $mail->Username = "st_yanxin@163.com";// 发送方的QQ邮箱用户名，就是自己的邮箱名
		        $mail->Password = "zhanshengziji99";// 发送方的邮箱密码，不是登录密码,是qq的第三方授权登录码,要自己去开启,在邮箱的设置->账户->POP3/IMAP/SMTP/Exchange/CardDAV/CalDAV服务 里面
		        $mail->SMTPSecure = "ssl";// 使用ssl协议方式,
		        $mail->Port = 465;// QQ邮箱的ssl协议方式端口号是465/587
		        $mail->IsHTML(false);

	            $mail->setFrom("st_yanxin@163.com","小明博客");// 设置发件人信息，如邮件格式说明中的发件人,
	            $mail->addAddress($toemail, $data['loginName']);// 设置收件人信息，如邮件格式说明中的收件人
	            $mail->addReplyTo("st_yanxin@163.com","Reply（回复）");// 设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址
	            //$mail->addCC("xxx@163.com");// 设置邮件抄送人，可以只写地址，上述的设置也可以只写地址(这个人也能收到邮件)
	            //$mail->addBCC("xxx@163.com");// 设置秘密抄送人(这个人也能收到邮件)
	            //$mail->addAttachment("bug0.jpg");// 添加附件

	            //激活用的token
	            $token = md5('home'.'User'.'register'.date('Y-m-d').$this->key);
	            $userid = base64_encode($insertId).'gm5Bi';
	            $content = url('User/active?token='.$token.'&userid='.$userid);

	            $mail->Subject = "小明博客激活邮件";// 邮件标题
	            $mail->Body = "您好：".$toemail."! 
	            您需要点击以下链接来激活你的小明博客账户: ".$_SERVER['HTTP_HOST'].$content;// 邮件正文
	            //$mail->AltBody = "This is the plain text纯文本";// 这个是设置纯文本方式显示的正文内容，如果不支持Html方式，就会用到这个，基本无用

	            if(!$mail->send()){// 发送邮件
	            	log_error('send_fail'.date('Y-m-d'),$mail->ErrorInfo);// 记录错误信息
	            }

				$this->success('注册成功，请前往邮箱激活该账号','login');	
					
				}else{
					log_error('fail_sql',Db::getlastsql());
					$this->error('注册失败！');
				}
			}else{
				return view();
			}
			
		}

		// //ajax验证是否存在
		// public function ajax(){
		// 	if(input('username')){
		// 		$info = Db::name('user_home')->where('loginName','=',input('username'))->find();
		// 		if(!empty($info)){
		// 			return 1;
		// 		}else{
		// 			return 0;
		// 		}
		// 	}elseif(input('email')){
		// 		$info = Db::name('user_home')->where('email','=',input('email'))->find();
		// 		if(!empty($info)){
		// 			return 1;
		// 		}else{
		// 			return 0;
		// 		}
		// 	}else{
		// 		return 1;
		// 	}
			
		// }


		//激活账户
		public function active(){
			if(input('token') && input('userid')){
				if(md5('home'.'User'.'register'.date('Y-m-d').$this->key) == input('token')){
					$replace = str_replace('gm5Bi','',input('userid'));
					$userid = base64_decode($replace);
					// echo $userid;
					if($result = Db::name('user_home')->where("user_id = '{$userid}'")->update(array('status'=>1))){
						$this->success('激活成功！','login');
					}else{
						$this->error('无效操作！');
					}
				}else{
					$this->error('无效操作！');
				}
			}else{
				$this->error('无效操作！');
			}
		}

		//个人中心页面
		public function center(){
			if(!empty($this->uid)){
				if(Request::instance()->isPost()){
					//修改个人信息
					if(empty(input('img'))){
						$this->error('请上传头像');exit;
					}
					if(empty(input('username'))){
						$this->error('请填写昵称');exit;
					}

					$data['img'] 			= input('img');
					$data['user_name'] 		= input('username');
					$data['update_time'] 	= date('Y-m-d H:i:s');

					if(Db::name('user_home')->where("user_id = '{$this->uid}'")->update($data)){
						session::set('home_username',input('username'));
						session::set('home_img',input('img'));
						$this->success('修改成功');
					}else{
						$this->error('修改失败');
					}
				}else{
					//获取用户相关信息
			  //       $captcha = new Captcha();
					// $code = $captcha->entry();

					$info = Db::name('user_home')->where("user_id = '{$this->uid}'")->find();
					$meta_title = '个人信息';
					return view('center',['meta_title'=>$meta_title,'info'=>$info]);
				}
			}else{
				$this->error('您还未登录，无法查看该页面','index/index/index');
			}
			
		}

		//修改密码页面
		public function password(){
			if(!empty($this->uid)){
				if(Request::instance()->isPost()){
					//修改个人信息
					if(empty(input('originpsw'))){
						$this->error('请输入原密码');
					}
					if(empty(input('newpsw'))){
						$this->error('请输入新密码');
					}
					if(empty(input('confirmpsw'))){
						$this->error('请输入确认密码');
					}
					if(input('newpsw') != input('confirmpsw')){
						$this->error('两次输入的密码不一致');
					}

					if(strlen(input('newpsw')) < 6){
						$this->error('密码长度必须大于6位数');
					}


					$info = Db::name('user_home')->where("user_id = '{$this->uid}'")->find();

					if(strlen(input('newpsw')) == $info['password']){
						$this->error('新密码不能和原密码相同');
					}
					if(input('originpsw') != $info['password']){
						$this->error('原密码输入不正确');
					}

					$data['password'] = input('newpsw');
					$data['update_time'] = date('Y-m-d H:i:s');
					if(Db::name('user_home')->where("user_id = '{$this->uid}'")->update($data)){
		        		session('home_uid',null);
		        		session('home_loginName',null);
		        		session('home_username',null);
		        		session('home_sign',null);

						$this->success('修改成功','index/index/index');
					}else{
						$this->error('修改失败');
					}
				}else{
					//页面
					$meta_title = '修改密码';
					return view('password',['meta_title'=>$meta_title]);
				}
			}else{
				$this->error('您还未登录，无法查看该页面','index/index/index');
			}
		}

		//找回密码
		public function forget(){
			if(Request::instance()->isPost()){
				if(empty(input('email'))){
					$this->error('邮箱不能为空');
				}

				$toemail = input('email');

				$info = Db::name('user_home')->where("email = '{$toemail}'")->find();

				if(!empty($info)){
					$this->send_mail($toemail,'forget',$info['user_id'],$info['user_name']);
					$this->success('邮件发送成功，请前往邮箱找回密码');
				}else{
					$this->error('该邮箱不存在');
				}
			}else{
				return view();
			}
		}

		//重置密码
		public function reset(){
			if(Request::instance()->isPost()){
				$user_id = input('user_id');
				if(empty(input('password'))){
					$this->error('密码不能为空');
				}
				if(empty(input('password'))){
					$this->error('确认密码不能为空');
				}
				if(input('password') != input('repwd')){
					$this->error('两次输入的密码不一致');
				}

				if($result = Db::name('user_home')->where("user_id = '{$user_id}'")->update(array('password'=>input('password')))){
					$this->success('密码重置成功','login');
				}else{
					$this->error('密码重置失败');
				}

			}else{
				if(input('token') && input('userid')){
					if(md5('home'.'User'.'forget'.date('Y-m-d').$this->key) == input('token')){
						$replace = str_replace('gm5Bi','',input('userid'));
						$userid = base64_decode($replace);
						// echo $userid;
						if($result = Db::name('user_home')->where("user_id = '{$userid}'")->find()){
							return view('reset',['userid'=>$userid]);
						}else{
							$this->error('无效操作！');
						}
					}else{
						$this->error('无效操作！');
					}
				}else{
					$this->error('无效操作！');
				}
			}
		}
	}

	
 ?>