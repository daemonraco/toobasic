# TooBasic: Controllers
## How to create a basic page
In order to create a basic page you need to create two files, a controller and a
template; and one name. 
The name must be a lower-case string without spaces or special characters (geeky
info: `/([a-z0-9_]+)/`).

_Why a name?_
Well, this name will become the name of your action, your controller, your
template and also the parameter to use in your browser, so, its an important name.
For our examples we'll use __myaction__ as our _chosen one_.

### Controller
The controller will be a simple PHP class where you assign values to a set of
names, trigger model calls, perform basic logics, etc.
Basically, this new controller will look like this:
```php
<?php
class MyactionController extends \TooBasic\Controller {
	protected function basicRun() {
		return $this->status();
	}
}
```
Such class must be saved into a file called __myaction.php__ inside
__ROOTDIR/site/controllers/__.
Also, the class must be name based on the action name:

>Take your chosen name, change its first letter to upper-case and append
__Controller__.
Then inherit __\TooBasic\Controller__ and define a protected method called
`basicRun()`.

Something to have in mind is to return _true_ or _false_ at the end of
`basicRun()`.
_Why?_
Well, __TooBasic__ uses this status to show errors and avoid features like
_cache_.

### View
Now that you have a controller, you need a template (the view) to specify the way
your new action is displayed when it's called. You may write something like this:
```html
<!DOCTYPE html>
<html>
    <head>
        <title>Hello World!</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <h4>Hello World!</h4>
    </body>
</html>
```
Now you save this code into a file called __myaction.html__ inside
__ROOTDIR/site/templates/action/__.

### Is that it?
Well, yes, that's all you need, now go to your browser, and enter your URL using
the name of your action.
Something like this:

>http://www.example.com/?action=myaction

### But?
Ya ya ya, I know, this seems to be too complicated to build just a HTML, _where is
the magic?_ well, suppose your controller looks like this:
```php
<?php
class MyactionController extends \TooBasic\Controller {
	protected $_cached = true;
	protected function basicRun() {
		$this->assign("hello", "Hello World!");
		return $this->status();
	}
}
```
And your template looks like this:
```html
<!DOCTYPE html>
<html>
    <head>
        <title>{$hello}</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <h4>{$hello}</h4>
    </body>
</html>
```
The result may be the same, but now you are using names previously set in your
controller and also you're saving a cache file of what you've seen in your browser
(by default, for an hour), so the next time you refresh your page, the method
`basicRun()` won't be called, its logic won't be used and a cached result will be
returned.

## Language
You may also create a file called __en_us.json__ inside __ROOTDIR/site/langs/__
with something like:
```json
{
	"keys": [
		{
			"key": "hello",
			"value": "Hello World!"
		}
	]
}
```
Modify your controller:
```php
<?php
class MyactionController extends \TooBasic\Controller {
	protected function basicRun() {
		$this->assign("hello", $this->translate->hello);
		return $this->status();
	}
}
```
And your template:
```html
<!DOCTYPE html>
<html>
    <head>
        <title>{$hello}</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <h4>{$tr->hello}</h4>
    </body>
</html>
```
Now you're also using translations, both inside your template and your controller.

## Even more basic
How about we make things even simpler, let's say you just want to put some content
under some action name and not worry about its controller and its settings and
assignments and bla bla bla.
Well there's is a solution for that, just create the HTML file as we explained
before and let __TooBasic__ use its own _virtual controller_ to render your page.
__TooBasic__ will take care of applying the layout when it's set and using the
cache system to keep it fast.
And in the future, if you think you need it, you can create a controller for that
action and modify it as you please.

## Suggestions
If you want, you may visit these documentation pages:

* [Layouts](layout.md)
* [Scaffolds](facilities.md)
