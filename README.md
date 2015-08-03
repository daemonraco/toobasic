# TooBasic 0.4.1

![ ](docs/images/TooBasic-logo-128px.png)

## What is it?
Well __TooBasic__ is a too basic php framework with some basic features, in other
words, a micro-framework.
Its main reason for existence is to provide a simple and quick framework in which
you can start right away building your site while __TooBasic__ takes care of some
common stuff.

## Why would I use this at all?
Well, there's no real reason, you'll probably find much better solutions in the
first page of a google search, but if you want to try a simple framework, keep
reading.

## Basic features?
__TooBasic__ provide some sort of solution to features like:

* __Services__: Controllers that only return a JSON result avoiding presentation
logics.
* __Plugins (modules)__: A simple mechanism to expand your site through plugins
* __Shell Tools__: Some sites usually have background tools to perform heavy
tasks, __TooBasic__ provides a way to define and manage this tools.
	* __Crons__: Something like tools but restricted to cron-type executions.
* __Cache__: It already has a simple way to cache controller result avoiding its
logic on a second request (visit [Cache](docs/cache.md)).
* __Database Wrapping__: Provides a simple way access tables in a database by
representation (visit [Databases](docs/databases.md)).
* __Routes__: Pretty and clean urls (visit [Routes](docs/routes.md)).
* __Skins__: Provide different ways of displaying your sites (visit
[Skins](docs/skins.md)).

## Folders
__TooBasic__ has many folders and we're not going to list them all here, Just a
few you may need to know.

* `ROOTDIR`
    * `site`: Mainly, all your site's stuff goes somewhere inside this folder.
        * `controllers`: Here you start all your PHP files with controller
        specifications.
        * `templates`: Here your store all your templates separated by the way you
        show them.
            * `action`: here you store the way your controllers are seen by
            default.
            * `modal`: and here the way your controllers are seen when they are
            used as modals.
        * `config`: Specific configuration for your site goes here.
        * `models`: Well, this are a bunch of classes that represent your real
        site's logic. Remember, your controllers shouldn't have complex logics.
        * `langs`: Translations.
    * `modules`: Consider these as plugins.
    * `cache`: All the dynamic stuff of your site like cache files, smarty
    compilations, etc. will be stored here.

## How to create a basic page
In order to create a basic page you need to create two files, a controller and a
template, and one name. 
The name must be a lower-case string without spaces or special characters (geeky
info: `/([a-z0-9_]+)/`).

Why a name? well, this name will become the name of your action, your controller,
your template and also the parameter to use in your browser, so, its an important
name.
For our examples we'll use __myaction__ as our _chosen one_.

### Controller
The controller will be a simple PHP class where you assign values to a set of
names, trigger models calls and perform basic logics, etc.
Basically, this new controller will look like this:
```php
<?php
class MyactionController extends \TooBasic\Controller {
	protected function basicRun() {
		return true;
	}
}
```
Such class must be saved into a file called __myaction.php__ inside
__ROOTDIR/site/controllers/__.
Also, the class must be name based on the action name:
> Take your chosen name, change its first letter to upper-case and append
__Controller__.
Then inherit __Controller__ and define a protected method called __basicRun()__

Something to have in mind is to return _true_ or _false_ at the end of
__basicRun()__.
Why? well, __TooBasic__ uses this status to show errors and avoid features like
_cache_.
### Template
Now that you have a controller, you need a template to specify the way you new
action is seen when it's called. You may write something like this:
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
> http://www.example.com/?action=myaction

### But?
Ya ya ya, I know, this seems to be too complicated to build just a HTML, where is
the magic? well, suppose your controller looks like this:
```php
<?php
class MyactionController extends \TooBasic\Controller {
    protected $_cached = true;
	protected function basicRun() {
	    $this->assign("hello", "Hello World!");
		return true;
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
(for an hour), so the next time you refresh your page, the method __basicRun()__
won't be called, its logic won't be used and a cached result will be returned.

Also, you may create a file called __en_us.json__ inside __ROOTDIR/site/langs/__
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
		return true;
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
Well there's is a solition for that, just create the HTML file as we explained
before and let __TooBasic__ use its own _virtual controller_ to render your page.
__TooBasic__ will take care of applying the layout when it's set and using the
cache system to keep it fast.
And in the future, if you think you need it, you can create a controller for that
action and modify it as you please.

## Even easier?!
So, you're not satisfied and you want something more simple, well, visit [this
link](docs/facilities.md) and you may find what you want.

## Suggestions
After all we said you should visit these documentation pages:

* If you're having problems:
	* [Troubleshooting](docs/troubleshooting.md)
	* [Author's Note](docs/authorsnote.md) (yes, it's somehow problems
related)
* If you want to know more:
	* [Using Layouts](docs/layout.md)
	* [Controller Exports](docs/controllerexports.md)
	* [Using Languages](docs/language.md)
	* [Models](docs/models.md)
	* [Using Configs](docs/configs.md)
	* [Services](docs/services.md)
	* [Cache](docs/cache.md)
* If you want the heavy stuff:
	* [Databases](docs/databases.md)
	* [Representations](docs/representations.md)
	* [Skins](docs/skins.md)
	* [Shell Tools](docs/shelltools.md)
	* [Routes](docs/routes.md)
	* [MagicProp](docs/magicprop.md)
	* [Conditional Redirections](docs/redirections.md)

Also:

* [The full index](docsindex.md) of document entries.

## Thanks
### Smarty
Even though you can use other mechanisms, __TooBasic__ provides and adapter for
template using _Smarty_ as engine, and it's selected by default.
You should visit its documentation at
[smarty.net Documentation](http://www.smarty.net/documentation).

Of course, if you want to use a different template interpreter, you may visit the
[Adapters Doc](docs/adapters.md) to find an alternative.
Nonetheless, [_sys-tools_](docs/facilities.md) use _Smarty_ to generate scaffolds.

__Home Page__: http://www.smarty.net/

### Twitter Bootstrap
Because it makes things pretty and saves the life of programmers with no designing
skill like I am.

__Home Page__: http://getbootstrap.com/

### jQuery
Who doesn't use jQuery?

__Home Page__: https://jquery.com/
