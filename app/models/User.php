<?php

class User{

	static function updateAdmin($rid){
		$uid = F3::get("COOKIE.se_user_id");
		$sql = "UPDATE admin SET rid = $rid WHERE uid = $uid";
		setcookie('se_user_admin', $rid, time() + 86400, '/');
		DB::sql($sql);
	}

	static function exist($name, $value, $table, $con = "1"){
		$sql = "SELECT * FROM $table WHERE $name = $value AND $con";
		$r = DB::sql($sql);

		if(count($r) > 0)
			return true;
		else
			return false;
	}

	//根据info数组插入数据表,并执行登录操作
	static function signUp($info){
		//var_dump($info);
		if($info['pass'] !== $info['pass_check'])
			return -1;
		if(self::exist("name", $info['name'], "user"))
			return -2;
		$r = DB::sql("INSERT INTO user VALUES ('', :uname, :upass);",
			array( ':uname' => $info['name'], ':upass' => $info['pass'])
		);
		$uid = DB::get_insert_id();

		if($info['type'] == 1){//admin
			$r = DB::sql("INSERT INTO admin VALUES (:uid, :rid);",
				array( ':uid' => $uid, ':rid' => '0')
			);
		}else{// customer
			$r = DB::sql("INSERT INTO customer VALUES (:phone, :point, :uid);",
				array(':phone' => $info['phone'] , ':point' => '0', ':uid' => $uid)
			);
			//Code::dump($r);
		}
		self::login(array('uid' => $uid, 'name' => $info['name']));
		return $uid;
	}

	static function changepass($info){
		if($info['new_pass'] !== $info['new_pass_check'])
			return -1;
		$old_right_pass=DB::sql("SELECT passwd FROM user WHERE uid = :uid",
			array( ':uid' => $info['uid'] ));
		if($old_right_pass[0]['passwd'] !== $info['old_pass'])
			return -2;

		$r = DB::sql("UPDATE user SET passwd = :new_pass WHERE uid = :uid", array(':new_pass' => $info['new_pass'], ':uid' => $info['uid']));
		return 0;
	}

	static function valid($uname, $upass){
		$uname = trim($uname);
		$upass = trim($upass);

		if(empty($uname) || empty($upass)){
			return false;
		}

		$r = DB::sql('SELECT * FROM user WHERE name = :uname AND passwd = :upass', 
			array( ':uname' => $uname, ':upass' => $upass
		));

		if( count($r) > 0 ){
			return $r[0];
		}else{
			return false;
		}
	}

	static function login($user){
		setcookie('se_user_id', $user['uid'], time() + 86400, '/');
		setcookie('se_user_name', $user['name'], time() + 86400, '/');
		setcookie('se_user_token', self::generate_login_token($user['uid']), time() + 86400, '/');

		//Code::dump($user);

		$rid = self::is_admin($user['uid']);
		if($rid !== false){
			setcookie('se_user_admin', $rid, time() + 86400, '/');
			//echo "<h1>set Cookie</h1>";
		}
	}

	static function logout(){
		setcookie('se_user_id', '', time() - 86400, '/');
		setcookie('se_user_name',  '', time() - 86400, '/');
		setcookie('se_user_token',  '', time() - 86400, '/');
		setcookie('se_user_admin',  '', time() - 86400, '/');
	}

	// 判断是否是管理员用户，如果是餐厅管理员，
	// 则返回其管理的餐厅的id ，否则返回false
	static function is_admin($uid = 0){
		//if(!self::is_login()) return false;

		$cookie = F3::get('COOKIE');
		if(isset($cookie['se_user_id']))
			$uid = $cookie['se_user_id'];

		$r = DB::sql('SELECT * FROM admin WHERE uid = :uid', 
			array( ':uid' => $uid));

		//var_dump($r);
		if( count($r) > 0 )
			return $r[0]['rid'];
		else
			return false;
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
	//add
	static function getUserInfo($uid){
		$sql = "SELECT * FROM user WHERE uid = $uid";
		//$u=DB::sql('SELECT * FROM user WHERE uid = :uid');
		$u=DB::sql($sql);
		return $u;
	}

}
