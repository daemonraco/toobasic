# TooBasic 1.1.0

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
__TooBasic__ provides some sort of solution to features like:

* __MVC__: Provides model-view-controller mechanism (visit
[Controllers](docs/controller.md) and [Controllers](docs/models.md)).
* __Skins__: Provides different ways of displaying your sites (visit
[Skins](docs/skins.md)).
* __Scaffolds__: Also provides a basic set of tools to create artifacts in a
faster way (visit [Facilities](docs/facilities.md)).
* __Routes__: Pretty and clean urls (visit [Routes](docs/routes.md)).
* __Database Wrapping__: Provides a simple way access tables in a database by
representations (visit [Databases](docs/databases.md) and
[Representations](docs/representations.md)).
* __Services__: Controllers that only return a JSON result avoiding presentation
logics (visit [Services](docs/services.md)).
* __Plugins (modules)__: A simple mechanism to expand your site through plugins.
* __Shell Tools__: Some sites usually have background tools to perform heavy
tasks, __TooBasic__ provides a way to define and manage this tools (visit
[Shell Tools and Crons](docs/shelltools.md)).
	* __Crons__: Something like tools, but restricted to cron-type executions.
* __Cache__: It provides a simple way to cache controller result avoiding its
logic on a second request (visit [Cache](docs/cache.md)).
* __APIs Wrapping__: Provides a simple mechanism to access external APIs (visit
[Simple API Reader](docs/sapireader.md)).
* __Language Translations__: Provides a way to show your sites in different
languages (visit [Languages](docs/language.md)).

## Installation
Installation is not that complicated and you can find how to do it following [this
link](docs/install.md).

## Start up
To show you how easy it could be, once you installed __TooBasic__ in a directory
(let's say named `mysite`), follow these three steps:

* Go to your __TooBasic__ directory (the one called `mysite`).
* Run this command:
```text
php shell.php sys controller new my_controller
```
* Access your newly created controller with your browser at an URL similar to
this:

> http://localhost/mysite/?action=my_controller

And just to let you know:

* this already gave you a controller and a separated view rendered using _Smarty_.
* and it's already using a really basic files based cache system.

Of course you can write controllers and views manually as explained in [this
link](docs/controller.md).

## Documentation
These are some documentation pages we suggest you to visit to get more knowledge
of how __TooBasic__ works:

* If you're having problems:
	* [Troubleshooting](docs/troubleshooting.md)
	* [Author's Note](docs/authorsnote.md) (yes, it's somehow problems
related)
* If you want to know more:
	* [Controllers](docs/controller.md)
	* [Layouts](docs/layout.md)
	* [Controller Exports](docs/controllerexports.md)
	* [Models](docs/models.md)
	* [Scaffolds](docs/facilities.md)
	* [Languages](docs/language.md)
	* [Configs](docs/configs.md)
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
	* [Simple API Reader](docs/sapireader.md)
	* [Search Engine](docs/searchengine.md)
* Even heavier stuff:
	* [Query Adapter](docs/tech/queryadapter.md)
	* [Adapters](docs/tech/adapters.md)

Also:

* [The full index](docsindex.md) of document entries.

## Thanks
### Smarty
Even though you can use other mechanisms, __TooBasic__ provides and adapter for
template using _Smarty_ as engine, and it's selected by default.
You should visit its documentation at
[smarty.net Documentation](http://www.smarty.net/documentation).

Of course, if you want to use a different template interpreter, you may visit the
[Adapters Doc](docs/tech/adapters.md) to find an alternative.
Nonetheless, [_sys-tools_](docs/facilities.md) use _Smarty_ to generate scaffolds.

__Home Page__: http://www.smarty.net/

### Twitter Bootstrap
Because it makes things pretty and saves the life of programmers with no designing
skill like I am.

__Home Page__: http://getbootstrap.com/

### jQuery
Who doesn't use jQuery?

__Home Page__: https://jquery.com/
