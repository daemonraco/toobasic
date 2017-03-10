# TooBasic: Controller Exports
## What are these?
_Controller exports_ are special functions provided inside view templates that
help with some tedious aspects.

For example, if you have multiple installations of your site in different URL and
sub-folders of the _DocumentRoot_, generating a proper URL for a `src` or `href`
attribute may become a real pain in t... problem.
For these cases, __TooBasic__ offers these functions to free you from this problem
while it takes care of it.

In the following sections we'll try to explain these functions.

### Warning
This has been tested through the Smarty view adapter which means it could have
issues if a new view adapter is developed and it attempts to make use of this
functionality.

Also, all the examples on this page will be using the Smarty view adapter.

## How to call an exported function
The first thing you need to know is how to call one of these functions, and here
is an example of it:
```html
	<a href="{$ctrl->link('?action=moreinfo&id=10')}" target="_blank">{$tr->label_more_info}</a>
```
Once rendered, it may look like this:
```html
	<a href="/mysite/moreinfo/10" target="_blank">More Info</a>
```
In this example, we've used `$ctrl->link()` to clean an URL of our site and make
sure that it uses the right absolute path and route transformations.

An easy way to identify this kind of functions is by the object `$ctrl` because
all of them belong to it inside view templates.

## Basic path expansion
The functions we'll explain in this section are exported function that simply
generate proper paths for a link or an asset.

### _$ctrl->css()_
This exporting method takes a stylesheet name, assumes its extension as _.css_ and
looks for it inside the folder __styles__ on each module and then the site, and
the first one found is returned as an absolute URI.
If it's not found it will return an empty string.

### _$ctrl->js()_
Idem for JavaScript files and they are looked for inside the folder called
__scripts__.

### _$ctrl->img()_
Idem for image files and they are looked for inside folders called
__images__ and __img__.

By default, this method assumes the _.png_ extension, but it may be change using a
second parameter:
```html
{$ctrl->img('imagename','jpeg')}
```

### _$ctrl->lib()_
This method is a little more simple, it takes a path and checks if it is inside
__ROOTDIR/libraries__, when it is, it returns it's path as an absolute URI,
otherwise it returns an empty string.

### _$ctrl->link()_
This method is a little more complex.
First of all, it follows some basic rules:

* If it takes a full URL containing a host name, it won't touch it and return it
as it is.
* When the URL starts with a slash (`/`), it won't touch it either.
* When there's no parameter given or when it is an empty string it will return the
sites _root URI_.

In other cases, it will prepend the sites _root URI_ and attempt to transform it
using route specifications.
For example, when you write this:
```html
<a class="HelpButton" href="{$ctrl->link('?action=product&id=204578&view_mode=clean&expand=description')}" target="_blank">Help Me Please!</a>
```
Your browser may end up receiving something like this:
```html
<a class="HelpButton" href="/mysite/product/204578?view_mode=clean&expand=description" target="_blank">Help Me Please!</a>
```
... supposing you have this route too:
```json
{
	"routes": [{
		"route": "product/:id:int",
		"action": "product"
	}]
}
```

## Controller insertion
There's an exported function called `$ctrl->insert()` that allows you to call
another controller, execute it, render its view and then insert its result
somewhere in a template.
This mechanism allows you to extract some functionality you'll be repeating and
place it inside a controller along with its template, later on you can call such
controller from anywhere in your templates and expect it to be inserted.

Let's say we have something like this inside one of our templates:
```html
	<div class="AdsSection">
		{$ctrl->insert('ads')}
	</div>
```
Also, you have a template (using the virtual controller) called __ads.html__ with
something like this inside:
```html
<div class="Ads">
	<h4>Visite our market pages at:
	<ul>
		<li><a href="http://www.amazon.com/oursite">Amazon</a></li>
		<li><a href="http://www.ebay.com/oursite">eBay</a></li>
	</ul>
</div>
```

When you call the first page, it will generate something like this:
```html
	<div class="AdsSection">
<div class="Ads">
	<h4>Visite our market pages at:
	<ul>
		<li><a href="http://www.amazon.com/oursite">Amazon</a></li>
		<li><a href="http://www.ebay.com/oursite">eBay</a></li>
	</ul>
</div>
	</div>
```

