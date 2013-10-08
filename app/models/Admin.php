<?php

class Admin{
	
	static function is_rest_signed(){
		if (User::is_admin()!==false){
			$rida = F3::get("COOKIE");
			$rid = $rida['se_user_admin'];
			if($rid == 0)
				return false;
			else
				return $rid;
		}else
			return false;
	}

}
