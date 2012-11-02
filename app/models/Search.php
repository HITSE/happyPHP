<?php

class Search{

	static function keyword($keys) 
	{
		foreach($keys as $key)
			if(!empty($key)) {
				$r = Search::search_all($key);
				if(!isset($all))
					$all = $r;
				else
					$all= array_intersect($all,$r);  //所有结果取交集
			}

		return $all;
	}

	static function search_all($str) //在所有表字段匹配关键字
	{
		//在device表里搜索关键字
		$sql1 = "SELECT DISTINCT did FROM device 
				LEFT JOIN country ON device.dcountry = country.id
				LEFT JOIN basic_category AS cat1 ON device.[1st_category] = cat1.id
				LEFT JOIN basic_category AS cat2 ON device.[2nd_category] = cat2.id
				LEFT JOIN basic_category AS cat3 ON device.[3rd_category] = cat3.id 
				WHERE 
				upper(dname) LIKE upper('%{$str}%') OR
				upper(dvendor) LIKE upper('%{$str}%') OR
				upper(duser) LIKE upper('%{$str}%') OR
				upper(dstatus) LIKE upper('%{$str}%') OR
				upper(doverview) LIKE upper('%{$str}%') OR
				upper(comment_year) LIKE upper('%{$str}%') OR
				upper(min_year) LIKE upper('%{$str}%') OR
				upper(max_year) LIKE upper('%{$str}%') OR
				upper(country.name) LIKE upper('%{$str}%') OR
				upper(cat1.name) LIKE upper('%{$str}%') OR
				upper(cat2.name) LIKE upper('%{$str}%') OR
				upper(cat3.name) LIKE upper('%{$str}%')";

		//在parameter_str表里查
		$sql2 = "SELECT DISTINCT device AS 'did' FROM parameter_str
				LEFT JOIN name ON parameter_str.name = name.id 
				LEFT JOIN category ON category.id = parameter_str.[1st_category]
				WHERE 
				upper(content) LIKE upper('%{$str}%') OR
				upper(comment) LIKE upper('%{$str}%') OR
				upper(name.name) LIKE upper('%{$str}%') OR
				upper(category.name) LIKE upper('%{$str}%')";

		//在parameter_num表里查
		$sql3 = "SELECT DISTINCT device AS 'did' FROM parameter_num
				LEFT JOIN name ON parameter_num.name = name.id
				LEFT JOIN unit ON parameter_num.unit = unit.id 
				LEFT JOIN category ON category.id =  parameter_num.[1st_category]
				WHERE 
				min LIKE '%{$str}%' OR
				max LIKE '%{$str}%' OR
				upper(comment) LIKE upper('%{$str}%') OR
				upper(unit.symbol) LIKE upper('%{$str}%') OR
				upper(category.name) LIKE upper('%{$str}%') OR
				upper(name.name) LIKE upper('%{$str}%')";

		$sql = $sql1. " UNION " .$sql2. " UNION " .$sql3;
		$r = DB::sql($sql);

		return Sys::two_into_one($r);
	}

	static function advance($t)
	{
			switch($t['item'])
			{
				case 'all':
					$r = empty($t['str'])? self::all() : self::keyword(explode(' ', $t['str']));
					break;
				case 'title':
					$r = Search::search_str('device','dname',$t['str']);
					break;
				case 'user':
					$r = Search::search_str('device','duser',$t['str']);
					break;
				case 'vendor':
					$r = Search::search_str('device','dvendor',$t['str']);
					break;
				case 'status':
					$r = Search::search_str('device','dstatus',$t['str']);
					break;
				case 'overview':
					$r = Search::search_str('device','doverview',$t['str']);
					break;
				case 'country':
					$r = Search::search_select('device','dcountry',$t['country']);
					break;
				case 'category1':
					$r = Search::search_select('device','[1st_category]',$t['1st_category']);
					break;
				case 'category2':
					$r = Search::search_select('device','[2nd_category]',$t['2nd_category']);
					break;
				case 'category3':
					$r = Search::search_select('device','[3rd_category]',$t['3rd_category']);
					break;
				case 'year':
					$r = Search::search_year($t['min_year'],$t['max_year']);
					break;
				case 'ablity':
					$r = Search::search_parameter($t);
					break;
				default:
					return FALSE;
					break;
			}
			return $r;
	}

	static function all(){
		$r = DB::sql("SELECT did FROM device WHERE 1 = 1");
		return Sys::two_into_one($r);
	}

