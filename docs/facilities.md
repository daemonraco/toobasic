# TooBasic: Facilities
## What is this?
Facilities is a bunch of shell sys-tools you can use to ease your experience with
__TooBasic__.
Basically, these tools allow you, for example, to create a controller an its view
with a single command line.
If you used other frameworks, for example Ruby on Rails, you may find this
familiar, not as powerful as that, of course, this is _too basic_ after all :)

In this page we are going show them and explain their use.

## _controller_
The first and perhaps obvious choice is the _sys-tool_ to create controllers.
This tool allows you to create a basic and functional controller along with its
view, deploy it and, if active, set a route for it in one command line.
For example:
```plain
$ php shell.php sys controller create my_action
```
In the example we are trying to create an action called __my_action__ and this
operation will generate this artefacts:

* A new file at __ROOTDIR/site/controllers/my_action.php__
	* This file will contain the definition of class __MyActionController__.
* A new file at __ROOTDIR/site/templates/action/my_action.html__.
* A new route entry in __ROOTDIR/site/configs/routes.json__ (only when routes are
activated).

Once you've run this command, you can access this action at, for example:
> http://www.example.com/mysite/?action=my_action

### Cache
By default, a controller created by this _sys-tool_ is a controller that is stored
in cache.
But if you prefer, you can use a command like this one:
```plain
$ php shell.php sys controller create my_action --cached small
```
This will indicate that our new controller has to be stored for a quarter of the
maximum cache time.

These are the values supported by `--cached`:

* `double`: Double of the maximum time.
* `large`: Maximum time.
* `medium`: A half of the maximum time.
* `small`: A quarter of the maximum time.
* `NOCACHE`: Disables cache for this controller.
* others will act as `large`.

### Layout
The default here is the default... wut?!
Yes, by default, the new controller has no layout configuration, so it will use
the one set as default layout for your site, if any.
If you want your controller to use a different layout you can use a command like
the next one:
```plain
$ php shell.php sys controller create my_action --layout admin_layout
```
This way you'll be creating a controller that uses a layout called
__admin_layout__.

On the other side, if you want to create a controller that works without a layout,
you can say something like this:
```plain
$ php shell.php sys controller create my_action --layout NOLAYOUT
```

### Parameters
When your controller requires some parameters you can specify them this way:
```plain
$ php shell.php sys controller create my_action --param id --param username
```
This will have in mind this two parameters and create your controller with this
considerations:

* These parameters are required, otherwise it should prompt a 400 HTTP error.
* If cache is active, these parameters has to be use on its cache key.
* If routes are active, this controller must insert a route pattern looking like
*my_action/:id:/:username:*.

You may define as many parameters as you need, but remember, you have to specify
them in the order you want them.

### Module
If you want to create your action's files inside a module instead of your site,
you may specify a module name in this way:
```plain
$ php shell.php sys controller create my_action --module MyPlugin
```
And if that module doesn't exists it will create all the folders it needs.

### Removing an action
If you find yourself in the need to destroy a controller, due to a wrong creation
or the simple joy of smashing things, you may use this command:
```plain
$ php shell.php sys controller remove my_action
```
It will run almost in the same way than `create`, but it will delete files instead
of creating them.
