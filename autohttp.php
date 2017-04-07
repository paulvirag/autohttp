<?php

namespace AutoHttp;

include_once 'lib/http.php';
include_once 'lib/autohttphelpers.php';

class AutoHttp {

	//
	// Private member data.
	//
	
	private $http = null;
	private $log = null;

	//
	// Public methods.
	//
	
	// Constructor.
	public function __construct($config = null) {
		$this->http = new Http($config);
		if ($config && array_key_exists('log', $config))
			$this->log = $config['log'];
	}
	
	// Performs navigation for the given page sequence.
	public function execute($seq) {
		// Do the actual page execution.
		$executeRes = $this->doExecute($seq);
	
		// Write a full log file.
		if ($this->log)
			$this->log->write('autohttp', $this->genLog($seq));
			
		return $executeRes;
	}
	
	//
	// Private helper methods.
	//
	
	// Performs navigation for the given page sequence.
	private function doExecute($seq) {
		// Initialize (or re-initialize) the sequence.
		$seq->variables = $seq->initialVariables;
		$seq->rebuildReplacements();
		foreach ($seq->pages as $page) {
			$page->response = null;
		}
		
		// Execute the sequence.
		foreach ($seq->pages as $page) {		
			// Download the page.
			$page->response = $this->http->getPage(
				$seq->replace($page->url),
				($page->headers === null ? null : $seq->replaceAll($page->headers)),
				null,
				($page->postBody === null ? null : $seq->replace($page->postBody))
			);
			
			// Check for download errors.
			if ($page->response['error'] !== null)
				return 'Error encountered while downloading ' . $seq->replace($page->url) . '.';
			
			// Perform page validations.
			foreach ($page->validations as $validation) {
				$validationRes = $validation->execute($seq, $page->response['body']);
				if ($validationRes !== true)
					return $validationRes;
			}
			
			// Process rules.
			foreach ($page->rules as $rule) {
				$ruleRes = $rule->execute($seq, $page->response['body']);
				if ($ruleRes !== true)
					return $ruleRes;
			}
			
			// Update the replacement strings.
			$seq->rebuildReplacements();
		}
		
		return true;
	}
	
	// Dump all state data for debugging purposes.
	private function genLog($seq) {
		$dump = '<h1>&lt;AutoHttp state dump&gt;</h1>';
		
		// Dump initial variables.
		$dump .= '<h2>Initial Variables</h2>';
		$dump .= '<table border="1">';
		$dump .= '<tr><th>Name</th><th>Value</th></tr>';
		foreach ($seq->initialVariables as $key => $val) {
			$dump .= "<tr><td>$key</td><td>$val</td></tr>";
		}
		$dump .= '</table>';
		
		// Dump updated variables.
		$dump .= '<h2>Final Variables</h2>';
		$dump .= '<table border="1">';
		$dump .= '<tr><th>Name</th><th>Value</th></tr>';
		foreach ($seq->variables as $key => $val) {
			$dump .= "<tr><td>$key</td><td>$val</td></tr>";
		}
		$dump .= '</table>';
		
		// Dump pages.
		for ($i = 0 ; $i < count($seq->pages); $i++) {
			$page = $seq->pages[ $i];
			$dump .= "<h2>Page " . ($i + 1) . "</h2>";
			
			// Dump URL.
			$dump .= '<h3>Url</h3>';
			$dump .= $seq->replace($page->url);
			
			// Dump POST data.
			if ($page->postBody) {
				$dump .= '<h3>Post Body</h3>';
				$dump .= $seq->replace($page->postBody);
			}
			
			// Dump response headers.
			$dump .= '<h3>Response Headers</h3>';
			if ($page->response && $page->response['headersRaw']) {
				$dump .= '<table border="1">';
				foreach ($page->response['headersRaw'] as $header) {
					$dump .= "<tr><td>$header</td></tr>";					
				}
				$dump .= '</table>';
			}
			else
				$dump .= '(n/a)';
			
			// Dump response body.
			$dump .= '<h3>Response Body</h3>';
			if ($page->response && $page->response['body']) {
				$dump .= str_replace(array('&', '<', '>', '"', "'"), array('&amp;', '&lt;', '&gt;', '&quot;', '&#39;'), $page->response['body']);
			}
			else
				$dump .= '(n/a)';
		}
		
		$dump .= '<h1>&lt;/AutoHttp state dump&gt;</h1>';
		
		return $dump;
	}
}
