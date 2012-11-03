<?php

class Table{

	static function getTable($rid, $num){
		$sql = "SELECT * FROM `table` WHERE rid = $rid ORDER BY capacity DESC";
		//echo $sql;
		$r = DB::sql($sql);
		$tmp = -1;
		foreach($r as $v){
			if($v['capacity'] < $num)
				break;
			$tmp = $v['capacity'];
		}
		return $tmp;
	}
}
