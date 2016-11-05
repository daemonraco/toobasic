# TooBasic: RESTful JavaScript Asset
## What is this?
__TooBasic__ provides a JavaScript asset with a set of objects based on _jQuery_
that let you easily use _RESTful_ resources.

## How do I activate it
If you want to make use of this asset you can add this line to one of your
configuration files, we recommend `ROOTDIR/site/configs/config_http.php`:
```php
$Defaults[GC_DEFAULTS_HTMLASSETS][GC_DEFAULTS_HTMLASSETS_SCRIPTS][] = 'toobasic_rest_asset';
```

## How to use it
Let's say you have a representation in your site called `people` (yes, we're gonna
use the same example than [before](../restful.md)) and you want to access it from
JavaScript.
The first thing you need to do is to create an object to use it as interface,
something list this:
```javascript
var people_interface = new TooBasic.RestManager('people');
```

### Wrong URL
Sometimes, when you have routes active, you may end up pointing to a wrong URL.
That could be easily solve by doing this:
```javascript
var people_interface = new TooBasic.RestManager('people', '/my_site/');
```
Or even:
```javascript
var people_interface = new TooBasic.RestManager('people', 'https://www.example.com/site/');
```

## Get all entries
To make use of the RESTful action `index` you can do something link this:
```javascript
people_interface.index(function(response) {
	console.log('Full List:', response);
});
```
As you can see on this example, you call a method on the object we've created
before and give a callback function that it's going to be run when the _ajax_ call
finishes.
You can even give a second callback function to execute if something goes wrong.

## Get one item by id
To use the action `show` and get one item, you can do:
```javascript
var id = 125;
people_interface.show(id, function(response) {
	console.log('Item with id ' + id + ' data:', response);
});
```
Again, you can give a callback function as third parameter to do something on
errors.

## Search
To use the action `show` and get one item, you can do:
```javascript
var id = 125;
people_interface.show(id, function(response) {
	console.log('Item with id ' + id + ' data:', response);
});
```




## Going further
If you want to go a bit further, you can expect that each called action return a
[`jqXHR`](http://api.jquery.com/jQuery.ajax/#jqXHR) object with all the methods
and promises that means.

## Suggestions
If you want or need, you may visit these documentation pages:

* [RESTful](../restful.md)
* [Representations](../representations.md)

<!--:GBSUMMARY:Services:3:RESTful:RESTful JavaScript Asset-->
