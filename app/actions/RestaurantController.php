<?php

class RestaurantController{

	function __construct(){
		if(User::is_login()){
			F3::set("login", "true");
		}else{
			F3::reroute('/login');
		}

		if(User::is_admin() === false){
			F3::reroute('/');
		}
	}

	function listQueue(){
		//$id = F3::get("GET.id");
		$all = array(
			array(
				"capacity" => 4,
				"customer" => array(
					array(
						"num" => 2,
						"time" => "14:30:23",
						"phone" => "18009872634",
						"status" => "queuing"
					),
					array(
						"num" => 4,
						"time" => "14:36:07",
						"phone" => "13234204938",
						"status" => "queuing"
					),
					array(
						"num" => 4,
						"time" => "14:42:42",
						"phone" => "13909234329",
						"status" => "queuing"
					)
				)
			),
			array(
				"capacity" => 6,
				"customer" => array(
					array(
						"num" => 5,
						"time" => "14:40:23",
						"phone" => "13409832637",
						"status" => "smsed"
					),
					array(
						"num" => 6,
						"time" => "14:41:23",
						"phone" => "13098302903",
						"status" => "queuing"
					)
				)
			)
		);

		F3::set("all", $all);

		echo Template::serve('admin/listqueue.html');
	}

	function notifyUser(){
		$user = F3::get("GET.id");
		// sms
		
		// update status
		//
	}

	function customerArrive(){
		$user = F3::get("GET.id");
	}

	function signUpRestaurant(){
	}

}
