<?php

class CustomerController{

	function __construct(){
		if(User::is_login()){
			F3::set("login", "true");
		}else{
			F3::reroute('/login');
		}
	}

	function addQueue($rid){
	}

	function showRestaurantDetail(){
		$id = F3::get("GET.id");
		$r = Restaurant::getDetail($id);
		F3::set("r", $r);
		echo Template::serve('user/detail.html');
	}

	function listAllRestaurant(){
		$all = Restaurant::getAllBasicInfo();
		F3::set("all",$all);
		//$pagination = Sys::pagination(
		echo Template::serve('user/listall.html');
	}


}
