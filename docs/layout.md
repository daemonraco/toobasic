# TooBasic: Using Layouts
## What is a Layout?
Well, you know, the part of your page that surrounds your main content an usually
stays always the same.
## Create a Site with Layout
### Main Content
Let's follow an old example an create an action called __myaction__ this way:

* A controller in __ROOTDIR/site/controllers/myaction.php__:
```php
<?php
class MyactionController extends \TooBasic\Controller {
	protected function basicRun() {
		$this->assign("helloaciton", "Hello World!");
		return true;
	}
}
```
* A template in __ROOTDIR/site/templates/action/myaction.html__:
```html
        <h4>{$helloaciton}</h4>
```
* And visit it, for example, at:
> http://www.example.com/?action=myaction

After all of this you'll find a page saying __Hello World!__.
### Nav Bar
Let's create another action to emulate a navigation bar and call it __mynav__:

* A controller in __ROOTDIR/site/controllers/nav.php__:
```php
<?php
class MynavController extends \TooBasic\Controller {
	protected function basicRun() {
		$this->assign("hellonav", "I'm a nav");
		return true;
	}
}
```
* A template in __ROOTDIR/site/templates/action/mynav.html__:
```html
        <h4 style="color:red;">{$hellonav}</h4>
```
* Why? You'll see.

### Layout
Now that you have a main content to show, let's create another controller called
__mylayout__ for your layout:

* A controller in __ROOTDIR/site/templates/action/mylayout.html__:
```php
<?php
class MylayoutController extends \TooBasic\Layout {
	protected function basicRun() {
		return true;
	}
}
```
* A template in __ROOTDIR/site/templates/action/mylayout.html__:
```html
<!DOCTYPE html>
<html>
    <head>
        <title>HELLO</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
{$ctrl->insert("mynav")}
%TOO_BASIC_ACTION_CONTENT%
    </body>
</html>
```
* And visit it, for example, at:
> http://www.example.com/?action=myaction&layout=mylayout

### Config
As you may see, using a parameter called __layout__ on each url may not be pretty,
therefore you can configure your site by creating a file in
__ROOTDIR/site/config.php__ with this content:
```php
<?php
$Defaults["layout"] = "mylayout";
```
In this way, you may enter your page just specifying your action.

Also, you may do this to have a default action:
```php
<?php
$Defaults["layout"] = "mylayout";
$Defaults["action"] = "myaction";
```
And then access this way:
> http://www.example.com/

## Doubts
### What the Heck is That?
You've probably seen an extrange word/constant/keyword/thing called
__%TOO_BASIC_ACTION_CONTENT%__, this is a keyword you must use inside your
template in the place where you want to put your main content.
### Insert?
If you look closely to our example you'll find something like
`{$ctrl->insert("mynav")}`. This sentence "inserts" the results of an action
called __mynav__. Of course you can import that part with AJAX later on, but in
this way, that part will be add to your layout cache when it's activated.

## Wrong Layout?
If for any reason you create an action that requires a different layout, you can
change it writing something like this:
```php
<?php
class MyactionController extends \TooBasic\Controller {
	protected $_layout = "otherlayout";
	protected function basicRun() {
		. . .
	}
}
```
And if you don't want a layout at all, you may write this:
```php
<?php
class MyactionController extends \TooBasic\Controller {
	protected $_layout = false;
	protected function basicRun() {
		. . .
	}
}
```

## Suggestions
If you want you may visit this documentation pages:

* [Snippets](snippets.md)
* [Redirections](redirections.md)
