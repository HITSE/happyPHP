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

		$rid = F3::get("COOKIE");
		//Code::dump($rid);
		$rid = $rid['se_user_admin'];
		if($rid == 0)
			F3::reroute("/admin/signup");

		$all = Queue::getAll($rid);
		F3::set("all", $all);
		echo Template::serve('admin/listqueue.html');
	}

	function notifyUser(){
		// phone
		$user = F3::get("GET.id");
		Queue::notify($user);
		F3::reroute("/admin");
	}

	function customerArrive(){
		$user = F3::get("GET.id");
		Queue::arrive($user);
		F3::reroute("/admin");
	}


	function showSignUpRestaurant(){
		echo Template::serve('admin/signup.html');
	}

	function signUp(){
		//$rid = F3::get("GET.rid");
		$a = array();
		$a['phone'] = F3::get("POST.phone");
		$a['name'] = F3::get("POST.name");
		$a['addr'] = F3::get("POST.addr");
		$a['describe'] = F3::get("POST.describe");

		$id = Queue::signUp($a);

		User::updateAdmin($id);

		$table = F3::get("POST.table");

		Table::setTable($id, $table);

		F3::reroute("/admin");

	}

}
