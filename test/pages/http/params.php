<html>
	<head></head>
	<body>
		<ul>
			<?php
				if ($_GET) {
					foreach ($_GET as $key => $val) {
						echo '<li>GET ' . $key . '=' . $val . '</li>' . "\r\n";
					}
				}
				if ($_POST) {
					foreach ($_POST as $key => $val) {
						echo '<li>POST ' . $key . '=' . $val . '</li>' . "\r\n";
					}
				}
			?>
		</ul>
	</body>
</html>
