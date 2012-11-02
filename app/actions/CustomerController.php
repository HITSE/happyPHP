<?php

class CustomerController{

	function __construct(){
		if(User::is_login()){
			F3::set("login", "true");
		}else{
			F3::reroute('/login');
		}
	}

	function showRestaurantDetail(){
		$id = F3::get("GET.id");
		$r = array(
			"id" => 2,
			"name" => "永福小吃",
			"describe" => "very good",
			"num" => 2,
			"time" => "20min",
			"phone" => "18009872637",
			"addr" => "where"
		);
		F3::set("r", $r);
		echo Template::serve('user/detail.html');
	}

	function listAllRestaurant(){
		$all = array(
			array(
				"id" => 1,
				"name" => "test",
				"num" => 2,
				"time" => "20min"
			),
			array(
				"id" => 1,
				"name" => "kjak",
				"num" => 4,
				"time" => "20min"
			)
		);

		F3::set("all",$all);
		//$pagination = Sys::pagination(
		echo Template::serve('user/listall.html');
	}


}
