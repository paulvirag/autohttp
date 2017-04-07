<?php

// Pull values from the query string.
$timeout = null;
$duration = null;
if ($_GET) {
	if (array_key_exists('timeout', $_GET))
		$timeout = $_GET['timeout'];
	if (array_key_exists('duration', $_GET) && is_numeric($_GET['duration']))
		$duration = 1 * $_GET['duration'];
}

// Validate the values we pulled.
if ($timeout !== 'initial' && $timeout !== 'during')
	die ('Please specify a valid "timeout" param (choices: "initial", "during").');
if ($duration == null)
	die ('Please specify a valid "duration" param (int).');

// For 'initial' timeout, delay before sending any data.
if ($timeout == 'initial') {
	sleep($duration);
}

?>
<html>
	<head></head>
	<body>
		<?php
		
		// For 'during' timeout, delay during page transmission.
		if ($timeout == 'during') {
			sleep($duration);
		}
		
		?>
		<p>Timeout completed. (timeout = <?php echo $timeout; ?>, duration = <?php echo $duration; ?>)</p>
	</body>
</html>