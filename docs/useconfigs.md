# TooBasic: Using Config Files
## Is it necessary an explanation?
Well yes, you already know every site has its own configuration files and
probably several logic block inside a site have their own configurations, so
what's new about this?

__TooBasic__ provides a mechanism to store and easily find two kind of
configuration files:

* user defined configuration files
* and system configuration files.

## User defined configuration files
To make it easier, we'll start with user defined configuration files.
Let's suppose you have a site that sells three types of paperboard boxes, each
type has a _name_, _width_, _length_ and _depth_, and you want to put such
configuration inside a file you can access from anywhere in your site.
For this example we're going to create a JSON configuration file with the next
content and store in __ROOTDIR/site/configs/boxes_types.json__:
```json
{
	"types": [{
		"name": "small",
		"width": 5,
		"length": 6,
		"depth": 10
	},{
		"name": "medium",
		"width": 10,
		"length": 12,
		"depth": 20
	},{
		"name": "large",
		"width": 20,
		"length": 24,
		"depth": 40
	}]
}
```
Why JSON? well JSON is an object specification very easy to use through
`json_decode` in PHP and it's already a _javascript_ object you can use directly
in your scripts.
If you want to keep things privately and access only from PHP files, you can
create a file called __ROOTDIR/site/configs/boxes_types.php__ containing something
like this:
```php
<?php
$boxesTypes = array(
	array(
		"name" => "small",
		"width" => 5,
		"length" => 6,
		"depth" => 10
	),array(
		"name" => "medium",
		"width" => 10,
		"length" => 12,
		"depth" => 20
	),array(
		"name" => "large",
		"width" => 20,
		"length" => 24,
		"depth" => 40
	)
);
```

Now, how do you access them?
Here is when the singleton `Paths` comes in handy, such singleton provides a
method called `configPath()`, take a look to this examples:
```php
<?php
//
// This line will find and return the path 'ROOTDIR/site/configs/boxes_types.php'.
echo \TooBasic\Paths::Instance()->configPath("boxes_types")."\n";
//
// This line will find and return the path 'ROOTDIR/site/configs/boxes_types.json'.
echo \TooBasic\Paths::Instance()->configPath("boxes_types", \TooBasic\Paths::ExtensionJSON)."\n";
```

Where is the magic in this? the real magic comes when you install a module, if it
has a file named __boxes_types.json__ in its _configs_ directory, it will be
overriding our first file which is useful if such module improves your site giving
it the ability to handle other types of boxes.

## I want em all!
At the end of the previous section we used the word "overriding" and it may no be
what you wanted to hear... I mean, read, well there's a work around for that and
it will look like this:
```php
<?php
print_r(\TooBasic\Paths::Instance()->configPath("boxes_types", \TooBasic\Paths::ExtensionPHP, true));
print_r(\TooBasic\Paths::Instance()->configPath("boxes_types", \TooBasic\Paths::ExtensionJSON, true));
```
Now we are using a `print_r` because we are going to get an array with all found
paths.

## Automatic config files
As we said, there's a second type of configuration file __TooBasic__ uses. This
files are automatically loaded (modules first and then site) and they are not
overridden between them.

### _config.php_
If you store a file at __ROOTDIR/site/configs/__ and/or
__ROOTDIR/modules/_modname_/configs__ and name it __config.php__, it will be
loaded automatically.
This allows you to give specific configurations for your site or your modules,
respectively.

### _config_shell.php_
This kind of file loads automatically before __config.php__ but only the current
execution is been run from a shell command line.

### _config_http.php_
Idem than the previous one, but when it's not run from a shell command line.

### Site config file
The last automatic file is always stored at __ROOTDIR/site/config.php__ and allows
you to give the final toches.

### Summary
So, how is it again?

* First, from every module folder and the the site folder: 
 * it loads every __config_http.php__ if it's not shell,
 * then every __config_shell.php__ if it is shell,
 * then every __config.php__,
* and finally it loads __ROOTDIR/site/config.php__.

And if it doesn't find any of these files, it won't say a thing and do as if there
were no problems.

## Suggestions
If you want you may visit this documentation pages:

* [MagicProp](magicprop.md)
