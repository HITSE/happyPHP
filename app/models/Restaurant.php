<?php

class Restaurant{
	function __construct(){
	}

	static function getDetail($id) {
		$r = DB::sql('select * from restaurant where id = :id', array(':id' => $id));
		if (count($r) > 0){
			$r = $r[0];
			$rs = DB::sql('SELECT COUNT(*) FROM queue WHERE rid = :rid AND status = "queueing"', 
				array(':rid' => $r['id'])
			);
			$r['num'] = $rs[0]["COUNT(*)"];
			$r['time'] = $r['num'] * 5;
			$r['time'].= " min";
			return $r;
		} else{
			return false;
		}
	}

	static function getBasicInfo($id) {
		$r = array();
		$temp = getDetail($id);
		if($temp != false){
			$r['id'] = $temp['id'];
			$r['name'] = $temp['name'];
			$r['num'] = $temp['num'];
			return $r;
		}
		return false;
	}

	static function getAllBasicInfo(){
		$a = array();
		$r = DB::sql('SELECT id FROM restaurant WHERE 1');
		$num = count($r);
		foreach($r as $v)
			$a[] = self::getDetail($v['id']);
		return $a;
	}
}

