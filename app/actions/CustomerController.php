<?php

class CustomerController{
	function __construct(){

	}

	function signUp(){
		$info = array();
		$info['name'] = F3::get('POST.uname');
		$info['pass'] = F3::get('POST.upass');
		$info['phone'] = F3::get('POST.uphone');

		$uid = User::signUp($info);

		if(F3::get('POST.mobile') != false){
			// WEB客户端
			if($uid != -1){
				F3::reroute('/');
			}else{
				F3::reroute('/signup');
			}
		}else{
			// Mobile客户端
			echo $uid;
		}
	}

	function login(){
		F3::set('title',"登录");
		$user = User::valid(F3::get('POST.uname'), F3::get('POST.upass'));
		if($user != false){ //login success
			User::login($user);
			F3::reroute('/');
		} else {
			F3::set('login_status','error');
			F3::set('error_display','inline-block');
			echo Template::serve('login.html');
		}
	}

	function logout(){
		Account::logout();
		F3::reroute('/');
	}

}
