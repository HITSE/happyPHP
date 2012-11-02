<?php
require 'db.php';

$db = new DataBase();

$arrive_time = time();

$db->update('queue', 'qid', $_GET['qid'], array('status'=>'serveing', 'arrive_time'=>$arrive_time));

$db->close();

$url = "http://192.168.17.16/se/restaurant.php?rid={$_GET['rid']}";

header("Location: {$url}");

?>
