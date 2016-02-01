# TooBasic: Skins
## What are skins for __TooBasic__?
Throughout many frameworks, _skins_, also called _themes_ are an important topic
because it provides a way to change and enhance the look and feel of a site, even
in some cases provides the flexibility for users to choose the way they want to
see your site.

Let's have an example of this, if you visit
[Bootswatch.com](http://bootswatch.com/), you'll find a site that provide several
styles for [Bootstrap](http://getbootstrap.com/) based pages and it only depends
on which CSS file it uses.
This is a simple example of the power of changing a stylesheet on your site.

But the idea of _skins_ doesn't stop there, it may also include changes in the
structure of your HTML templates.
For this reason, __TooBasic__ provides the mechanism we're going to explain below.

## How to create a skin
Creating a skin for your site is almost as simple as creating a folder.
The first thing you have to do is to create a folder at __ROOTDIR/site/skins__ and
call it with the name for your skin, let's say __myskin__.
Inside it, you can create a structure similar to this:
```text
myskin/
myskin/images/
myskin/scripts/
myskin/snippets/
myskin/styles/
myskin/templates/
myskin/templates/action/
```

You can also create this same structure inside a module, for example inside
__ROOTDIR/modules/mymodule/skins/myskin__ (if your module is called __mymodule__),
and it will be considered when __myskin__ skin is active.

If you're wondering, you don't need to create all those folders, only those you
need.

## Different style
Let's say your site looks good either with light or dark colors and you are
thinking about using that as skins.
Let's also say that the structure may be the same, but the stylesheet to use has
to change entirely between skins.
To achieve this, we propose this structure:
```text
ROOTDIR/site/styles/mystyle.css
ROOTDIR/site/skins/myskin/styles/mystyle.css
```
First one for your light colored and default skin, and the second for the dark
one.
Also ensure that your CSS inclusion looks like this allowing __TooBasic__ to paste
the right URI:
```html
<link rel="stylesheet" type="text/css" href="{$ctrl->css('mystyle')}"/>
```

Following this idea, you can create templates, JavaScript scripts, images,
snippets, etc. with different contents in your skins.
Even modules can specify the way they want to be seen under certain skins.

## How to activate a skin
We've been talking about creating a skin, but we haven't said how to see it
working, in other way, activating it.
Well, there are three way in which a skin can be seen:

### URL specified
There's a debug URL parameter you can add to you URL call `skin` that will force
your page to consider the specified skin when rendering your page.
For example:

>http://www.example.com/?action=someaction&skin=myskin

This mechanism has the highest priority when choosing a current skin.

### By configuration
If you decide to configure a specific skin for your site, you can achieve this by
specifying it this way in your site's configuration file:
```php
<?php
$Defaults[GC_DEFAULTS_SKIN] = 'myskin';
```
This mechanism has the lowest priority when choosing a current skin.

### By session
As we said before, you may want to give your users the flexibility of choosing the
skin they want.
For that, __TooBasic__ provides a way to set a session value and get from it the
current skin.
In other words, if you do this somewhere in your site's code:
```php
<?php
. . .

\TooBasic\setSessionSkin('myskin');

. . .
```
You'll be setting the current skin to be _myskin_.

__Note__: If you use no parameters on this function, it will remove the session
setting and use the default skin, in other words, no skin.

## Debugging
If you are not sure which skin is being used, just add to your URL the debug
parameter called `debugskin` and it will prompt the current skin and some other
values you may need.

>http://www.example.com/?action=someaction&debugskin

## Multiple sites
If you're using more than one __TooBasic__ site in your PHP server, and we thank
you for that ^o^ , you may run into some issues when selecting a skin on one
those sites.
This may happen because the session data depends on the connection and not on the
page being requested.
To avoid these collisions you may set a specific suffix for the value store in
your session that is different for each __TooBasic__ site.
To do so, you must add something like this in your site's configuration file:
```php
<?php
$Defaults[GC_DEFAULTS_SKIN_SESSIONSUFFIX] = 'site1';
```

## Suggestions
If you want or need, you may visit these documentation pages:

* [Layouts](layout.md)
* [Controller Exports](controllerexports.md)
* [Snippets](snippets.md)

<!--:GBSUMMARY:MVC:6:Skins:-->
