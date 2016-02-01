# TooBasic: Simple API Reader
## What is SApiReader?
_Simple API Reader_ is a small mechanism provided by __TooBasic__ to represent API
connections and use them as objects in your site leaving the REST interaction to
__TooBasic__.

In our examples we are going to try connection to a
[_Wikia_](http://www.wikia.com) site for some basic interactions.
More precisely, we're going to connect to [One Piece
_Wikia_](http://onepiece.wikia.com).

## Specification
Given the version 1 of _Wikia_'s API we are going to create a _SApiReader_
specification to list current articles in alphabetical order.
For that we can create a JSON file with the next content and store it at
__ROOTDIR/site/sapis/onepiece.json__:
```json
{
	"name": "One Piece Wikia",
	"description": "One Piece Wikia Page (Non Official)",
	"url": "http://onepiece.wikia.com/api/v1",
	"services": {
		"list": {
			"method": "GET",
			"uri": "/Articles/List?limit=:LIMIT",
			"params": [":LIMIT"],
			"defaults": {
				":LIMIT": 50
			}
		}
	}
}
```
This short specification allows us to do something like this, for example inside a
controller:
```php
. . . 

protected function basicRun() {
	$op = $this->sapireader->onepiece;
	debugit($op->list, true);
	return $this->status();
}

. . . 
```
This will end up calling http://onepiece.wikia.com/api/v1/Articles/List?limit=50
and its result will be printed in your page when you try to access your
controller.
Also, if you add `"type":"json"` at the end of your specification, the result
you'll when calling to `$op->list` will be pre-decoded as JSON and returned as an
object.
`"type":"xml"` is also supported.

### Parameters
If we take a closer look to our example, we'll notice the presence of `:LIMIT`
which is a parameter that can be used when calling `$op->list` simply calling to
`$op->list(25)`.
This will call to http://onepiece.wikia.com/api/v1/Articles/List?limit=25

Here is where `params` shows its importance because, in the case it has more than
one value in it's list, it will be configuring the way these parameters are going
to be read when calling `$op->list(...)`.
For example, if your configuration has something like this:
```json
{
	"name": "One Piece Wikia",
	"description": "One Piece Wikia Page (Non Official)",
	"url": "http://onepiece.wikia.com/api/v1",
	"services": {
		"list": {
			"method": "GET",
			"uri": "/Articles/List?limit=:LIMIT&offset=:OFFSET",
			"params": [":LIMIT", ":OFFSET"],
			"defaults": {
				":OFFSET": 0,
				":LIMIT": 50
			}
		}
	}
}
```
And you call `$op->list(100, 200)`, it will consider `100` as value for `:LIMIT`
and `200` for `:OFFSET`.

### Defaults
You probably guest it by now, but if you don't specify a parameters when calling
`$op->list(...)` if will try to use default values from `defaults`.
Nonetheless, if a parameters without default value is in `params` and is not
provided, it will trigger an exception.

## Extends
Since we based our examples in _Wikia_, we should consider the possibility of
connecting our site to more than one _Wikia_ pages, this means creating a second
configuration file with almost the same information except for 3 or 4 lines.
To avoid this situations, _SApiReader_ provides a way to extend specifications.

Let's follow our examples and suppose that we want to connect to _One Piece_ and
_Fairy Tail_ _Wikia_ pages. First we are going to create a file at
__ROOTDIR/site/sapis/wikia.json__ with this content:
```json
{
	"type": "json",
	"services": {
		"list": {
			"method": "GET",
			"uri": "/Articles/List?limit=:LIMIT&offset=:OFFSET",
			"params": [":LIMIT", ":OFFSET"],
			"defaults": {
				":OFFSET": 0,
				":LIMIT": 50
			}
		}
	}
}
```
Then we are goint to update our specification for _One Piece_ with something like
this:
```json
{
	"name": "One Piece Wikia",
	"description": "One Piece Wikia Page (Non Official)",
	"url": "http://onepiece.wikia.com/api/v1",
	"extends": "wikia"
}
```
And finally we are going to create a file at
__ROOTDIR/site/sapis/fairytail.json__ with this content:
```json
{
	"name": "Fairy Tail's Wikia",
	"description": "Fairy Tail's Wikia Page (Non Official)",
	"url": "http://fairytail.wikia.com/api/v1",
	"extends": "wikia"
}
```
In this way you can add or update services for all _Wikia_ configurations from the
same file and your configurations get smaller and maintainable.

Now you can write something like this:
```php
. . . 

protected function basicRun() {
	$ft = $this->sapireader->fairytail;
	$op = $this->sapireader->onepiece;
	debugit(array(
		"Fairy Tail's list" => $ft->list,
		"One Piece's list" => $op->list
	), true);
	return $this->status();
}

. . . 
```

Also, the parameters `extends` could be a list of names with it requires to extend
more than one file and those files can also extend others giving the flexibility
to create a very well organized set of specifications.

## Headers
Something that is very common in REST calls is the possibility to set a certain
list of headers on each call to provide information like the type of content our
reader is expecting, some authentication information or perhaps some specific flag
required by the API we're accessing.
As an example, we're going to modify our _Wikia_ configuration and add headers for
a supposed user agent that is going to be sent on each request and also the kind
of response we expect:
```json
{
	"abstract": true,
	"type": "json",
	"headers": {
		"User-Agent": "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.86 Safari/537.36",
		"Accept": "application/json"
	},
	"services": {
		"list": {
			"method": "GET",
			"uri": "/Articles/List?limit=:LIMIT&offset=:OFFSET",
			"params": [":LIMIT", ":OFFSET"],
			"defaults": {
				":OFFSET": 0,
				":LIMIT": 50
			}
		}
	}
}
```

## Methods
_SApiReader_ can handle only two request methods, __GET__ which is the default and
the one we've been using, and __POST__.

### POST parameters
As expected, _SApiReader_ provides a way to set which parameters are going to be
sent in the POST request body and not inside the URL.

Let's suppose we want to access certain service of an API that requires two
parameters when we call using POST and write something like this:
```json
{
	"name": "somename",
	"description": "",
	"url": "http://somesite.com/API/v2",
	"type": "json",
	"headers": {
		"User-Agent": "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.86 Safari/537.36",
		"Accept": "application/json"
	},
	"services": {
		"keepAlive": {
			"method": "POST",
			"uri": "/keep-alive?close=:CLOSE",
			"params": [":UID", ":CLOSE", ":TOKEN"],
			"sendParams": {
				"uid": ":UID",
				"authtoken": ":TOKEN"
			},
			"defaults": {
				":CLOSE": "false",
				":TOKEN": "4f3561e3c80d52b64c566f340dc74336"
			}
		}
	}
}
```
Supposing you stored this inside a file called __someapi.json__, you can use it in
this way:
```php
. . . 

protected function basicRun() {
	global $currentUserId;
	$sa = $this->sapireader->someapi;
	debugit($op->keepAlive($currentUserId), true);
	return $this->status();
}

. . . 
```

## Suggestions
If you want or need it, you may visit these documentation pages:

* [Representations](representations.md)

<!--:GBSUMMARY:Tools:1:Simple API Reader:-->
