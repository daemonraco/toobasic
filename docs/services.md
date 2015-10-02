# TooBasic: Services
## Service?
Think about a controller, but smaller and simpler, some king of API.
A service provides a way to request information or trigger a task through a
controller, but avoiding complex rendering stuff and adding a few things that my
be of help.

In __TooBasic__, a service is a small tool that always gives an answer in JSON
format with a standard structure, something like this:
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
			"file": "/var/www/mysite/includes/Exporter.php",
			"line": 186
		}
	},
	"errors": [
		{
			"code": "400",
			"message": "Parameter 'id' is not set (GET)",
			"location": {
				"method": "TooBasic\\Exporter::checkParams()",
				"file": "/var/www/mysite/includes/Exporter.php",
				"line": 186
			}
		}
	]
}
```

## Let's use an example
Let's think you have a site that handles users and it must provide a way to log-in
from anywhere without using a web page, say a cell phone application or another
site with [REST](https://en.wikipedia.org/wiki/Representational_state_transfer)
access.
When you provide the right username and password, you obtain a token you can use
later on every request.

## Creating a service
Let's create a service to attend this matter by writing the next code and saving
it at __ROOTDIR/site/services/login.php__:
```php
<?php
class LoginService extends \TooBasic\Service {
	protected function basicRun() {
		$out = true;
		if($this->params->server->REQUEST_METHOD != "POST") {
			$this->setError(HTTPERROR_BAD_REQUEST, "Method '{$this->params->server->REQUEST_METHOD}' not supported");
			$out = false;
		} else {
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

* As you've probably guessed already, we are indicating that post parameters
`username` and `password` are required.
* We're making sure this service is been called as a `POST` request.
	* Otherwise, we trigger an error.
* If it's `POST` we validate the user and generate a token for it.
* We also set a header called `Access-Control-Allow-Origin` to avoid
[CORS](https://en.wikipedia.org/wiki/Cross-origin_resource_sharing). This may not
be polite, but it's ok for our examples. Nonetheless, if you want the polite what,
you can read more about it in the section [CORS](services.md#cors).

## Simpler
There is a way to make this more simpler and avoid one of our controls
automatically:
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
Yes, you may replace the common method `basicRun()` for `runGET()` and in most
cases it'll be ok.

## Interfaces
Services in __TooBasic__ provide a way to know what is required to call certain
service in the right manner.
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
		},
		"CORS": {
			"headers": [
				"Accept",
				"Content-Type"
			],
			"methods": [
				"POST",
				"OPTIONS"
			],
			"origins": []
		}
	},
	"error": false,
	"errors": []
}
```
Here you may find the right request method and all the require parameters.

Also you may call this URL to obtain a full list of services and their interfaces.
> http://www.example.com/?explaininterface

## CORS
When you are developing services and trying to provide them as API, one of the
first problems you'll find is _CORS_.
If you want to really understand it you may follow
[this link](https://en.wikipedia.org/wiki/Cross-origin_resource_sharing), but to make it
simple, let's make an example.

Let's say you created a service at http://example.com and then a page at
http://otherexample.com.
In this last one you have a JavaScript that wants to use the service from the
first page.
Everything seems nice, but your browser will fail and won't let you access it due
to CORS issues.
The reason is that you can only use JavaScript to access remote servers when the
page your are visiting has the same server name than the remote one (and schema
and port) or when the remote server allows you to do so.

This seems somehow problematic but it's a security policy enforced by browsers and
there's not much to do except working in compliance with such policy.
_Is there a work around?_ well yes, you can always create a proxy and access
trough it, but __TooBasic__ provides a more polite way to do this.

### Allowing sites
The first thing you'll want to configure is which sites are allowed to access your
services and there are three way to achieve it.

* The first one is to allow sites inside each service writing something like this:
```php
<?php
class LoginService extends \TooBasic\Service {

	. . .

	protected function init() {
		parent::init();

		. . .

		$this->_corsAllowOrigin[] = 'http://otherexample.com';
	}
}
```
This setting will allow any page in http://otherexample.com to access your login
service using JavaScript.

* The second way and the most generic is to add a configuration like this one:
```php
$Defaults[GC_DEFAULTS_SERVICE_ALLOWEDSITES][] = 'http://otherexample.com';
```
This mechanism allows any page in http://otherexample.com to access _any_ service
in your site.

* If you take a closer look, the first way implies code modification every time you
need to add/remove/update a site, while the second one affects all services at
once.
For this reason there's a third way that is in the middle of the other two.
If you want to make only your __login__ service available at any page in
http://otherexample.com without changing the code, you may add this configuration:
```php
$Defaults[GC_DEFAULTS_SERVICE_ALLOWEDBYSRV]['login'] = array(
	'http://otherexample.com'
);
```

### Methods
By default, __TooBasic__ tries to guess what methods your are allowing, but if you
what to provide some in particular, you can add this to your service:
```php
<?php
class LoginService extends \TooBasic\Service {

	. . .

	protected function init() {
		parent::init();

		. . .

		$this->_corsAllowMethods[] = 'PUT';
		$this->_corsAllowMethods[] = 'POST';
	}
}
```

__Note__: Method _OPTIONS_ is always present.

### Headers
If you need to make use of some specific headers you may specify them as allowed
headers writing something like this:
```php
<?php
class LoginService extends \TooBasic\Service {

	. . .

	protected function init() {
		parent::init();

		. . .

		$this->_corsAllowHeaders[] = 'Authorization';
	}
}
```
