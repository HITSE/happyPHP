<?php

class CustomerController{

	function __construct(){
		if(User::is_login())
			F3::set("login", "true");
		//}else{
			//F3::reroute('/login');
		//}
	}


	function addQueue(){
		$rid = F3::get("GET.rid");
		$phone = F3::get("GET.phone");
		$num = F3::get("GET.num");
		Queue::addItem($rid, $phone, $num);
		F3::reroute('/user/list');
	}

	function showRestaurantDetail(){
		$id = F3::get("GET.id");
		$r = Restaurant::getDetail($id);
		F3::set("r", $r);
		F3::set("phone", F3::get("COOKIE.se_user_name"));
		echo Template::serve('user/detail.html');
	}

	function listAllRestaurant(){
		$all = Restaurant::getAllBasicInfo();
		F3::set("all",$all);
		//Code::dump($all);
		//$pagination = Sys::pagination(
		echo Template::serve('user/listall.html');
	}



	function addQueueMobile(){
		$rid = F3::get("GET.rid");
		$phone = F3::get("GET.phone");
		$num = F3::get("GET.num");
		Queue::addItem($rid, $phone, $num);
	}

	function listAllMobile(){
		$all = Restaurant::getAllBasicInfo();
		F3::set("all",$all);
		//Code::dump($all);
		echo json_encode($all);
	}

	function showDetailMobile(){
		$id = F3::get("GET.id");
		$r = Restaurant::getDetail($id);
		$a = array($r);
		echo json_encode($a);
	}


}
