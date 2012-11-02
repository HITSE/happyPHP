<?php

class Device{

	static function get_basic_detail($did, $show_country = true, $simple = false)
	{
		if($show_country)
			$sql = "SELECT *,name AS dcountry, '未知' AS dyear FROM device,country WHERE did IN ({$did}) AND dcountry = country.id";
		else if($simple)
			$sql = "SELECT did,dname, duser, dvendor,min_year, max_year,comment_year, name AS dcountry, '未知' AS dyear FROM device,country 
			WHERE did IN ({$did}) AND dcountry = country.id";
		else
			$sql = "SELECT * FROM device WHERE did = {$did}";

		//echo $sql;

		$r = DB::sql($sql);

		if(!empty($r)){
			return $show_country?$r:$r[0];
		}else{
			return FALSE;  //设备不存在
		}
	}


	static function get_parameter_by_category($did, $cid)
	{
		$sql = "SELECT * ,'num' as parameter_type FROM parameter_num as num, name
				WHERE num.device = :did AND num.[1st_category] = :cid AND name.id = num.name";
		$sql = Sys::format_sql($sql);
		$r = DB::sql($sql,array(':did' => $did, ':cid' => $cid));

		foreach($r as &$d)
			$d = Sys::convert_num_show($d);

		$sql = "SELECT * ,'str' as parameter_type FROM parameter_str as str, name
				WHERE str.device = :did AND str.[1st_category] = :cid AND name.id = str.name";
		$sql = Sys::format_sql($sql);
		$s = DB::sql($sql,array(':did' => $did, ':cid' => $cid));

		$t = self::get_id_name('category', "id = {$cid}");

		$all = array();
		$all['all']= array_merge($r, $s);
		$all['name'] = $t[$cid];
		//Code::dump($all);
		return $all;
	}


	static function get_num_parameter_detail($did)
	{
		$sql = " SELECT category.id, category.name AS cn, name.name AS nn,
					pnum.min, pnum.unit, pnum.max, type, num_type.show,
					pnum.comment, 'num' AS num_str, symbol
				FROM  device , category,  parameter_num AS pnum, name, unit, num_type
				WHERE   did = :did
					AND pnum.device = did
					AND pnum.[1st_category] = category.id
					AND pnum.name = name.id
					AND pnum.type = num_type.id
					AND pnum.unit = unit.id
				;";
		$sql = Sys::format_sql($sql);
		$r = DB::sql($sql,array(':did' => $did));

		//foreach($r as &$d) 
			//$d = Sys::convert_num($d, 'show');

		return $r;
	}

	static function get_str_parameter_detail($did)
	{
		$sql = " SELECT category.id, category.name AS cn, name.name AS nn,
					pstr.content ,  pstr.comment , 'str' AS num_str
				FROM  device , category,  parameter_str AS pstr, name
				WHERE did = :did
					AND pstr.device = did
					AND pstr.[1st_category] = category.id
					AND pstr.name = name.id
				;";
		$sql = Sys::format_sql($sql);
		return DB::sql($sql,array(':did' => $did));
	}

	static function get_parameter_detail($did)
	{
		$num = self::get_num_parameter_detail($did);
		$str = self::get_str_parameter_detail($did);

		$all = array_merge($num, $str);

		return Sys::format_show_parameter($all);
	}

	/**
	 * 从指定表中得到字段为id和name的结果数组
	 * country,category,unit
	 */
	static function get_id_name($table, $condition = '1 = 1') {
		$x = ($table == "unit")?"symbol AS name":"name";
		$r = DB::sql("SELECT id, {$x} FROM {$table} WHERE 1 = 1 AND {$condition} ORDER BY id ASC");
		$a = array();
		foreach($r as $key => $v)
			$a[$v['id']] = $v['name'];

		return $a;
	}


