<html>
<head>
	<meta charset="utf-8">
	<title>我要排队</title>
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
					<li class="active"><a href="#">我要排队</a></li>
					<li><a href="restaurant.php">餐厅管理</a></li>
					<li><a href="about.php">关于我们</a></li>
				</ul>
			</div>
		</div>
	</div>
</div>


<!-- content -->
<div class="container" style="margin-top:60px">
<div class="row">
	<div class="span6 offset3">

		<form class="form-horizontal" method="get" action="postr.php">

			<legend>将我加入排队</legend>

			<div class="control-group">
				<label class="control-label" for="inputPhone">手机号码</label>
				<div class="controls">
					<input type="text" id="inputPhone" name="phone" placeholder="请输入手机号码">
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="inputNum">用餐人数</label>
				<div class="controls">
					<input type="text" id="inputNum" name="num" placeholder="请输入用餐人数">
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="inputId">餐厅id</label>
				<div class="controls">
					<input type="text" id="inputId" name='rid' placeholder="请输入餐厅id">
				</div>
			</div>

			<div class="form-actions">
				<button type="submit" class="btn btn-primary">提交</button>
				<button type="reset" class="btn">重填</button>
			</div>

		</form>


	<div>

</div>
</div>
<!-- content -->




</body>
</html>
