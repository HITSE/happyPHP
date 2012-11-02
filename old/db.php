<?php
class DataBase{
	
	var $querynum = 0;	//当前页面进程查询数据库的次数
	var $dblink = false;	//数据库链接资源
	var $last_query = '';
	var $insert_id = 0;


	/**
	 * 构造函数，链接数据库
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		//根据数据库配置文件中的信息连接数据库
		//define(MYSQL_HOST,'localhost');
		//define(MYSQL_USER,'root');
		//define(MYSQL_PWD,'vpcm');
		//define(MYSQL_DB,'happy');
		//define(MYSQL_CHARSET,'utf8');

		//$this->dblink = mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PWD);
		$this->dblink = mysql_connect('localhost', 'root', 'vpcm');

		$a = mysql_select_db('happy', $this->dblink);
		//$a = mysql_query("use happy;");

		//var_dump($a);
		mysql_query("SET NAMES utf8;");
		//echo mysql_error();
	}

	/*
	 * 执行sql命令，成功返回结果集，失败返回FALSE
	 * 
	 * @access protected
	 * @param  string $sql
	 * @return mixed
	 */
	public function query($sql){
		//var_dump($this->dblink);
		if($this->dblink)
		{
			$this->querynum++;
			//$sql = $this->escape($sql);

			//echo $sql;
			$r =  mysql_query($sql);

			$this->last_query = $sql;

			$id = mysql_insert_id();
			if($id)
				$this->insert_id = $id;

			return $r;
		}else{
			return false;
		}
	}

	public function get_last_query()
	{
		return $this->last_query;
	}

	
	/*
	 * 检查输入
	 *
	 * @access private
	 * @param  mixed
	 * @return mixed
	 */
	private function escape($v){
		if(is_array($v) || is_object($v)){
			$v = serialize($v);
		}
		if(get_magic_quotes_gpc()){
			$v = stripslashes($v);
		}
		return mysql_real_escape_string($v);
	}

	/*
	 * @brief : 从数据表table_name中取出一条记录，满足条件：字段名为field_name的字段，其值为value
	 */
	public function fetch($table_name, $field_name = NULL, $value = NULL)
	{
		$sql = "SELECT * FROM `$table_name` WHERE `$field_name` = '$value';";
		//echo $sql;

		$result = $this->query($sql);
		$row = mysql_fetch_array($result);

		$r = array();
		if($row)
		{
			foreach($row as $k => $v)
			{
				$t = @unserialize($v);
				if($v === 'b:0;' || $t !== false)
					$r[$k] = $t;
				else
					$r[$k] = $v;
			}
			return $r;
		}
		return $row;
	}

	/*
	 * @brief : 从数据表table_name中取出所有符合条件condition的记录数目
	 */
	public function get_num($table_name, $condition = NULL){
		if($condition == NULL)
			$sql = "SELECT count(*) FROM `$table_name`;";
		else
			$sql = "SELECT count(*) FROM `$table_name` WHERE $condition;";
		$result = $this->query($sql);

		$rs = array();
		if($result){
			while( ($row = mysql_fetch_array($result)) )	$rs[] = $row;
		}
		return $rs[0][0];
	}

		
	/*
	 * @brief : 从数据表table_name中取出所有符合条件condition的记录
	 */
	public function get($table_name, $condition = NULL){
		if($condition == NULL)
			$sql = "SELECT * FROM `$table_name`;";
		else
			$sql = "SELECT * FROM `$table_name` WHERE $condition;";
		//echo $sql;
		$result = $this->query($sql);
		//var_dump($result);

		$rs = array();
		if($result){
			while( ($row = mysql_fetch_array($result)) )	$rs[] = $row;
		}
		return $rs;
	}

	/*
	 * @brief : 向数据表table_name中插入一条记录，data是一个关联数组，键名为字段名，值为字段的值
	 */
	public function insert($table_name, $data){
		$q="INSERT INTO `".$table_name."` ";
		$v=''; $n='';
	
		foreach($data as $key=>$val)
	       	{
			$n.="`$key`, ";
			if(strtolower($val)=='null') $v.="NULL, ";
			elseif(strtolower($val)=='now()') $v.="NOW(), ";
			else $v.= "'".$this->escape($val)."', ";
		}
	
		$q .= "(". rtrim($n, ', ') .") VALUES (". rtrim($v, ', ') .");";
	
		if($this->query($q)){
			$this->insert_id = mysql_insert_id();
			return $this->insert_id;
		}
		else return false;
	}

	private function insert_id()
	{
		return $this->insert_id;
	}

	/*
	 * @brief : 更新数据库条目
	 * @desc : 更新数据表table_name中的id为id_value的记录，data是一个关联数组，键名为字段名，值为字段的值
	 * @return true or false
	 */
	public function update($table,$idname, $id, $data)
	{
		$sql = "UPDATE `$table` SET ";

		if(is_array($data) && count($data) > 0):
			foreach($data as $field => $value):
				$sql .= "`$field` = '". $this->escape($value) ."',";
			endforeach;

			$sql = rtrim($sql, ', ') . " WHERE `{$idname}` = '$id'";

			//echo $sql;

			return $this->query($sql);
		else:
			return false;
		endif;

	}
	/*
	 * @brief : 删除$table中id为$id的行
	 */
	public function delete($table, $id)
	{
		$sql = "DELETE FROM `$table` WHERE `id` = '$id'";

		return $this->query($sql);
	}
	//具有可变参数个数的函数，类似于sprintf，fsql定义了数据格式，v1, v2等变量定义了要替换的值，然后将替换后的字符串作为数据库查询进行执行
	const NUM = 'd';
	const STR = 's';
	const RAW = 'r';
	const ESC = '%';

	function queryf()
	{
		$args = func_get_args();

		if( ($argCount = count($args)) == 0 )
			return false;

		$format = $args[0];
		$arg_pos = 1;
		$esc_pos = false;
		$v_pos = 0;

		$sql = '';

		while(true)
		{
			$esc_pos = strpos($format, CDB::ESC, $v_pos);
			if($esc_pos === false)
			{
				$sql .= substr($format, $v_pos);
				break;
			}

			$sql .= substr($format, $v_pos, $esc_pos - $v_pos);

			$esc_pos++;
			$v_pos = $esc_pos + 1;

			if($esc_pos == strlen($format))
			{// % 后面没有类型字符
				return false;
			}

			$v_char = $format{$esc_pos};

			if($v_char != CDB::ESC)
			{
				if($argCount <= $arg_pos)
				{// 参数个数不够
					return false;
				}
				$arg = $args[$arg_pos++];
			}

			switch($v_char){
			case CDB::NUM:
				$sql .= doubleval($arg);
				break;
			case CDB::STR:
				$sql .= $this->escape($arg);
				break;
			case CDB::RAW:
				$sql .= $arg;
				break;
			case CDB::ESC:
				$sql .= CDB::ESC;
				break;
			default: //非法的符号
				return false;
			}
		}

		$rs = $this->query($sql);

		if(is_bool($rs))
		{
			return $rs;
		}
		else
		{
			$r = array();
			while( ($row = mysql_fetch_array($rs)) )	$r[] = $row;

			return $r;
		}
	}
	
	//关闭链接
	public function close(){
		return mysql_close();
	}
}
?>