	static function add_basic($d)
	{
		if(!isset($d['3rd_category']))
			$d['3rd_category'] = 0;

		$value = array(
				':dname' => $d['dname'],
				':dvendor' => $d['dvendor'],
				':duser' => $d['duser'],
				':dcountry' => $d['dcountry'],
				':min_year' => $d['min_year'],
				':max_year' => $d['max_year'],
				':comment_year' => $d['comment_year'],
				':dstatus' => $d['dstatus'],
				':doverview' => $d['doverview'],
				':1st_category' => $d['1st_category'],
				':2nd_category' => $d['2nd_category'],
				':3rd_category' => $d['3rd_category']
			);

		if($d['did'] == '') {// insert
			//DB::sql("set identity_insert device ON");
			$sql = "INSERT INTO device
					(dname, dvendor, dcountry, min_year, max_year, comment_year,
					duser, dstatus, doverview, [1st_category], [2nd_category], [3rd_category])
				VALUES
					(:dname, :dvendor, :dcountry, :min_year, :max_year, :comment_year,
					:duser, :dstatus, :doverview, :1st_category, :2nd_category, :3rd_category)";

		}else{ // update
			$value[':did'] = $d['did'];
			$sql = "SET IDENTITY_INSERT device ON;
					INSERT INTO device
						(did, dname, dvendor, dcountry, min_year, max_year, comment_year,
						duser, dstatus, doverview, [1st_category], [2nd_category], [3rd_category])
					VALUES
						(:did, :dname, :dvendor, :dcountry, :min_year, :max_year, :comment_year,
						:duser, :dstatus, :doverview, :1st_category, :2nd_category, :3rd_category);
					SET IDENTITY_INSERT device OFF;";
		}

		$sql = Sys::format_sql($sql);
		if(DB::sql($sql, $value) == 1){
			return true;
		} else{
			return false;
		}
	}

	static function add_parameter($num, $str,$did = null, $cid = null)
	{
		$sql = array();
		if($cid != null){
			$sql[] = "DELETE FROM parameter_num WHERE device = '{$did}' AND [1st_category] = {$cid}";
			$sql[] = "DELETE FROM parameter_str WHERE device = '{$did}' AND [1st_category] = {$cid}";
		}

		foreach($num as &$d)
		{
			$d = Sys::convert_num($d);
			//Code::dump($d);
			$sql[] = "INSERT INTO parameter_num 
				(device, name, min, max, type, unit, comment, [1st_category], [2nd_category])
				VALUES ('{$d['did']}','{$d['name']}','{$d['min']}','{$d['max']}','{$d['type']}',
					'{$d['unit']}','{$d['comment']}','{$d['1st_category']}','{$d['2nd_category']}')";
		}
		foreach($str as $d)
		{
			$sql[] = "INSERT INTO parameter_str
 				(device, name, content, comment, [1st_category], [2nd_category]) VALUES
				('{$d['did']}','{$d['name']}','{$d['content']}','{$d['comment']}','{$d['1st_category']}','{$d['2nd_category']}')";
		}

		//Code::dump($sql);

		$sql = Sys::format_sql($sql);
		if(DB::sql($sql) == 1)
			return true;
		else
			return false;
	}

	static function del_parameter($did, $cid = '')
	{
		$con = $cid == ""? "1 = 1" : "[1st_category] = {$cid}";
		
		$sql[] = "DELETE FROM parameter_num WHERE device = '{$did}' AND {$con}";
		$sql[] = "DELETE FROM parameter_str WHERE device = '{$did}' AND {$con}";
		$a = DB::sql($sql);
		if($a == 0)
			return true;
		else
			return false;
	}

	static function del_basic($did)
	{
		$sql = "DELETE FROM device WHERE did = :did";

		if(DB::sql($sql,array('did' => $did)) > 0)
			return true;
		else
			return false;
	}

	static function del($did){
		if(self::del_parameter($did) && self::del_basic($did))
			return true;
		else
			return false;
	}
	
	static function is_name_exist($dname)  //查看数据库是否存在$dname名称的设备
	{
		$r = DB::sql('SELECT * FROM device WHERE dname = :dname',
			array('dname' => $dname));

		if(empty($r))   //不存在$dname设备 返回false
			return false;
		else
			return $r;  //返回名称为$dname的基本信息的关联数组
	}

}
