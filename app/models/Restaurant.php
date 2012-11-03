<?php

class Restaurant{
	function __construct(){
	}

	static function getDetail($id) {
		$r = DB::sql('SELECT * FROM restaurant WHERE id = :id', array(':id' => $id));
		if (count($r) > 0){
			$r = $r[0];
			$r['num'] = Queue::getNum($id);
			$r['time'] = $r['num'] * 5;
			$r['time'].= " min";
			//Code::dump($r);
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

