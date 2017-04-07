<?php

namespace AutoHttp;

// Log implementation that writes new files to a folder.
class FileLog {

	//
	// Private member data.
	//
	
	private $logDir = null;
	
	//
	// Public methods.
	//
	
	public function __construct($logDir) {
		$this->logDir = $logDir;
	}
	
	public function write($caller, $text) {
		// Create the log folder if necessary.
		if (!is_dir($this->logDir)) {
			mkdir($this->logDir);
		}
		
		// Create the filename.
		$timeArray = explode(' ', microtime(false));
		$filename = $this->logDir . '/' . $caller . date('YmdHis') . substr($timeArray[0], 2) . '.log';
		
		// Create the file.
		$myFile = fopen($filename, "w");
		if ($myFile) {
			fwrite($myFile, $text);
			fclose($myFile);
		}
	}
}

// Log implementation that writes to stdout.
class ResponseLog {
	
	//
	// Public methods.
	//
	
	public function write($caller, $text) {
		echo '<p>Logging for caller "' . $caller . '":</p>';
		echo $text;
	}
}

// Represents a sequence of pages to be loaded by AutoHttp.
class Sequence {
	
	//
	// Public member data.
	//
	
	// Setup data.
	public $pages = null;
	public $initialVariables = null;
	
	// Execution-time data.
	public $variables = null;
	public $replacements = null;
	
	//
	// Public methods.
	//
	
	// Constructor.	
	public function __construct($pages = null, $initialVariables = null) {
		$this->pages = ($pages == null ? array() : $pages);
		$this->initialVariables = ($initialVariables == null ? array() : $initialVariables);
	}
	
	// Rebuilds the array of replacement strings based on new variable values.
	public function rebuildReplacements() {
		$search = array();
		$replace = array();
		foreach ($this->variables as $var => $val) {
			$search[] = '{{' . $var . '}}';
			$replace[] = $val;
		}
		$this->replacements = array($search, $replace);
	}
	
	// Replace text based on variable values.
	public function replace($text) {
		return str_replace($this->replacements[0], $this->replacements[1], $text);
	}
	
	// Replace array based on variable values.
	public function replaceAll($arr) {
		for ($i = 0; $i < count($arr); $i++)
			$arr[$i] = $this->replace($arr[$i]);
		return $arr;
	}
}

// Represents a single page to be loaded.
class Page {
	
	//
	// Public member data.
	//
	
	// Setup data.
	public $url = null;
	public $headers = null;
	public $postBody = null;
	public $validations = null;
	public $rules = null;
	
	// Execution-time data.
	public $response = null;
	
	//
	// Public methods.
	//
	
	// Constructor.
	public function __construct($url, $headers = null, $postBody = null, $validations = null, $rules = null) {
		$this->url = $url;
		$this->headers = $headers;
		$this->postBody = $postBody;
		$this->validations = ($validations == null ? array() : $validations);
		$this->rules = ($rules == null ? array() : $rules);
	}
}

// Represents a validation to perform after a page has loaded.
class Validation {
	
	//
	// Public member data.
	//
	
	public $text = null;
	
	//
	// Public interface methods.
	//
	
	// Constructor.
	public function __construct($text) {
		$this->text = $text;
	}
	
	// Verify that the response contains the text we expect.
	public function execute($seq, $response) {
		$text = $seq->replace($this->text);
		if (stripos($response, $text) === false)
			return 'Validation failed. Couldn\'t find text in response: "' . $text . '"';
			
		return true;
	}
}

// Represents a variable creation rule to execute after a page has loaded.
class Rule {
	
	//
	// Public member data.
	//
	
	public $values = null;
	
	//
	// Public interface methods.
	//
	
	// Constructor.
	public function __construct($values) {
		$this->values = $values;
	}
	
	// Execute the rule and modify the state of the browsing sequence.
	public function execute($seq, $response) {
		if (array_key_exists('name', $this->values) &&
			array_key_exists('left', $this->values) &&
			array_key_exists('right', $this->values)) {			
			$readRes = Http::readBetween($response, $seq->replace($this->values['left']), $seq->replace($this->values['right']), (array_key_exists('start', $this->values) ? $seq->replace($this->values['start']) : null));
			if ($readRes === false)
				return 'Processing failed for rule: "' . $this->values['name'] . '"';
				
			$seq->variables[$this->values['name']] = $readRes;
		}
		else
			return "Rule was not set up correctly.";
			
		return true;
	}
}
