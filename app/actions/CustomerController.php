<?php

	function sort_people($a, $b){
		if($a['num'] == $b['num']) return 0;
		//return ($a['time_t'] > $b['time_t'])? -1 : 1;
		return ($a['num'] > $b['num'])? 1 : -1;
	}

	function sort_time($a, $b){
		if($a['time_t'] == $b['time_t']) return 0;
		//return ($a['time_t'] > $b['time_t'])? -1 : 1;
		return ($a['time_t'] > $b['time_t'])? 1 : -1;
	}

class CustomerController{

	function __construct(){
		if(User::is_login()){
			F3::set("login", "true");
			F3::set("title", "我要排队");
		}
		//}else{
			//F3::reroute('/login');
		//}
		F3::set('route', 'user');
	}


	function addQueue(){
		$rid = F3::get("GET.rid");
		$phone = F3::get("GET.phone");
		$num = F3::get("GET.num");
		$r = Queue::addItem($rid, $phone, $num);
		Code::dump($r);
		$a = $r === true ? '1' : '0';
		F3::reroute("/user/list?a=$a");
	}

	function showRestaurantDetail(){
		$id = F3::get("GET.id");
		$r = Restaurant::getDetail($id);
		F3::set("r", $r);
		F3::set("rid", $id);
		F3::set("phone", F3::get("COOKIE.se_user_name"));
		echo Template::serve('user/detail.html');
	}

	function listAllRestaurant(){
		$all = Restaurant::getAllBasicInfo();
		$order = F3::get("GET.order");

		//Code::dump($all);
		if($order == "time")
			uasort($all, "sort_time");
		else if($order == "people")
			uasort($all, "sort_people");
		//Code::dump($all);
		$a = F3::get("GET.a");
		if($a === '1'){
			F3::set("has_submit", true);
			F3::set("success", true);
			F3::set("msg", "请等侯餐厅的短信就餐通知");
		}else if($a === '0'){
			F3::set("has_submit", true);
			F3::set("success", false);
			F3::set("msg", "您的就餐人数超过餐厅单桌最大可用餐人数，请与餐厅电话联系");
		}

		F3::set("all",$all);
		//$pagination = Sys::pagination(
		echo Template::serve('user/listall.html');
	}

	function mobileLogin(){
		$name = F3::get("GET.phone");
		$pwd = F3::get("GET.pwd");
		$r = array();
		if(User::valid($name, $pwd) !== false)
			$r['answer'] = 1;
		else
			$r['answer'] = 0;
		$t = array();
		$t[] = $r;
		echo json_encode($t);
	}

	function mobileSignUp(){
		$info = array();
		$info['name'] = F3::get('GET.phone');
		$info['pass'] = F3::get('GET.pwd');
		//$info['pass'] = F3::get('POST.upass'); //TODO check pass
		$info['phone'] = F3::get('GET.phone');
		$info['type'] = 0;
		$r = array();
		
		if(User::signUp($info) != -2)
			$r['answer'] = 1;
		else
			$r['answer'] = 0;
		$t = array();
		$t[] = $r;
		echo json_encode($t);
	}

	function addQueueMobile(){
		$rid = F3::get("GET.rid");
		$phone = F3::get("GET.phone");
		$num = F3::get("GET.num");
		$s = Queue::addItem($rid, $phone, $num);
		$r = array();
		if($s === true)
			$r['answer'] = 1;
		else
			$r['answer'] = 0;
		$t = array();
		$t[] = $r;
		echo json_encode($t);
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
