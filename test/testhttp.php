<?php

namespace testhttp;

include_once 'test/shared.php';
include_once 'lib/http.php';

//
// Test functions.
//

// Basic tests for downloading a page.
function testGetPage() {
	$config = getConfig();
	$root = getRootPath();
	
	// Get the test page.
	$http = new \AutoHttp\Http($config);
	$page = $http->getPage($root . '/test/pages/http/basic.php');
	
	// Validate basic return structure.
	if (!validateResponse($page))
		return 'Result array was in a bad format.';
	
	// Check the test header content.
	if (!array_key_exists('X-HttpTestHeader', $page['headers']))
		return 'Test header doesn\'t exist.';		
	if ($page['headers']['X-HttpTestHeader'] != 'servertestval')
		return 'Test header value wasn\'t what we were expecting.';
		
	// Check the raw headers.
	if (count($page['headersRaw']) < 1 || strpos($page['headersRaw'][0], '200 OK') === false)
		return 'Raw headers should contain the HTTP response code.';
		
	// Check the test body.
	if (strpos($page['body'], 'This is my page.') === false)
		return 'Test body wasn\'t what we were expecting.';
		
	return true;	
}

// Make sure cookies work.
function testCookies() {
	$config = getConfig();
	$root = getRootPath();
	
	// Get the test page.
	$http = new \AutoHttp\Http($config);
	$page = $http->getPage($root . '/test/pages/http/cookie.php');
	
	// Verify we didn't send a cookie.
	if (strpos($page['body'], "Cookie named 'user' is not set!") === false)
		return 'The cookie shouldn\'t be set on our first page access.';
		
	// Get the page again.
	$page = $http->getPage($root . '/test/pages/http/cookie.php');
	
	// Verify we sent a cookie this time.		
	if (strpos($page['body'], "Cookie 'user' is set!") === false ||
		strpos($page['body'], "Value is: John Doe") === false)
		return 'The cookie should be set on our second page access.';
		
	return true;
}

// Make sure text extraction works.
function testExtraction() {
	$config = getConfig();
	$root = getRootPath();
	
	// Get the test page.
	$http = new \AutoHttp\Http($config);
	$page = $http->getPage($root . '/test/pages/http/textpage.html');
	
	// Verify we can extract absolute text.
	if (\AutoHttp\Http::readBetween($page['body'], '<a id="secretLink" href="', '"') != 'http://example.com/secret')
		return 'Absolute text extraction should have returned the sample URL data.';
	
	// Verify we can extract relative text.
	if (\AutoHttp\Http::readBetween($page['body'], '<td>', '</td>', 'pmurphy') != 'booyah2')
		return 'Relative text extraction should have returned the sample password data.';
		
	return true;	
}

// Basic test for query strings.
function testPageQuery() {
	$config = getConfig();
	$root = getRootPath();
	
	$http = new \AutoHttp\Http($config);
	
	// Load the test page as a GET using a param array.
	$page = $http->getPage($root . '/test/pages/http/params.php', null, array('foo' => 'bar'));
		
	// Make sure the test page received our GET params.
	if (strpos($page['body'], '<li>GET foo=bar</li>') === false)
		return 'Test page should list the GET parameters from our array.';
	
	// Load the test page as a GET using a param string.
	$page = $http->getPage($root .'/test/pages/http/params.php', null, 'hi=there');
		
	// Make sure the test page received our GET params.
	if (strpos($page['body'], '<li>GET hi=there</li>') === false)
		return 'Test page should list the GET parameters from our string.';
	
	// Load the test page as a POST using a param array.
	$page = $http->getPage($root .'/test/pages/http/params.php', null, null, array('createUser' => '1'));
		
	// Make sure the test page received our POST params.
	if (strpos($page['body'], '<li>POST createUser=1</li>') === false)
		return 'Test page should list the POST parameters from our array.';
	
	// Load the test page as a POST using a param string.
	$page = $http->getPage($root .'/test/pages/http/params.php', null, null, 'username=johndoe');
		
	// Make sure the test page received our POST params.
	if (strpos($page['body'], '<li>POST username=johndoe</li>') === false)
		return 'Test page should list the POST parameters from our string.';
		
	return true;	
}

