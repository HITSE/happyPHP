<?php

define(EACH_ONE_TIME, 10);

require_once("db.php");

$db = new DataBase();


$r = $db->fetch('restaurant', 'rid', $_GET['rid']);

$num = $db->get_num('queue', "rid = {$_GET['rid']} and status = 'queuing'");

$alltime = EACH_ONE_TIME * $num;

$hour = ceil($alltime / 60);
$min = $alltime % 60;

$time = $hour."h ".$min."min";

$data = array(
	'name' => $r['rname'],
	'num' => $num,
	'time' => $time,
	'address' => $r['raddr'],
	'phone' => $r['rphone'],
	'describe' => $r['rdescribe']
);

$a = array($data);

echo json_encode($a);

?>
