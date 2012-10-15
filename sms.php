<?php
require 'PHPFetion.php';
require 'db.php';

$suppose_min= 30;

$suppose_arrive_time = time() + $suppose_min * 60;

//$form_date = date($suppose_arrive_time, ",

$phone = '18245151502';

$content = "感谢您参加<{$_GET['rname']}>排队，请您于{$suppose_min}分钟内到达餐厅用餐，祝您用餐愉快！";

$fetion = new PHPFetion('13000000000', '12345678');// 手机号、飞信密码
$result =  $fetion->send($phone, $content);// 接收人手机号、飞信内容

$db = new DataBase();

$db->update('queue', 'qid', $_GET['qid'], array('status'=>'smsed', 'suppose_arrive_time'=>$suppose_arrive_time));

$db->close();

$url = "http://192.168.17.16/se/restaurant.php?rid={$_GET['rid']}";

header("Location: {$url}");

?>
