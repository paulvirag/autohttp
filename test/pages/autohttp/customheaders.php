<html>
	<head>
		<title>Header Test</title>
	</head>
	<body>
		<p>Custom header test page</p>
		<?php
			$foundUsername = false;
			$foundPassword = false;
			foreach ($_SERVER as $key => $val) {
				if ($key == 'HTTP_X_TEST_USERNAME' && $val == 'johndoe')
					$foundUsername = true;
				if ($key == 'HTTP_X_TEST_PASSWORD' && $val == 'baseball22')
					$foundPassword = true;
			}
		
			if ($foundUsername && $foundPassword)
				echo "<p>Authentication headers accepted!</p>";
			else
				echo "<p>Couldn't find the authentication headers.</p>";
			
		?>
	</body>
</html>
