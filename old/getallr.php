<?php

require_once("db.php");

$db = new DataBase();

$r = $db->get('restaurant');



$data = array();
foreach($r as $v){
	$num = $db->get_num("queue", "rid = {$v['rid']} and status = 'queuing'");
	$data[] = array(
		'rid' => $v['rid'],
		'name' => $v['rname'],
		'num' => $num
	);
}

echo json_encode($data);

?>
