<?php
	foreach($_SERVER as $name => $value)
		if (substr($name, 0, 5) === 'HTTP_')
			echo $name . ': ' . $value . '<br>\r\n';
