<?php

class User{


	//根据info数组插入数据表,并执行登录操作
	static function signUp($info){
	}

	static function valid($uname, $upass){
		$uname = trim($uname);
		$upass = trim($upass);

		if(empty($uname) || empty($upass)){
			return false;
		}

		$r = DB::sql('SELECT * FROM users WHERE name = :uname AND pass = :upass', array(
			':uname' => $uname, ':upass' => $upass
		));

		if( count($r) > 0 ){
			return $r[0];
		}else{
			return false;
		}
	}

	static function login($user){
		setcookie('se_user_id', $user['uid']);
		setcookie('se_user_name', $user['uname']);
		setcookie('se_user_token', self::generate_login_token($user['uid']));

		$user = self::id_admin($user['uid']);
		if($user != false)
			setcookie('se_user_admin', $user['restaurant']);
	}

	static function logout(){
		setcookie('se_user_id', '', time() - 86400);
		setcookie('se_user_name',  '', time() - 86400);
		setcookie('se_user_token',  '', time() - 86400);
		//setcookie('se_user_admin',  '', time() - 86400);
	}

	// 判断是否是管理员用户，如果是餐厅管理员，则返回其管理的餐厅的id ，否则返回false
	static function is_admin(){
	}












	static function is_login(){
		$cookie = F3::get('COOKIE');

		if(!isset($cookie['se_user_id']))
			return false;	

		if($cookie['se_user_id'] != ''){
			return self::validate_login_token($cookie['se_user_id'], $cookie['se_user_token']);
		}else{
			return false;
		}
	}






	static function generate_login_token($uid){
		return md5( $uid . F3::get('TOKEN_SALT') );
	}

	static function validate_login_token($uid, $token){
		$valid = self::generate_login_token($uid);
		return $token == $valid;
	}

}
