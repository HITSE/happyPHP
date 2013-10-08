<?php

class Home{

	function __construct(){
		if(User::is_login()){
			F3::set("login", "true");
			if(User::is_admin()!==false){
				F3::set("admin","true");
				if(Admin::is_rest_signed()!==false)
					F3::set('rest_signed', 'true');
			}
		}
	}

	function run(){
		F3::set('title',"HappyQueue");
		echo Template::serve('index.html');
	}

	function showLoginPage(){
		F3::set('title',"登录");
		F3::set('login_status','');
		F3::set('error_display','none');
		echo Template::serve('login.html');
	}

	function login(){
		F3::set('title',"登录");
		$user = User::valid(F3::get('POST.uname'), F3::get('POST.upass'));
		if($user !== false){ //login success
			User::login($user);
			F3::reroute('/');
		} else {
			F3::set('login_status','error');
			F3::set('error_display','inline-block');
			echo Template::serve('login.html');
		}
	}

	function logout(){
		User::logout();
		F3::reroute('/');
	}

	function showSignUp(){
		F3::set('title',"注册");
		echo Template::serve('user/usersignup.html');
	}

	function showchangepass(){
		F3::set('title',"修改密码");
		echo Template::serve('user/changepass.html');
	}

	function signUp(){
		F3::set('title',"注册");
		$info = array();
		$info['name'] = F3::get('POST.uphone');
		$info['pass'] = F3::get('POST.upass');
		$info['pass_check'] = F3::get('POST.upass_check'); //TODO check pass
		$info['phone'] = F3::get('POST.uphone');
		$info['type'] = F3::get('POST.ugroup');
		//Code::dump($info);

		$uid = User::signUp($info);

		//if(F3::get('GET.mobile') != false){
			// WEB客户端
		if($uid == -1){
			F3::set("has_submit", "true");
			//F3::set("success", "false");
			F3::set("msg", "密码输入不一致");
			F3::set("p", F3::get('POST'));
			echo Template::serve('user/usersignup.html');
		}else if($uid == -2){
			F3::set("has_submit", "true");
			//F3::set("success", "false");
			F3::set("msg", "该手机号码已注册");
			F3::set("p", F3::get('POST'));
			echo Template::serve('user/usersignup.html');
		}else{
			F3::set("success_title", "注册成功!");
			F3::set("success_msg", "注意: 在注册成功之后, 排队也快乐会加您为飞信好友, 请务必同意, 否则无法享受短信通知服务.");
			$msg = new PHPFetion(F3::get('Fetionphone'), F3::get('Fetionpasswd'));
			$msg->addfriend("排队也快乐", $info['phone']);
			echo Template::serve('user/successnotify.html');
		}
	}

	function changepass(){
		F3::set('title',"修改密码");
		$info = array();
		$info['uid'] = F3::get('COOKIE.se_user_id');
		$info['phone'] = F3::get('COOKIE.se_user_name');
		$info['old_pass'] = F3::get('POST.old_pass');
		$info['new_pass'] = F3::get('POST.new_pass');
		$info['new_pass_check'] = F3::get('POST.new_pass_check'); //TODO check pass

		$uid = User::changepass($info);

		//if(F3::get('GET.mobile') != false){
			// WEB客户端
		if($uid == -1){
			F3::set("has_submit", "true");
			//F3::set("success", "false");
			F3::set("msg", "新密码输入不一致.");
			echo Template::serve('user/changepass.html');
		}else if($uid == -2){
			F3::set("has_submit", "true");
			//F3::set("success", "false");
			F3::set("msg", "旧密码错误.");
			echo Template::serve('user/changepass.html');
		}else{
			F3::set("success_title", "操作成功!");
			F3::set("success_msg", "密码修改成功, 关闭将跳到到首页.");
			$msg = new PHPFetion(F3::get('Fetionphone'), F3::get('Fetionpasswd'));
			$msg->send($info['phone'], "提醒您, 您的密码已被修改, 如果不是您本人的操作, 请立即联系客服.");
			echo Template::serve('user/successnotify.html');
		}
	}
/*
	function usersuccesstest(){
		echo Template::serve('user/signupsuccess.html');
	}
*/
	function noaccess()
	{
		F3::reroute('/');
	}

	function about(){
		F3::set('route', 'about');
		echo Template::serve('about.html');
	}

};

?>
