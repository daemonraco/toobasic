# TooBasic: Services
## Service?
Think about a controller, but smaller and simpler, some king of API.
A service provides a way to request information or trigger a task through a controller, but avoiding complex rendering stuff and adding a few things that my be of help.

In __TooBasic__, a service is a small tool that always gives an answer in JSON format with a standard structure, something like this:
```json
{
	"status": true,
	"data": {
		"service": "login",
		"name": "login"
	},
	"error": false,
	"errors": []
}
```
Or this when there's an error:
```json
{
	"status": false,
	"data": [],
	"error": {
		"code": "400",
		"message": "Parameter 'id' is not set (GET)",
		"location": {
			"method": "TooBasic\\Exporter::checkParams()",
			"file": "C:\\RACO\\xampp\\htdocs\\toobasic-dev\\includes\\Exporter.php",
			"line": 186
		}
	},
	"errors": [
		{
			"code": "400",
			"message": "Parameter 'id' is not set (GET)",
			"location": {
				"method": "TooBasic\\Exporter::checkParams()",
				"file": "C:\\RACO\\xampp\\htdocs\\toobasic-dev\\includes\\Exporter.php",
				"line": 186
			}
		}
	]
}
```


## Let's use an example
Let's think you have a site that handles users and it must provide a way to log-in from anywhere without using a web page, say a cellphone application or another site with REST access.
When you provide the right username and password, you obtain a token you can use later on every request.

## Creating a service
Let's create a service to attend this matter by writing the next code and saving it at __ROOTDIR/site/services/login.php__:
```php
<?php
class LoginService extends \TooBasic\Service {
	protected function basicRun() {
		$out = true;
		if($_SERVER["REQUEST_METHOD"] != "POST") {
			$this->setError(HTTPERROR_BAD_REQUEST, "Method '{$_SERVER["REQUEST_METHOD"]}' not supported");
			$out = false;
		}else{
			$username = $this->params->post->username;
			$password = $this->params->post->password;
			if($this->model->users->auth($username, $password)) {
				$this->assign("token", $this->model->users->genToken($username));
			}
			$this->_headers["Access-Control-Allow-Origin"] = "*";
		}
		return $out;
	}
	protected function init() {
		parent::init();
		$this->_requiredParams["POST"][] = "username";
		$this->_requiredParams["POST"][] = "password";
	}
}
```
Possible answer:
```json
{
	"status": true,
	"data": {
		"service": "login",
		"name": "login",
		"token": "bb7aa3e54918aa5f5aec3f7898bd23c9"
	},
	"error": false,
	"errors": []
}
```

Let's explain how this works:

* As you've probably guessed already, we are indicating that post paramateres `username` and `password` are required.
* We're making sure this service is been called as a `POST` request.
	* Otherwise, we trigger an error.
* If it's `POST` we validate the user and generate a token for it.
* We also set a header called `Access-Control-Allow-Origin` to avoid [CORS](https://en.wikipedia.org/wiki/Cross-origin_resource_sharing). This may not be polite, but it's ok for our examples.

## Simpler
There is a way to make this more simpler and avoid one of our controls automatically:
```php
<?php
class LoginService extends \TooBasic\Service {
	protected function runPOST() {
		$out = true;
		$username = $this->params->post->username;
		$password = $this->params->post->password;
		if($this->model->users->auth($username, $password)) {
			$this->assign("token", $this->model->users->genToken($username));
		} else {
			$this->setError(HTTPERROR_BAD_REQUEST, "Invalid username or password");
		}
		$this->_headers["Access-Control-Allow-Origin"] = "*";
		return $out;
	}
	protected function init() {
		parent::init();
		$this->_requiredParams["POST"][] = "username";
		$this->_requiredParams["POST"][] = "password";
	}
}
```
In this way you're forcing your service to work only for `POST` requests.

### May I?
Yes, you may replace the common method `basicRun()` for `runGET()` and in most cases it'll be ok.

## Interfaces
Services in __TooBasic__ provide a way to know what is required to call certain service in the right manner.
If you call your service in this way:
> http://www.example.com/?service=login&explaininterface

You may obtain something like this:
```json
{
	"status": true,
	"interface": {
		"name": "login",
		"cached": false,
		"methods": [
			"POST"
		],
		"required_params": {
			"POST": [
				"username",
				"password"
			]
		},
		"cache_params": {
			"GET": [
				"mode"
			],
			"POST": []
		}
	},
	"error": false,
	"errors": []
}
```
Here you may find the right request method and all the require parameters.

Also you may call this URL to obtain a full list of services and their interfaces.
> http://www.example.com/?explaininterface
