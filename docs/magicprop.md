# TooBasic: MagicProp
## MagicProp?
If you've been reading other __TooBasic__ document pages, you may came across
something like this `$this->model->user` where the question is _what is
`$this->model`?_.
Well, that is a magic property that allows you to access your site's models in an
easy way.

## How does it work?
Internally, it gives you access to core factories like __ModelsFactory__ or
__ItemsFactoryStack__, or core singletons like __Params__ or __Paths__.
If you prefer, you may access this core classes directly, but the __MagicProp__
method may be easier.

## Where can I use it?
At this point, you may use these magic properties inside any of these:

* Controllers
* Layouts
* Services
* Models
* Shell Tools
* Shell Crons
* Emails

## Known properties
The properties you may use are:

* `$this->model`: To access any model defined of your site (visit
[Models](models.md)).
* `$this->representation`: To access any representation factory of your site
(visit [Representations](representations.md)).
* `$this->params`: To access the singleton `Params` and then any of its public
methods. For example:
```php
return isset($this->params->get->userid);
```
* `$this->paths`:  To access the singleton `Paths` and then any of its public
methods. For example:
```php
$namesConfigPath = $this->paths->configPath("known_names", \TooBasic\Paths::ExtensionJSON);
```
* `$this->translate`: To access the singleton `Translate` and then any of its
public methods. For example:
```php
echo $this->translate->key_hello_world;
```
* `$this->cache`: To access current cache adapter  (visit [Cache](cache.md)).

## My own
Now suppose you are inside one of your own classes and you don't have these magic
properties.
Well, don't panic, there's is a way to obtain this behavior using a singleton
called __MagicProp__, something like this:
```php
<?php
class MyClass {
	protected $_magic = false;
	public function __construct() {
		$this->_magic = \TooBasic\MagicProp::Instance();
	}
	public function sayHello() {
		echo $this->_magic->translate->key_hello_world;
	}
}
```
Or even better:
```php
<?php
class MyClass {
	protected $_magic = false;
	public function __construct() {
		$this->_magic = \TooBasic\MagicProp::Instance();
	}
	public function __get($prop) {
		$out = false;
		try {
			$out = $this->_magic->{$prop};
		} catch(\TooBasic\MagicPropException $ex) {}
		return $out;
	}
	public function sayHello() {
		echo $this->translate->key_hello_world;
	}
}
```

### MagicPropException
If you look closely, in the second example we're using a try-catch sentence and
trapping an exception called `\TooBasic\MagicPropException` which is raised when
an unknown property is requested.

## Suggestions
Here you have a few links you may want to visit:

* [Models](models.md)
* [Representations](representations.md)
* [Cache](cache.md)
