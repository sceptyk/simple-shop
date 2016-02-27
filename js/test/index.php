<html>
	<head>
		<title>JS test</title>
		<script type="text/javascript" src="../library/jquery-2.1.3.min.js"></script>
		<script>
			
			var n = { x : "x"};
			var m = { y : "y"};
			
			n.y = m.y;
			m.x = n.x;
			
			n.x = "z";
			n.y = "z";
			
			console.log(n);
			console.log(m);
			
		</script>
	</head>
	<body>
		<div id="content"></div>
	</body>
</html>