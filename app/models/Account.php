<?php

class Account{

	static function get_condition_by_group($g){
		if($g == -1)
			return "ugroup <> 0";
		else if($g == 1)
			return "ugroup = 1";
		else if($g == 2)
			return "ugroup = 2";
		else
			return FALSE;
	}

	static function group($page, $group = -1){
		
		$total_user = Account::count_user($group);
		if($total_user === FALSE)
			return FALSE;
		$page_num = ceil($total_user / EACH_PAGE_SHOW);

		$limit = $page * EACH_PAGE_SHOW;
		$each_page_show = EACH_PAGE_SHOW;

		$condition = Account::get_condition_by_group($group);
		if($condition=== FALSE)
			return FALSE;

		//TODO 分页
		//$sql = "SELECT * FROM users WHERE 1 = 1 AND {$condition} ORDER BY uid ASC LIMIT {$limit}, {$each_page_show};";
		$sql = "SELECT * FROM users WHERE 1 = 1 AND {$condition} ORDER BY uid ASC;";

		DB::sql($sql);

		return $page_num;
	}
	
	static function count_user($ugroup)
	{
		$condition = Account::get_condition_by_group($ugroup);
		if($condition == false)
			return false;
		$sql = "SELECT COUNT(*) AS num FROM users WHERE 1 = 1 AND {$condition}";
		$r = DB::sql($sql);

		return $r[0]["num"];
	}

	static function exists($uname){
		if(trim($uname) == ''){
			return -1;
		}
		$r = DB::sql('SELECT COUNT(*) AS num FROM users WHERE uname = :uname', array(':uname' => $uname));
		return $r[0]["num"];
	}

	static function valid($uname, $upass){
		$uname = trim($uname);
		$upass = trim($upass);

		if(empty($uname) || empty($upass)){
			return false;
		}

		$r = DB::sql('SELECT * FROM users WHERE uname = :uname AND upass = :upass', array(
			':uname' => $uname, ':upass' => $upass
		));

		if( count($r) > 0 ){
			return $r[0];
		}else{
			return false;
		}
	}

	static function add($uname, $upass, $ugroup){
		$uname = trim($uname);
		$upass = trim($upass);
		$ugroup = trim($ugroup);

		if(empty($uname) || empty($upass) || empty($ugroup)){
			return false;
		}

		$r = DB::sql('INSERT INTO users (uname,upass,ugroup) VALUES (:uname,:upass,:ugroup)', array(
			':uname' => $uname, ':upass' => $upass, ':ugroup' => $ugroup
		));
		if( $r == 1 )
			return true;
		else	
			return false;
		
	}
	
	static function del($uid){
		$uid = trim($uid);

		if(empty($uid) || $uid == 1)  //uid = 1 means(root)
			return false;

		$r = DB::sql('DELETE FROM users WHERE uid = :uid', array(
			':uid' => $uid
		));

		if( $r == 1)
			return true;
		else
			return false;
	}
	
	static function search($uname)
	{
		$r = DB::sql('SELECT * FROM users WHERE uname = :uname', array(
			':uname' => $uname));

		if(count($r) > 0)
			return 1;
		else
			return false;
	}

	static function reset_pass($uid, $upass)
	{
		$r = DB::sql('UPDATE users SET upass = :upass WHERE uid = :uid', array(
			':uid' => $uid, ':upass' => $upass
		));

		if($r >= 0)
			return true;
		else
			return false;
	}
	
	static function reset_group($uid, $ugroup)
	{
		$r = DB::sql('UPDATE users SET ugroup = :ugroup WHERE uid = :uid', array(
			':uid' => $uid, ':ugroup' => $ugroup
		));

		if($r >= 0)
			return true;
		else
			return false;
	}
	
	static function login($user){
		setcookie('se_user_id', $user['uid']);
		setcookie('se_user_name', $user['uname']);
		setcookie('se_user_group', $user['ugroup']);
		setcookie('se_user_token', self::generate_login_token($user['uid']));
	}

	static function logout(){
		setcookie('se_user_id', '', time() - 86400);
		setcookie('se_user_name',  '', time() - 86400);
		setcookie('se_user_group',  '', time() - 86400);
		setcookie('se_user_token',  '', time() - 86400);
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

	static function the_user_id(){
		return F3::get('COOKIE.se_user_id');
	}
	static function the_user_name(){
		return F3::get('COOKIE.se_user_name');
	}
	static function the_user_group(){
		$group = F3::get('COOKIE.se_user_group');
		if($group !== null) return $group;
		if(self::is_login()) return self::get_user_group();
		else return 100;
	}

	static function get_user_group(){
		$uid = self::the_user_id();
		$r = DB::sql('SELECT ugroup FROM users WHERE uid = :uid', array(
			':uid' => $uid));
		if(count($r) != 0) return $r[0]['ugroup'];
		else return 100;
	}

	static function generate_login_token($uid){
		return md5( $uid . F3::get('TOKEN_SALT') );
	}

	static function validate_login_token($uid, $token){
		$valid = self::generate_login_token($uid);

		return $token == $valid;
	}
};

?>