// Test downloading a page with an empty response body.
function testEmptyResponseBody() {
	$config = getConfig();
	$root = getRootPath();
	
	// Get the test page.
	$http = new \AutoHttp\Http($config);
	$page = $http->getPage($root .'/test/pages/http/headersonly.php');
	
	// Validate basic return structure.
	if (!validateResponse($page))
		return 'Result array was in a bad format.';
	
	// Check the test header content.
	if (!array_key_exists('X-HttpTestHeader', $page['headers']))
		return 'Test header doesn\'t exist.';		
	if ($page['headers']['X-HttpTestHeader'] != 'servertestval')
		return 'Test header value wasn\'t what we were expecting.';
		
	// Check the raw headers.
	if (count($page['headersRaw']) < 1 || strpos($page['headersRaw'][0], '200 OK') === false)
		return 'Raw headers should contain the HTTP response code.';
		
	// Make sure the body is empty.
	if (strlen($page['body']) !== 0)
		return 'Response body length should be 0.';
		
	return true;
}

// Test for http error handling.
function testErrors() {
	$config = getConfig();
	$root = getRootPath();
	
	$http = new \AutoHttp\Http($config);

	// Try a malformed URL.
	$page = $http->getPage('cheese');
	if (!validateResponse($page))
		return 'We should receive a valid response object for malformed URLs.';
	if ($page['error'] === null)
		return 'We should have an error message for malformed URLs.';

	// Try a URL that fails to resolve.
	$page = $http->getPage('http://www.test.abcdosjidfs4nt/page.html');
	if (!validateResponse($page))
		return 'We should receive a valid response object for URLs that don\'t resolve properly.';

	// Try a URL that resolves but fails to connect.
	$page = $http->getPage('http://localhost:1/test/omg/page.html');
	if (!validateResponse($page))
		return 'We should receive a valid response object for URLs that reject our connection.';
	if ($page['error'] === null)
		return 'We should have an error message for URLs that reject our connection.';
	
	// Try a page that times out before sending any data.
	$page = $http->getPage($root .'/test/pages/http/errortimeout.php?timeout=initial&duration=10');
	if (!validateResponse($page))
		return 'We should receive a valid response object for pages that time out before sending data.';
	if ($page['error'] === null)
		return 'We should have an error message for pages that time out before sending data.';
	
	// Try a page that times out midway through sending data.
	$page = $http->getPage($root .'/test/pages/http/errortimeout.php?timeout=during&duration=10');
	if (!validateResponse($page))
		return 'We should receive a valid response object for pages that time out while sending data.';
	if ($page['error'] === null)
		return 'We should have an error message for pages that time out while sending data.';
	
	return true;
}

// Test for sending custom request headers.
function testCustomHeaders() {
	$config = getConfig();
	$root = getRootPath();
	
	// Get the test page.
	$http = new \AutoHttp\Http($config);
	$page = $http->getPage($root .'/test/pages/http/customheaders.php', array('X-HttpTestHeader: clienttestval'));
	
	// Make sure our custom header was sent.
	if (strpos($page['body'], 'Custom header detected!') === false)
		return 'Custom header wasn\'t sent.';
	
	return true;
}

//
// Internal helper functions.
//

// Verify the response is in the format we expect.
function validateResponse($page) {
	return array_key_exists('headers', $page) && array_key_exists('headersRaw', $page) && array_key_exists('body', $page) && array_key_exists('error', $page)
		&& is_array($page['headers']) && is_array($page['headersRaw']) && is_string($page['body']);
}

//
// External driver invocation.
//

function runAllTests() {
	return runTests('testhttp', array(
		'testGetPage',
		'testCookies',
		'testExtraction',
		'testPageQuery',
		'testEmptyResponseBody',
		'testCustomHeaders',
		'testErrors'
	));
}
