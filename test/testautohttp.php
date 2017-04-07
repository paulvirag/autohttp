<?php

namespace testautohttp;

include_once 'test/shared.php';
include_once 'autohttp.php';

//
// Test functions.
//

// Basic tests for downloading a sequence of pages.
function testGetPages() {
	$config = getConfig();
	$root = getRootPath();
	$ah = new \AutoHttp\AutoHttp($config);
	
	// Create the navigation sequence.
	$seq = new \AutoHttp\Sequence(
		array(
			new \AutoHttp\Page(
				$root .'/test/pages/autohttp/one.php?token={{token}}',
				null,
				null,
				array(
					new \AutoHttp\Validation('Token accepted.')
				),
				array(
					new \AutoHttp\Rule(array(
						'name' => 'urlTwo',
						'left' => '<a href="',
						'right' => '"',
						'start' => 'Token accepted.'
					))
				)
			),
			new \AutoHttp\Page(
				$root .'/test/pages/autohttp/{{urlTwo}}',
				null,
				null,
				array(
					new \AutoHttp\Validation('<title>Page Two</title>')
				),
				array(
					new \AutoHttp\Rule(array(
						'name' => 'username',
						'left' => '<p>Username: ',
						'right' => '</p>'
					)),
					new \AutoHttp\Rule(array(
						'name' => 'password',
						'left' => '<p>Password: ',
						'right' => '</p>'
					))
				)
			),
			new \AutoHttp\Page(
				$root .'/test/pages/autohttp/three.php',
				null,
				'username={{username}}&password={{password}}',
				array(
					new \AutoHttp\Validation('<p>Welcome, {{username}}!</p>')
				)
			)
		),
		array(
			'token' => 'abc123'
		)
	);
	
	// Execute the sequence.
	$res = $ah->execute($seq);
	
	return $res;
}

// Additional test for sending a custom header.
function testSendHeaders() {
	$config = getConfig();
	$root = getRootPath();
	$ah = new \AutoHttp\AutoHttp($config);
	
	// Create the navigation sequence.
	$seq = new \AutoHttp\Sequence(
		array(
			new \AutoHttp\Page(
				$root .'/test/pages/autohttp/two.php',
				null,
				null,
				array(
					new \AutoHttp\Validation('<title>Page Two</title>')
				),
				array(
					new \AutoHttp\Rule(array(
						'name' => 'username',
						'left' => '<p>Username: ',
						'right' => '</p>'
					)),
					new \AutoHttp\Rule(array(
						'name' => 'password',
						'left' => '<p>Password: ',
						'right' => '</p>'
					))
				)
			),
			new \AutoHttp\Page(
				$root .'/test/pages/autohttp/customheaders.php',
				array(
					'X-Test-Username: {{username}}',
					'X-Test-Password: {{password}}'
				),
				null,
				array(
					new \AutoHttp\Validation('<p>Custom header test page</p>'),
					new \AutoHttp\Validation('<p>Authentication headers accepted!</p>')
				)
			)
		),
		array(
			'token' => 'abc123'
		)
	);
	
	// Execute the sequence.
	$res = $ah->execute($seq);
	
	return $res;
}

//
// External driver invocation.
//

function runAllTests() {
	return runTests('testautohttp', array(
		'testGetPages',
		'testSendHeaders'
	));
}
