<?php

require_once("db.php");

$db = new DataBase();

$data = array(
	'uid' => 0,
	'rid' => $_GET['rid'],
	'unum' => $_GET['num'],
	'uphone' => $_GET['phone']
);


$db->insert('queue', $data);

?>
