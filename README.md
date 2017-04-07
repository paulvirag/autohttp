# AutoHttp
AutoHttp provides a convenient interface for automating a sequence of HTTP requests. The system features a lightweight templating engine that allows you to extract values from responses and regurgitate them in subsequent requests, enabling you to declaratively describe nontrivial HTTP transactions (login and authentication, csrf token submission, following hyperlinks, etc) without needing to write your own processing code.

## Getting Started
Install AutoHttp by extracting the repo into your project folder, and including 'autohttp.php' in any code that requires the system.

To interface with the system you'll define your HTTP steps by creating an \AutoHttp\Sequence, which defines a series of \AutoHttp\Page objects. If you're interfacing with a third-party website or application, you'll likely want to start by using an HTTP debugger (either your browser's dev tools or a debugging proxy like Fiddler) to figure out the exact set of requests you'd like to automate and which request values need to be extracted from response data.

A simple example might look like this:
```php
include_once 'autohttp.php'

// Declare our navigation sequence.
$seq = new \AutoHttp\Sequence(
	array(
		// First request - an HTTP GET for a login page.
		new \AutoHttp\Page(
			'http://example.com/login.php',
			null,
			null,
			array(
				// Verify the login page looks the way we expect before proceeding.
				new \AutoHttp\Validation('Welcome to example.com!')
			),
			array(
				// Extract a token value from the login page so we can submit the form.
				new \AutoHttp\Rule(array(
					'name' => 'token',
					'left' => '<input type="hidden" name="token" value="',
					'right' => '" />'
				))
			)
		),
		// Second request - an HTTP POST to submit the login form.
		new \AutoHttp\Page(
			'http://example.com/postlogin.php',
			null,
			// A non-null request body will instruct AutoHttp to use POST instead of GET.
			// Placeholders like {{this}} in the URL, body, or headers, will automatically
            // be replaced with variables extracted by \AutoHttp\Rule objects.
			'username={{username}}&password={{password}}&token={{token}}',
			array(
				// Verify the response looks the way we expect.
				new \AutoHttp\Validation('<title>Welcome, John Doe!</title>')
			),
			array(
				// Extract a response value after navigation is complete.
				new \AutoHttp\Rule(array(
					'name' => 'balance',
					'start' => '<h2>Account Summary</h2>',
					'left' => '<p>Your account balance is: ',
					'right' => '</p>'
				))
			)
		)
	),
	// An initial array of variables for the templating system to use.
	array(
		'username' => 'johndoe53',
		'password' => 't0pS3cret'
	)
);

// Perform the navigation.
$ah = new \AutoHttp\AutoHttp();
$res = $ah->execute($seq);
if ($res === true)
	echo 'Login successful! Your account balance is ' . $seq->variables['balance'] . '.';
else
	echo 'Login failed: ' . $res;
```

## Classes

### AutoHttp
Primary interface class for the AutoHttp system.

#### Methods
#### `public function __construct(array $config = null)`

`$config` : an associative array containing the following optional values:

Key | Value
---|---
log | a ResponseLog or FileLog object to use for logging status and failure data (default: null)
timeoutMs | an integer number of milliseconds to wait before timing out an individual response (default: 4000)
userAgent | a User-Agent string to submit with requests (default: the Win10 Firefox52 UA string)
maxRedirects | the maximum number of redirects to follow before aborting a request (default: 8)

#### `public function execute(Sequence $seq)`
Executes a sequence of HTTP transactions. Returns true if all requests succeed, all Validations pass, and all Rules successfully execute. Otherwise returns an error string.

### Sequence
Defines an entire sequence of HTTP navigations. This is the data class that is handed to AutoHttp for execution.

#### Methods
#### `public function __construct(array $pages = null, array $initialVariables = null)`

`$pages` : an array of Page objects to be executed in order

`$initialVariables` : an associative array containing an initial set of variables to be used by the template replacement system

#### Public Variables
The following variables can be accessed after execution to examine information about each transaction:

`public $pages` : the array of Page objects, which will contain response data once execution is complete

`public $initialVariables` : the initial variable dictionary

`public $variables` : the final variable dictionary after execution


### Page
Defines a single HTTP request.

#### Methods
#### `public function __construct(string $url, array $headers = null, string $postBody = null, array $validations = null, array $rules = null)`

`$url` : the url to request. May contain template placeholders

`$headers` : a (non-associative) array containing header rows to add to the request. May contain template placeholders

`$postBody` : the request body. If this is non-null, POST is assumed instead of GET. May contain template placeholders

`$validations` : an array of Validation objects to run after the response is received

`$rules` : an array of Rule objects to run after the response is received

#### Public Variables
The following variables can be accessed after execution to examine information about the transaction:

`public $response` : an associative array containing the following values:
Key | Value
---|---
headers | an associative array of response headers
headersRaw | a non-associative array of response headers
body | the response body
error | an error string if one was encountered during the HTTP transaction

### Validation
Defines a piece of text to search for in the response body to ensure the page matches the one we're expecting. If a Validation fails, AutoHttp will halt further execution.

#### Methods
#### `public function __construct(string $text)`

`$text` : the text to search for

### Rule
Defines a string extraction rule that takes place after the page has been downloaded. Extracts a piece of text from between two fixed strings in the response body and adds it to the variable array.

#### Methods
#### `public function __construct(array $values)`

`$values` : an associative array containing the following parameters:
Key | Value
---|---
name | the name of the variable to store the extracted result
start | (optional) a string in the response body to use as a starting point for the search
left | a string in the response body that occurs directly to the left of the desired value
right | a string in the response body that occurs directly to the right of the desired value

### FileLog
A logger class that writes to a directory on the server.

#### Methods
#### `public function __construct(string $logDir)`

`$logDir` : the directory to write log files to

### ResponseLog
A logger class that writes to the response stream.

#### Methods
#### `public function __construct()`

## Testing & Contribution
AutoHttp works fine inside command-line PHP projects, but in order to run the test library you will need a webserver that is configured to serve tests.php and the pages in the 'test/pages' directory. Execute the test cases by loading tests.php in a web browser and verifying that the result is '**All tests passed**'.

Contributions are welcome and encouraged :). Just add a test case to cover your new functionality, and please ensure all tests are passing before submitting a PR. 

## About
Contact: paulvirag (paulvirag@live.com)
