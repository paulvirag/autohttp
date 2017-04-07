<html>
	<head></head>
	<body>
		<?php
			$found = false;
			foreach ($_SERVER as $key => $val)
				if ($key == 'HTTP_X_HTTPTESTHEADER' && $val == 'clienttestval')
					$found = true;
			
			if ($found)
				echo '<p>Custom header detected!</p>';
			else
				echo '<p>Couldn\'t find a custom HTTP header.</p>';
		?>
	</body>
</html>
