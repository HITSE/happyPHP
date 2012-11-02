<?php

date_default_timezone_set('Asia/Shanghai');

require __DIR__.'/app/lib/base.php';

F3::config('app/cfg/setup.cfg');
F3::config('app/cfg/routes.cfg');

try{
	if(F3::get('DBT') == 'mssql')
		$dsn = 'sqlsrv:Server='.F3::get('DBC.HOST').';database='.F3::get('DBC.NAME');
	else if(F3::get('DBT') == 'mysql')
		$dsn = 'mysql:host='.F3::get('DBC.HOST').';dbname='.F3::get('DBC.NAME');

	F3::set('DB', new DB($dsn, F3::get('DBC.USR'), F3::get('DBC.PWD')));

}catch(PDOException $e){
	echo $e.message;
	exit;
}

F3::run();

?>