## Snippets
There's an exported function called `$ctrl->snippet()` similar to
`$ctrl->insert()`, but more simple in the way it works.
If you want to read more about it you may visit [this link](snippets.md).

## HTML assets
Your first question may be _what is a HTML asset in **TooBasic**?_ and that would
be any JavaScript or CSS file either inside the libraries folder or inside one of
the known folders for these kind of files.

As an example, let's say your layout imports three different Javascript files
called:

* `jsquery.siteplugins.js`
* `links.sanitizer.js`
* `magic_menu.js`
	* This one inside the folder __scripts__ of a module called _MagicMenu_.

Also you have some stylesheets to include:

* `site.styles.css`
* `magic_menu.css`
	* Also from that module.

If you read the above sections, you'll probably be thinking on writing something
like this:
```html
	<script src="{$ctrl->js('jsquery.siteplugins')}"></script>
	<script src="{$ctrl->js('links.sanitizer')}" type="text/javascript"></script>
	<script type="text/javascript" src="{$ctrl->js('magic_menu')}"></script>

	<link rel="stylesheet" type="text/css" href="{$ctrl->css('site.styles')}"/>
	<link type="text/css" rel="stylesheet" href="{$ctrl->css('magic_menu')}"/>
```
... and it wouldn't be wrong, but you can also write something like this:
```html
{$ctrl->htmlAllScripts()}
{$ctrl->htmlAllStyles()}
```
These two functions will generate the same inclusions based on HTML assets
configurations.

### HTML assets configuration
The previous example may look like magic, but truth is you'll have to make same
configuration to make it work.
If we continue the example with those assets, you'll have to add a configuration
like this at __ROOTDIR/site/config.php__:
```php
$Defaults[GC_DEFAULTS_HTMLASSETS][GC_DEFAULTS_HTMLASSETS_SCRIPTS][] = 'jsquery.siteplugins';
$Defaults[GC_DEFAULTS_HTMLASSETS][GC_DEFAULTS_HTMLASSETS_SCRIPTS][] = 'links.sanitizer';
$Defaults[GC_DEFAULTS_HTMLASSETS][GC_DEFAULTS_HTMLASSETS_SCRIPTS][] = 'magic_menu';
$Defaults[GC_DEFAULTS_HTMLASSETS][GC_DEFAULTS_HTMLASSETS_STYLES][] = 'site.styles';
$Defaults[GC_DEFAULTS_HTMLASSETS][GC_DEFAULTS_HTMLASSETS_STYLES][] = 'magic_menu';
```
This mechanism allows you to add templates in your layout without modifying its
template.
Also, any module may add its own assets in a cleaner way.

### Specifics
Throughout many pages is common to see a bunch of JavaScript files included inside
tag `<head>` and another bunch of them at the end of tag `<body>`, which can be a
problem for the previous configuration explanation.
But don't panic, there's a way in which you can specify which assets are included
at the end or wherever inside a controllers template.

Let's suppose the same example we've been using and add the idea of a fourth
javascript file called '*magic_menu.fixes.js*' that must be included at the end of
tag `<body>`.
Also, let's suppose our layout is called __my_layout__.

The first thing to do is to create an specific configuration of assets:
```php
$Defaults[GC_DEFAULTS_HTMLASSETS_SPECIFICS]['bottom_assets'] = [
	GC_DEFAULTS_HTMLASSETS_SCRIPTS => ['magic_menu.fixes']
];
```
Once you have this kind of configuration in your site, you can write something
like this:
```html
		. . .
		{$ctrl->htmlAllScripts('bottom_assets')}
	</body>
</html>
```
In this way, you'll be creating a section with only some specific assets.

Now, let's say you have this:
```php
$Defaults[GC_DEFAULTS_HTMLASSETS_SPECIFICS]['my_layout'] = [
	GC_DEFAULTS_HTMLASSETS_SCRIPTS => ['magic_menu.fixes']
];
```
With this configuration you may either use `$ctrl->htmlAllScripts('my_layout')` or
`$ctrl->htmlAllScripts(true)` and obtain the same result.
Giving a value `true` implies that the name of the controller is also de name of a
specific configuration.
This mechanism allows you to have separated configurations for each controller,
but we'll leave that to your imagination and needs of your site.

