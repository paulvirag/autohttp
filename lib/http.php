<?php

namespace AutoHttp;

class Http {

	//
	// Public static helper functions.
	//

	// Extract text between two tokens in a body.
	public static function readBetween($body, $left, $right, $start = null) {
		$result = false;
		$startIndex = 0;
		
		// If a start location is specified, change our start index.
		if ($start !== null) {
			$startIndex = strpos($body, $start);
			if ($startIndex !== false) {
				$startIndex += strlen($start);
			}
		}
		
		// Extract the text.
		if ($startIndex !== false) {
			$leftIndex = strpos($body, $left, $startIndex);
			if($leftIndex !== false) {
				$rightIndex = strpos($body, $right, $leftIndex + strlen($left));
				if($rightIndex !== false) {
					$result = substr($body, $leftIndex + strlen($left), $rightIndex - ($leftIndex + strlen($left)));
				}
			}
		}
		
		return $result;
	}

	//
	// Private member data.
	//
	
	private $ch = null;
	private $log = null;
	private $timeoutMs = 4000;
	private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0';
	private $maxRedirects = 8;

	//
	// Public methods.
	//
	
	// Constructor.
	public function __construct($config = null) {
		$this->ch = curl_init();
		if ($config && array_key_exists('log', $config))
			$this->log = $config['log'];
		if ($config && array_key_exists('timeoutMs', $config))
			$this->timeoutMs = $config['timeoutMs'];
		if ($config && array_key_exists('userAgent', $config))
			$this->userAgent = $config['userAgent'];
		if ($config && array_key_exists('maxRedirects', $config))
			$this->maxRedirects = $config['maxRedirects'];
	}
	
	// Gets the page and returns it as a string.
	public function getPage($url, $headers = null, $get = NULL, $post = null, array $options = array()) {
		// Set up parameters.
		$defaults = array(
			CURLOPT_URL => $url . ($get === null ? '' : '?' . (is_array($get) ? http_build_query($get) : $get)),
			CURLOPT_HEADER => TRUE,
			CURLOPT_USERAGENT => $this->userAgent,
			CURLOPT_FOLLOWLOCATION => TRUE,
			CURLOPT_MAXREDIRS => $this->maxRedirects,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_TIMEOUT_MS => $this->timeoutMs,
			CURLOPT_HTTPGET => TRUE,
			CURLOPT_SSL_VERIFYPEER => TRUE,
			CURLOPT_CAPATH => 'cacert.pem',
			CURLOPT_FRESH_CONNECT => TRUE,
			CURLOPT_COOKIEFILE => "",
			CURLOPT_HTTPHEADER => array_merge(
				array(
					"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
					"Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7",
					"Accept-Language: en-US,en;q=0.5",
					"Accept-Encoding: identity"
				),
				(($headers && is_array($headers)) ? $headers : array())
			)
		);
		
		// Add POST data.
		if ($post) {
			$defaults += array(
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => (is_array($post) ? http_build_query($post) : $post)
			);
		}

		// Make the request.
		$error = null;
		curl_setopt_array($this->ch, ($options + $defaults));
		if( ! $response = curl_exec($this->ch)) {
			$error = curl_error($this->ch);
		}
		
		// Parse the headers.
		$headers = array();
		$headersRaw = array();
		$bodyString = '';
		if (strpos($response, "\r\n\r\n") !== false) {
			list($headerString, $bodyString) = explode("\r\n\r\n", $response, 2);
			foreach (explode("\r\n", $headerString) as $header) {
				$headersRaw[] = $header;
				if (stripos($header, ': ') !== false) {
					list($headerName, $headerVal) = explode(": ", $header, 2);
					$headers[$headerName] = $headerVal;
				}
			}
		}
		
		// Write a log file.
		if ($this->log)
			$this->log->write('http', $this->genLog($url, $get, $post, $response, $error));
		
		return array('headers' => $headers, 'headersRaw' => $headersRaw, 'body' => $bodyString, 'error' => $error);
	}
	
	//
	// Private helper methods.
	//
	
	// Creates a log file for the HTTP request.
	private function genLog($url, $get, $post, $response, $error) {
		$dump = '<h1>&lt;Http state dump&gt;</h1>';
		
		// Dump url.
		$dump .= '<h2>Url</h2>';
		$dump .= $url;
		
		// Dump GET params.
		$dump .= '<h2>GET params</h2>';
		$dump .= (is_array($get) ? http_build_query($get) : $get);
		
		// Dump POST params.
		$dump .= '<h2>POST params</h2>';
		$dump .= (is_array($post) ? http_build_query($post) : $post);
		
		// Dump response.
		$dump .= '<h2>Response</h2>';
		$dump .= str_replace(array('&', '<', '>', '"', "'"), array('&amp;', '&lt;', '&gt;', '&quot;', '&#39;'), $response);
		
		// Dump error.
		if ($error) {
			$dump .= '<h2>cURL Error</h2>';
			$dump .= $error;
		}
		
		$dump .= '<h1>&lt;/Http state dump&gt;</h1>';
		
		return $dump;
	}
}
