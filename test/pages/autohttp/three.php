<html>
	<head>
		<title>Page Three</title>
	</head>
	<body>
		<p>Third page</p>
		<?php
			if ($_POST != null && $_POST['username'] == 'johndoe' && $_POST['password'] == 'baseball22') {
		?>
		<p>Welcome, johndoe!</p>
		<?php
			}
		?>
	</body>
</html>
