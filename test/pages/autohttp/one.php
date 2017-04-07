<html>
	<head>
		<title>Page One</title>
	</head>
	<body>
		<p>Start page</p>
		<?php
			if ($_GET != null && $_GET['token'] == 'abc123') {
		?>
		<p>Token accepted. Click <a href="two.php">here</a> to go to the login page.</p>
		<?php
			}
		?>
	</body>
</html>
