<?php

include_once 'test/testhttp.php';
include_once 'test/testautohttp.php';

function runAllTests() {
	$res = true;
	$res &= testhttp\runAllTests();
	$res &= testautohttp\runAllTests();
	echo '<b>' . ($res ? 'All tests passed.' : 'Some test failures occurred.') . '</b><br/>';
}

runAllTests();
