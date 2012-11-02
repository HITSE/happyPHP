
<?php
require_once("db.php");

if(!isset($_GET['rid']))
	$_GET['rid'] = 1;

$db = new DataBase();

//得到排队信息
$current = $db->get("queue", "(`status` = 'queuing' OR `status` = 'smsed') AND rid = {$_GET['rid']}");
//var_dump($current);

$wait_num = $db->get_num("queue", "rid = {$_GET['rid']} AND `status` = 'queuing'");

$r = $db->fetch("restaurant", "rid", $_GET['rid']);
$rname = $r['rname'];

$allr = $db->get("restaurant");

//发送过信息，但指定时间内未到达，则设置状态quited
$time = time();
$sql = "UPDATE `queue` SET `status` = 'quited' WHERE `suppose_arrive_time` < {$time} AND `status` = 'smsed';";
$db->query($sql);
//设置用餐完成的状态finshed
$time += 30 * 60;
$sql = "UPDATE `queue` SET `status` = 'finshed' WHERE `arrive_time` < {$time} AND `status` = 'serveing';";
$db->query($sql);


$db->close();

?>

<html>
<head>
	<meta charset="utf-8">
	<title>餐厅管理</title>
	<link href="css/bootstrap.css" rel="stylesheet">
	<link href="css/bootstrap-responsive.css" rel="stylesheet">
	<script src="js/jquery.js"></script>
	<script src="js/bootstrap.js"></script>
</head>
<body>

<div class="navbar navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container">
			<a class="brand" href="">HappyQueuing</a>
			<div class="nav-collapse collapse">
				<ul class="nav">
					<li><a href="index.php">首页</a></li>
					<li><a href="queue.php">我要排队</a></li>
					<li class="active"><a href="#">餐厅管理</a></li>
					<li><a href="about.php">关于我们</a></li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">选择餐厅<b class="caret"></b></a>
						<ul class="dropdown-menu">
						<?php foreach($allr as $v):?>
							<li><a href="restaurant?rid=<?=$v['rid']?>"><?=$v['rname']?></a></li>
						<?php endforeach;?>
						</ul>
					</li>
				</ul>



			</div>
		</div>
	</div>
</div>


<!-- content -->
<div class="container" style="margin-top:60px">
<div class="row">
	<div class="span6 offset3">
		<h2>当前排队情况<span>（等侯<?=$wait_num?>人）</span></h2>

<table class="table table-hover">

<thead>
<tr>
	<th>#</th>
	<th>手机号</th>
	<th>用餐人数</th>
	<th>排队时间</th>
	<th>操作</th>
</tr>
</thead>

<tbody>

<?php foreach($current as $v): ?>
	<tr>
		<td><?=$v['qid']?></td>
		<td><?=$v['uphone']?></td>
		<td><?=$v['unum']?></td>
		<td><?=$v['time']?></td>
		<td>
		<?php if($v['status'] == 'queuing'){?>
			<a class="btn btn-success" href="sms.php?rid=<?=$_GET['rid']?>&phone=<?=$v['uphone']?>&qid=<?=$v['qid']?>&rname=<?=$rname?>">通知</a>
		<?php }else if($v['status'] == 'smsed'){?>
			<a class="btn btn-primary" href="arrive.php?qid=<?=$v['qid']?>&action=serving&rid=<?=$_GET['rid']?>">到达</a>
		<?php }?>
		</td>
	</tr>

<?php endforeach; ?>


</tbody>

</table>


	<div>
</div>


</div>
<!-- content -->




</body>
</html>

