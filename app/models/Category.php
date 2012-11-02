<?php
class Category
{
	static function get_by_id($id,$table)
	{

		$sql = "SELECT * FROM {$table} WHERE id = {$id}";
		$r = DB::sql($sql);

		if(!empty($r))
			return $r[0];
		else
			return FALSE;
	}

	static function get_all_parent_category()
	{
		$sql = "SELECT id ,name,py FROM category";
		$r = DB::sql($sql);
		return $r;
	}

	static function get_name_by_parent($parent)
	{
		if($parent == -1)
			$sql = "SELECT DISTINCT id ,name,py FROM name";
		else
			$sql = "SELECT DISTINCT child AS id ,name,py FROM category_inherit, name WHERE parent = '{$parent}' AND child = id";
		$r = DB::sql($sql);
		return $r;
	}

	static function is_name_exist($name,$table)
	{
		$sql = "SELECT * FROM {$table} WHERE name = :name";
		$r = DB::sql($sql, array(':name' => $name));
		
		if(empty($r))
			return FALSE;
		else
			return $r[0]['id'];
	}

	static function add_name_py($name, $py, $table, $id = false)
	{
		if($id === false){
			$sql = "INSERT INTO {$table} (name, py) VALUES (:name, :py)";
			$r = DB::sql($sql, array(':name' => $name, ':py' => $py));
		}else{
			$sql = "INSERT INTO {$table} (id, name, py) VALUES (:id, :name, :py)";
			$r = DB::sql($sql, array(':id' => $id, ':name' => $name, ':py' => $py));
		}

		if($r == 1)
			return true;
		else
			return false;
	}
	
	static function add_inherit($parent, $child)
	{
		$sql = "INSERT INTO category_inherit (parent, child) VALUES (:parent, :child)";
		$r = DB::sql($sql, array(':parent' => $parent, ':child' => $child));

		if($r == 1)
			return true;
		else
			return false;
	}

	static function remove_all_inherit($parent)
	{
		$sql = "DELETE FROM category_inherit WHERE parent = :parent";
		DB::sql($sql,array(':parent' => $parent));
	}

	static function add_child($data,$parent)
	{
		foreach(array_keys($data) as $keys)
		{
			$k = explode('-',$keys);

			if($k[0] == "newchildname")
			{
				$child = self::is_name_exist($data[$keys],'name');
				if($child === FALSE)
				{
					self::add_name_py($data[$keys], $data['newchildpy-'.$k[1]], 'name');
					$child = DB::get_insert_id();
					self::add_inherit($parent, $child);
				}
				else
					self::add_inherit($parent, $child);
			}
			else if($k[0] == "oldchildname")
				self::add_inherit($parent, $data['oldchildid-'.$k[1]]);
		}
		return TRUE;
	}

	static function add($data, $old_id = false)
	{
		//Code::dump($data);
		if(self::add_name_py($data['name'], $data['pysx'], 'category', $old_id)){
			if($old_id === false) {
				$id = DB::get_insert_id();
				if(self::add_child($data, $id))
					return "add";
			} else {
				if(self::add_child($data, $old_id))
					return "list";
			}
		}
		else return FALSE;
	}

	static function list_all()
	{
		$sql = "SELECT * FROM category";
		$r = DB::sql($sql);
	}

	static function del($id,$table,$role)
	{
		$sql = "DELETE FROM {$table} WHERE id = '{$id}'";
		DB::sql($sql);
		
		$sql = "DELETE FROM category_inherit WHERE {$role} = '{$id}'";
		DB::sql($sql);
	}

	static function is_category_used($category)
	{
		$sql = "SELECT * FROM  parameter_num WHERE  [1st_category] = {$category}";
		$sql = Sys::format_sql($sql);
		$r = DB::sql($sql);

		if(!empty($r))
			return TRUE;

		$sql = "SELECT * FROM  parameter_str WHERE  [1st_category] = {$category}";
		$sql = Sys::format_sql($sql);
		$r = DB::sql($sql);

		if(!empty($r))
			return TRUE;

		return FALSE;
	}

}

?>
