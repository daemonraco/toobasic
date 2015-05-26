# TooBasic: Routes
## What are routes?
Oh yes, routes, some pretty stuff and clean way to browse your site, but also a
heavy matter when it comes to explain _what_ it is and _how_ it works, so, to
avoid misunderstanding and outdated terminology, let's allow the community to
explain it for us and follow these links:

* [Semantic URL](http://en.wikipedia.org/wiki/Semantic_URL)
* [Rewrite Engine](http://en.wikipedia.org/wiki/Rewrite_engine)

Perhaps too much to read and not that much understanding, well let's say you have this complex and ugly url here:

>http://www.myhost.com/mysite/?action=product&id=204578&view_mode=clean&expand=description

_Routes_ allows you to use it in cleaner way like this:

>http://www.myhost.com/mysite/product/cleaned/204578?expand=description

## Before we start
Hold your horses! Before we start there are a bunch of things you need to check to
be sure you can use routes.

### mod_rewrite
Check you _Apache_ configuration (here we suppose you're using
[_Apache_](http://httpd.apache.org/)) and make sure you are loading the module
__mod_rewrite__.
If your are not sure how to check that, write a simple php file in your site
having this code an access it:
```php
<?php
phpinfo();
```
When you access it through browser, look for a section called __Loaded Modules__
and check if it includes __mod_rewrite__.
If not, follow
[this link](http://httpd.apache.org/docs/current/mod/mod_rewrite.html) and you may
find some help in how to solve this issue.

### Allow Override
Check you _Apache_ configuration file (probably at __/etc/apache2/apache2.conf__
on *nix systems).
Inside it look for something looking like this:
```
<Directory /var/www/>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
</Directory>
```
And make sure that `AllowOverride` is set to `All`.

If you're not sure about the security of this step, you may add something like
this instead:
```
<Directory /var/www/mysite/>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
</Directory>
```

### Permissions
Make sure your web user has reading permission for the __.htaccess__ file.

### The right name
To avoid assuming a default name that may be wrong, check you _Apache_
configuration file and look for the tag __AccessFileName__, if it's not
__.htaccess__ you have to take one of these options:

* Rename __.htaccess__ file to match this configuration (recommended).
* Change the _Apache_ configuration (here I hope you know what you are doing).

## Activating routes
By default, __TooBasic__ assumes you're not using clean url, therefore the first
step to take is activate it.
Go to your site's main configuration file located at __ROOTDIR/site/config.php__
and add something like this:
```php
<?php
$Defaults[GC_DEFAULTS_ALLOW_ROUTES] = true;
```
This will change many internal behaviors and it will start analyzing routes
configurations and checking for a url parameter called __route__.

## Our first route
Let's think about the url we mentioned at the beginning of this page and use it as
our example:

>http://www.myhost.com/mysite/?action=product&id=204578&view_mode=clean&expand=description

If we look closely we can find three important parts:

* `action`: The action/controller to invoke, in this case, a controller to display
a product.
* `id`: The id of a product to display.
* `view_mode`: The way a product has to be displayed.

Based on this, we may want to transform this url in something simpler something
like our example:

>http://www.myhost.com/mysite/product/cleaned/204578?expand=description

Where:

* `product`: Is a short access for `action=product`.
* `cleaned`: Short for `view_mode=clean`.
* `204578`: Short for `id=204578`.

To achieve this we are going to write a file at
__ROOTDIR/site/configs/routes.json__ with something like this:
```json
{
    "routes": [
        {
            "route": "product/cleaned/:id:int",
            "action": "product",
            "params": {
                "view_mode": "clean"
            }
        }
    ]
}
```

Now, what the heck is going on here.
First thing you need to know is that every route specification has two important
parameters __route__ and __action__, in other words, the way the url looks like
and the action/controller that is going to take care of it.

The parameters `params` is a way to implicitly set url parameters, in our case,
when this route is valid it will assume there's a url parameter called
__view_mode__ with the value _clean_.

## Route Analysis
Ok, we've seen how to configure a route, but, how does the __route__ pattern work?
Well the first thing you need to now is that __TooBasic__ splits this value on
each slash (`/`) and checks each piece against the current url, also split in the
same manner.

First two pieces, `product` and `cleaned` are considered to be _literal_ which
means they must be exact matches.
The third piece is a little more tricky and it's considered a _parameter_.

Now, a _parameter_ is not a piece of url it has to be check, in fact is a piece
that has to be transformed into a url query's parameter.
In our example, when we say `:id:` we are saying the piece is a parameter (that's
the meaning of those colons) and it's name will be __id__.
Also we are adding a type check saying `:id:int`, which means it is a parameter
called __id__ and it must be an integer:

### Parameters types
When validating parameters types, you may use this:

* `string` or `str`: for string values.
* `integer` or `int`: for numeric values.
* nothing: for no validation.

## Url issues
All this may look interesting, clean and fun, but there are a few aspects that can
give you a headache.
Let's you have a layout looking like this (in Smarty):
```html
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>MySite</title>
		<link rel="shortcut icon" type="image/png" href="site/images/favicon.ico"/>
		<link rel="stylesheet" type="text/css" href="site/styles/main.css"/>
	</head>
	<body>
		<a class="HelpButton" href="?action=helpme" target="_blank">Help Me Please!</a>
		<div class="MainContent container">
%TOO_BASIC_ACTION_CONTENT%
		</div>
		<script type="text/javascript" src="libraries/angularjs/angular.min.js"></script>
		<script type="text/javascript" src="site/scripts/main.js"></script>
	</body>
</html>
```
When you use route you may end up HTTP-404 errors every where looking for things
like:

>http://www.myhost.com/mysite/product/cleaned/204578/site/images/favicon.ico

When it should be:

>http://www.myhost.com/mysite/site/images/favicon.ico

And you shouldn't use something like this:
```html
<link rel="shortcut icon" type="image/png" href="/mysite/site/images/favicon.ico"/>
```
Because you'll never know where your site will be deployed, so __/mysite__ is a
directory name that may change.

Here is where the controller exports come in handy.
Let's rewrite this layout in this way:
```html
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>MySite</title>
		<link rel="shortcut icon" type="image/png" href="{$ctrl->img('favicon','ico')}"/>
		<link rel="stylesheet" type="text/css" href="{$ctrl->css('main')}"/>
	</head>
	<body>
		<a class="HelpButton" href="{$ctrl->link('?action=helpme')}" target="_blank">Help Me Please!</a>
		<div class="MainContent container">
%TOO_BASIC_ACTION_CONTENT%
		</div>
		<script type="text/javascript" src="{$ctrl->lib('angularjs/angular.min.js')}"></script>
		<script type="text/javascript" src="{$ctrl->js('main')}"></script>
	</body>
</html>
```
In this way, we are letting __TooBasic__ take care of many of our url trusting
that it will add the write prefix to avoid problems.

### _$ctrl->css()_
This exporting method takes a stylesheet name, assumes its extension is _.css_ and
looks for it inside folder __styles__ on each module and then the site, and the
first one found is returned as an absolute URI.
If it's not found it will return an empty string.

### _$ctrl->js()_
Idem for JavaScript files and they be looked for inside a folder called
__scripts__.

### _$ctrl->img()_
Idem for images files and they be looked for inside a folders called
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
First of all, if it takes a full URL containing hostname, it won't touch it and
return it as it is.
When the url starts with a slash (`/`), it won't touch it either.
When there's no parameter given or when it is an empty string it will return the
sites _root uri_.

In other cases, it will prepend the sites _root uri_ and attempt to transform it
using routes specs.
For example, when write this:
```html
<a class="HelpButton" href="{$ctrl->link('?action=product&id=204578&view_mode=clean&expand=description')}" target="_blank">Help Me Please!</a>
```
Your browser may end up receiving something like this:
```html
<a class="HelpButton" href="/mysite/product/204578?view_mode=clean&expand=description" target="_blank">Help Me Please!</a>
```
... Supposing you have this route too:
```json
{
    "routes": [
        {
            "route": "product/:id:int",
            "action": "product"
        }
    ]
}
```

### Final result
After rendering, our layout may look like this:
```html
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>MySite</title>
		<link rel="shortcut icon" type="image/png" href="/mysite/site/images/favicon.ico"/>
		<link rel="stylesheet" type="text/css" href="/mysite/site/styles/main.css"/>
	</head>
	<body>
		<a class="HelpButton" href="/mysite/?action=helpme" target="_blank">Help Me Please!</a>
		<div class="MainContent container">
%TOO_BASIC_ACTION_CONTENT%
		</div>
		<script type="text/javascript" src="/mysite/libraries/angularjs/angular.min.js"></script>
		<script type="text/javascript" src="/mysite/site/scripts/main.js"></script>
	</body>
</html>
```

## Modules?
Yes, each module may add a __routes.json__ file inside their __configs__ directory
and it will be loaded.
