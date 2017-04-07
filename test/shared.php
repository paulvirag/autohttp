<?php

include_once 'autohttp.php';

//
// Shared test functions.
//

// Create a sample AutoHttp config array.
function getConfig() {
	return array(
		'log' => new AutoHttp\FileLog('./testlogs'),
		'timeoutMs' => 500
	);
}

// Return the root URL for AutoHttp on this server.
function getRootPath() {
	$host = $_SERVER['HTTP_HOST'];
	$uri = $_SERVER['REQUEST_URI'];
	return $host . substr($uri, 0, strrpos($uri, '/'));
}

// Execute tests and print test output.
function runTests($module, $tests) {
	// Run tests and write output.
	$resAll = true;
	echo '<b>' . $module . '</b><br/>';
	foreach ($tests as $test) {
		$func = $module . '\\' . $test;
		$res = 'An exception occurred while executing the test.';
		try {
			$res = $func();
		}
		catch(Exception $e) {
		}
		echo $test . ': ' . ($res === true ? 'PASS<br/>' : 'FAIL<br/>' . $res . '<br/>');
		$resAll &= ($res === true);
	}
	echo '<br/>';
	return $resAll;
}
