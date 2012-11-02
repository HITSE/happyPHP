<?php

class DataBase{

	static function para($str)
	{
		$arg = array(
			'sep' => 't@uX3am#', 'dbname' => F3::get('DBC.NAME'));

		return $arg[$str];
	}

	static function is_empty($tables)  //检查数据库的表是否都为空
	{
		foreach($tables as $table)
			$r = DB::sql("SELECT * FROM [{$table}]");
			if(!empty($r))
				return FALSE;  //只要有一个表不是空 就返回FALSE

		return TRUE;
	}

	static function del($database)
	{
		$tables = DataBase::list_tables($database);

		if(self::is_empty($tables) === TRUE)
			return false;  //数据库不存在,无法导出

		foreach ($tables as $table) 
		{
			$sql = "TRUNCATE TABLE [{$table}]";
			DB::sql($sql);
		}

		return true;  
	}

	static function backup($database,$filename = null)
	{
		$tables = DataBase::list_tables($database);
		
		if(self::is_empty($tables) === TRUE)
			return false;  //数据库不存在,无法导出

		if($filename == null)
		{
			$now_time = date("Y-m-d(H-i-s)");
			$filename = $now_time.'.sql';
		}

		if(F3::get('SYSTEM') == 'windows')
			$fp = fopen("db_bak\\$filename", 'w');
		else
			$fp = fopen("db_bak/$filename", 'w');

		foreach ($tables as $table) {
    		DataBase::dump_table($table, $fp, $database);
		}
		fclose($fp);
		return true;
	}

	static function export($database)
	{
		$now_time = date("Y-m-d(H-i-s)");
		$filename = $now_time.'.sql';

		if(self::backup($database,$filename) === false)
			return false;

		Header("Content-type:application/octet-stream;");
		Header("Content-Disposition:attachment;filename=".$filename);

		if(F3::get('SYSTEM') == 'windows')
		{
			readfile("db_bak\\$filename");
			unlink("db_bak\\$filename");
		}
		else
		{
			readfile("db_bak/$filename");
			unlink("db_bak/$filename");
		}
		return true;
	}

	static function import($database, $filename)
	{
		DataBase::del($database);
		if(F3::get('SYSTEM') == 'windows')
			$sql = file_get_contents("db_bak\\".$filename);
		else
			$sql = file_get_contents("db_bak/".$filename);
	 	$sqls = explode(DataBase::para('sep'),$sql);   
		$cnt = 0;	
		foreach($sqls as $sq)   
		{   
			if(trim($sq) != '')   
		    {   
				DB::sql($sq);
				$cnt++;   
			}
		}   
		if($cnt > 0)
			return true;
		else
			return false;
	}

	static function list_tables($database)  //返回数据库所有表名
	{
		if(F3::get('DBT') == "mysql")
			$sql = "SHOW TABLES FROM `{$database}`";
		else
			$sql = "select name from sysobjects where xtype='U'";

		$r = DB::sql($sql);

		$tables = array();
		foreach($r as $row)
			foreach($row as $tab)
				if($tab!= 'users')  //不导出users表
					$tables[] = $tab;

		return $tables;
	}
	
	static function fields_table($table, $database)   //得到表的所有字段名
	{
		$fields = Array(); 
		if(F3::get('DBT') == "mysql")
		{
			$sql = "SHOW COLUMNS FROM  `{$table}` FROM  `{$database}`";
			$r = DB::sql($sql);
			foreach($r as $col)
				$fields[] = $col['Field'];
		}
		else
		{
			$sql = "Select Name FROM SysColumns Where id=Object_Id('{$table}')";
			$r = DB::sql($sql);
				foreach($r as $cols)
					foreach($cols as $col)
						$fields[] = $col;
		}
		return $fields;
	}

	static function dump_table($table, $fp, $database)
	{
		$fields = self::fields_table($table,$database);	

		$sql = "SELECT * FROM [{$table}]";
		$r = DB::sql($sql);

		foreach($r as $t)
		{
			$sql1 = "INSERT INTO [{$table}] (";
			$sql2 = " VALUES (";
			foreach($fields as $f)
			{
				$sql1 .= "[{$f}] ,";
				$val = DB::quote($t[$f]);
				$sql2 .= "{$val} ,";
			}
			$sql1[strlen($sql1)- 1] = ")";
			$sql2[strlen($sql2) - 1] = ")";

			if($table == "category_inherit" || $table == "parameter_num" || $table == "parameter_str")
			    $sql = $sql1.$sql2.DataBase::para('sep');
			else
			    $sql = "SET IDENTITY_INSERT {$table} ON; ".$sql1.$sql2."; SET IDENTITY_INSERT {$table} OFF;".DataBase::para('sep');

			fwrite($fp, $sql);
		}
	}
	
}

?>