__Note__: Just remember, if you use a specific configuration and it happens to be
undefined, it will use the main configuration.

### Libraries
At the beginning of this section we said that an asset may be inside a library,
but we've given no examples of it.
If you find yourself in this situation and you need to include, for example, a
JavaScript file from a library, you can do something like this:
```php
$Defaults[GC_DEFAULTS_HTMLASSETS][GC_DEFAULTS_HTMLASSETS_SCRIPTS][] = 'lib:jquery/jquery-2.1.3.min.js';
```
The prefix `lib:` will be removed and the rest will be used as a path inside the
folder __ROOTDIR/libraries__.

## Ajax insert
In essence, `$ctrl->ajaxInsert()` is similar to `$ctrl->insert()`, but instead of
rendering and inserting a controller's result, it just inserts a simple `<div>`
tag that may be used to asynchronously insert a controller's result.

Let's reuse the example we gave in section __Controller insertion__ and say that
we want that view called __ads.html__ to load asynchronously.
The only change we need to do would be this:
```html
	<div class="AdsSection">
		{$ctrl->ajaxInsert('ads')}
	</div>
```
And it will generate something like this:
```html
	<div class="AdsSection">
		<div data-toobasic-insert="?action=ads"></div>
	</div>
```
Or something like this if there's a route configuration supporting it:
```html
	<div class="AdsSection">
		<div data-toobasic-insert="/mysite/ads"></div>
	</div>
```

### Autoloading
Yes, you made the change and nothing happens, _it doesn't "asynchronously load" a
thing._
The problem here is really simple and it comes from the idea of giving you the
flexibility of writing your own code to perform these _asynchronous loads_.
Nonetheless, if you don't want to worry about it, just add this to your
configuration:
```php
$Defaults[GC_DEFAULTS_HTMLASSETS][GC_DEFAULTS_HTMLASSETS_SCRIPTS][] = 'toobasic_asset';
```
Or something like this to your layout:
```html
<script type="text/javascript" src="{$ctrl->js('toobasic_asset')}"></script>
```
Among other functionalities, this JavaScript file provides a way to asynchronously
load `<div>` tags created by `$ctrl->ajaxInsert()`.

### Parameters
If you take a closer look to `$ctrl->insert()` you'll notice that it uses
parameters from the current URL, but when you use a asynchronous load, such URL is
not accessible. Now, how do we solve this problem?

Let's suppose our add section requires a parameter called `pattern` what will
allow targeted ads in future implementations.
Let's also suppose our layout makes this assignment:
```php
$this->assign('adsparams', ['pattern' => 'vodka']);
```
Then you change your layout's template to something like this:
```html
{$ctrl->ajaxInsert('ads',$adsparams)}
```
... and you'll get this:
```html
<div data-toobasic-insert="?action=ads&pattern=vodka"></div>
```

### Attributes
Something else you may want is to add some attributes to these `<div>` tags.
Again let's say our layout makes this assignment:
```php
$this->assign('adsattrs', [
	'id' => 'mainAds',
	'class' => 'AdsSection'
]);
```
Then you change your template:
```html
{$ctrl->ajaxInsert('ads',$adsparams,$adsattrs)}
```
... you'll end up with something like this:
```html
<div data-toobasic-insert="?action=ads&pattern=vodka" id="mainAds" class="AdsSection"></div>
```

Yes, you can also call it this way:
```html
{$ctrl->ajaxInsert('ads',false,$adsattrs)}
```

### Reloading
Following the example and supposing your using the `toobasic_asset.js` file, at
anytime you may execute something like this to reload one of this ajax insertions:
```js
$('#mainAds').tooBasicReload();
```

## Suggestions
You may also want to visit these pages:

* [Layouts](layout.md)
* [Snippets](snippets.md)
* [Routes](routes.md)

<!--:GBSUMMARY:MVC:2:Controller Exports:-->