	static function search_str($table,$field,$str)  //字符串匹配
	{
		if(empty($str))
			return FALSE;

		if($table == 'device')
			$aim = 'did';
		else
			$aim = 'device';
	
		$sql = "SELECT DISTINCT {$aim} AS 'did' FROM  {$table} WHERE  upper({$field}) LIKE  upper('%{$str}%')";
		$r = DB::sql($sql);

		return Sys::two_into_one($r);
	}

	static function search_select($table,$field,$id)  //根据下拉列表的选择搜索
	{
		if(empty($id))
			return FALSE;

		if($table == 'device')
			$aim = 'did';
		else
			$aim = 'device';

		$sql = "SELECT DISTINCT {$aim} AS 'did' FROM  {$table} WHERE {$field} = {$id}";
		$r = DB::sql($sql);

		return Sys::two_into_one($r);
	}

	static function search_year($min,$max)  //年代检索
	{
		if(!preg_match("/^\d{4}$/", $min) || !preg_match("/^\d{4}$/", $max) || $max < $min)
			return FALSE;

		$sql = "SELECT did FROM device WHERE 
				min_year >= {$min} AND min_year <= {$max} OR 
				max_year >= {$min} AND max_year <= {$max} OR
				max_year >= {$max} AND min_year <= {$min}";
		$r = DB::sql($sql);

		return Sys::two_into_one($r);
	}

	static function search_parameter($t)
	{
		if(empty($t['pname']))  //没有选择属性 则筛选出只包含性能参数类的所有
		{
			if($t['ablity'] == -1)
				return FALSE;

			$sql = "SELECT DISTINCT device AS 'did' FROM  parameter_str WHERE  [1st_category] = {$t['ablity']} UNION 
					SELECT DISTINCT device AS 'did' FROM  parameter_num WHERE  [1st_category] = {$t['ablity']}";
			$r = DB::sql($sql);

			return Sys::two_into_one($r);
		}

		//选择了属性 但是没有输入查询的文本内容或者数字 则筛选出含有此属性的所有
		if(empty($t['pstr']) && empty($t['min']) && empty($t['max'])) 
		{
			if(empty($t['name']))
				return FALSE;

			if($t['ablity'] == -1) //类别为所有类别
				$sql = "SELECT DISTINCT device AS 'did' FROM parameter_str WHERE name = {$t['name']} UNION 
						SELECT DISTINCT device AS 'did' FROM parameter_num WHERE name = {$t['name']}";
			else
				$sql = "SELECT DISTINCT device AS 'did' FROM parameter_str WHERE  [1st_category] = {$t['ablity']} AND name = {$t['name']} UNION 
						SELECT DISTINCT device AS 'did' FROM parameter_num WHERE  [1st_category] = {$t['ablity']} AND name = {$t['name']}";
			$r = DB::sql($sql);

			return Sys::two_into_one($r);
		}

		if($t['search_type'] == 'str')  //根据文本搜索
		{
			if(empty($t['pstr']))
				return FALSE;

			if($t['ablity'] == -1) //类别为所有类别
				$sql = "SELECT DISTINCT device AS 'did' FROM  parameter_str WHERE  
					name = {$t['name']} AND 
					upper(content) LIKE  upper('%{$t['pstr']}%')";
			else
				$sql = "SELECT DISTINCT device AS 'did' FROM  parameter_str WHERE  
					name = {$t['name']} AND  [1st_category] = {$t['ablity']} AND 
					upper(content) LIKE  upper('%{$t['pstr']}%')";

			$r = DB::sql($sql);

			return Sys::two_into_one($r);
		}
		else   //根据数字 最大 最小 条件搜索
		{
			if(empty($t['min']) && empty($t['max']))  //min和max都为空
				return FALSE;

			$t = Sys::convert_num($t);	
			$min = $t['min'];
			$max = $t['max'];  //转换后的区间

			if($t['search_type'] == "num_yange")  //区间严格匹配 子集
				$sql = "SELECT DISTINCT device AS 'did' FROM parameter_num WHERE 
					min >= {$min} AND max <= {$max} AND name = {$t['name']}";
			else  //模糊  有交集即可
				$sql = "SELECT DISTINCT device AS 'did' FROM parameter_num WHERE 
					(
						(min >= {$min} AND min <= {$max}) OR 
						(max >= {$min} AND max <= {$max}) OR
						(max >= {$max} AND min <= {$min}) 
					) AND name = {$t['name']}";

			if($t['ablity'] != -1) //类别不为所有类别
				$sql .= " AND [1st_category] = {$t['ablity']} ;";

			$r = DB::sql($sql);
			return Sys::two_into_one($r);
		}
	}

};

?>
