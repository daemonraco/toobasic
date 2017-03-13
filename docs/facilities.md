# TooBasic: Facilities
## What is this?
Facilities is a bunch of shell _sys-tools_ you can use to ease your experience
with __TooBasic__.
Basically, these tools allow you, for example, to create a controller an its view
with a single command line.
If you used other frameworks, for example Ruby on Rails, you may find this
familiar, not as powerful as that, of course, this is _too basic_ after all :)

In this page we are going show them and explain their use.

## Sys-tool _controller_
The first and perhaps obvious choice is the _sys-tool_ to create controllers.
This tool allows you to create a basic and functional controller along with its
view, deploy it and, if active, set a route for it in one command line.
For example:
```plain
$ php shell.php sys controller create my_action
```
In the example we are trying to create an action called __my_action__ and this
operation will generate these artefacts:

* A new file at __ROOTDIR/site/controllers/my_action.php__
	* This file will contain the definition of class called
	__MyActionController__.
* A new file at __ROOTDIR/site/templates/action/my_action.html__.
* A new route entry in __ROOTDIR/site/configs/routes.json__ (only when routes are
activated).

Once you've run this command, you can access this action at, for example:

>http://www.example.com/mysite/?action=my_action

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
_The default here is the default... wut?!_
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
`my_action/:id:/:username:`.

You may define as many parameters as you need, but remember, you have to specify
them in the order you want them.

### Module
If you want to create your action's files inside a module instead of your site,
you may specify a module name in this way:
```plain
$ php shell.php sys controller create my_action --module MyPlugin
```
And if that module doesn't exists it will create every folder it needs.

### Removing an action
If you find yourself in the need to destroy a controller, due to a wrong creation
or the simple joy of smashing things, you may use this command:
```plain
$ php shell.php sys controller remove my_action
```
It will run almost in the same way than `create`, but it will delete files instead
of creating them.

## Sys-tool _shell_
Shell tools allow you to perform scheduled tasks or some background operation from
a command line, so it would be useful if you can have something that creates one
of these _things_ so you can start typing your logic.
Well that's what this tool is for.

if you want to know more about shell tools go to [this link](shelltools.md).

### Creating a simple tool
Let's say you want to create a new tool called __manage_users__ to perform some
important maintenance tasks on your site's users and it sounds like a headache to
start writing such tool from the scratch.
Basically you should run a command like this one:
```plain
$ php shell.php sys shell create manage_users --type tool
```
This command will:

* Create a file at __ROOTDIR/site/shell/tools/manage_users.php__.
	* It will also create required directories.
* It will define a class called __ManageUsersTool__ and add initial methods to it.

### Basic parameters
Now let's say you need to read parameters to do some stuff inside your tool.
This _sys-tool_ provides your with a way to specify possible parameters executing
something like this:
```plain
$ php shell.php sys shell create manage_users --type tool --param dead-users --param id:M
```
By doing this you are asking to have two parameters at least, one called
__DeadUsers__ and other called __Id__.
The first parameter will be a simple parameter that requires no values and its
triggers will be `--dead-users` and `-d`.
The second will be a multi-value parameter, which means you can specify it as many
times as you need on a command line, and its triggers will be `--id` and `-i`.

Now here is a tricky thing, we've said _multi-value_, _but how did that happened?_
Well, if you look closely we've added a suffix `:M` on the name of our parameter
meaning that it is a multi-value parameter.
You can also use `:V` for a parameters with only one possible value or `:N` (the
default) for no values.

### Master parameters
On the previous example, __DeadUsers__ seems to be a specific task you would
probably want to attend it in a separated method inside your tool.
In these cases you need to change a little the command and do something like this:
```plain
$ php shell.php sys shell create manage_users --type tool --master-param dead-users --param id:M
```
This will create a protected method called __ taskDeadUsers()__ inside your tool
and will be automatically called when you use `--dead-users` or `-d`.

### What do I have?
At this point you may end up with a tool looking like this:
```php
<?php

use TooBasic\Shell\Option as TBS_Option;

class ManageUsersTool extends TooBasic\Shell\ShellTool {
        //
        // Constants.
        const OptionDeadUsers = 'DeadUsers';
        const OptionId = 'Id';
        //
        // Protected methods.
        protected function setOptions() {
                $this->_options->setHelpText("TODO tool summary");

                $text = "TODO help text for: '--dead-users', '-d'.";
                $this->_options->addOption(TBS_Option::EasyFactory(self::OptionDeadUsers, ['--dead-users', '-d'], TBS_Option::TYPE_NO_VALUE, $text, 'value'));

                $text = "TODO help text for: '--id', '-i'.";
                $this->_options->addOption(TBS_Option::EasyFactory(self::OptionId, ['--id', '-i'], TBS_Option::TYPE_MULTI_VALUE, $text, 'value'));
        }
        protected function taskDeadUsers($spacer = "") {
                debugit("TODO write some valid code for this option.", true);
        }
}
```
And you'll probably be able to do something like this:
```plain
$ php shell.php tool manage_users --help
TODO tool summary

        --help, -h
                Shows this help text.

        --version, -V
                Shows this tool's version number.

        --info, -I
                Shows this tool's information.

        --dead-users, -d
                TODO help text for: '--dead-users', '-d'.

        --id <value>, -i <value>
                TODO help text for: '--id', '-i'.
```

### In a module
If you want your tool to be created inside a module you may run this:
```plain
$ php shell.php sys shell create manage_users --type tool --module my_module --master-param dead-users --param id:M
```

### Destroy it
If you need to remove it, do this:
```plain
$ php shell.php sys shell remove manage_users --type tool
```

### Cron shell tool
If you want create a tool that can be called on a _cron_ execution, just change
the type. Something like this:
```plain
$ php shell.php sys shell create manage_users --type cron --module my_module --master-param dead-users --param id:M
```
and you'll end up with something like this:
```php
<?php

use TooBasic\Shell\Option as TBS_Option;

class ManageUsersCron extends TooBasic\Shell\ShellCron {
        //
        // Constants.
        const OptionDeadUsers = 'DeadUsers';
        const OptionId = 'Id';
        //
        // Protected methods.
        protected function setOptions() {
                $this->_options->setHelpText("TODO tool summary");

                $text = "TODO help text for: '--dead-users', '-d'.";
                $this->_options->addOption(TBS_Option::EasyFactory(self::OptionDeadUsers, ['--dead-users', '-d'], TBS_Option::TYPE_NO_VALUE, $text, 'value'));

                $text = "TODO help text for: '--id', '-i'.";
                $this->_options->addOption(TBS_Option::EasyFactory(self::OptionId, ['--id', '-i'], TBS_Option::TYPE_MULTI_VALUE, $text, 'value'));
        }
        protected function taskDeadUsers($spacer = "") {
                debugit("TODO write some valid code for this option.", true);
        }
}
```

## Sys-tool _table_
This is perhaps the most complex _sys-tool_ of __TooBasic__ if you think about the
things it does, but its usage is not much different than _sys-tool_ shell or
controller.

Let's make an example and work on it with this _sys-tool_.
Suppose you want to represent _people_ inside your database and each person has
these attributes:

* `name`: First and last name.
* `age`
* `children`: The amount of children this person has.
* `notes`: Some reminder you may need related to a person.
* `hair_color`: This is a list of a few possible values:
	* `brown`
	* `redhead`
	* `blonde`
	* `black`
	* `other`

Let's have this in mind on the next sub-sections.

__Note__: we are going to assume you have a default database connection
configured.

### Creating a table
Based on the example let's run a command line like this one on your site's
__ROOTDIR__:
```bash
$ php shell.php sys table create person -c name:varchar -c age:int -c children:int -c notes:text -c hair_color:enum:brown:redhead:blonde:black:other
```
_Now what the heck is that?!_
Basically, this command is telling __TooBasic__ to create all the required
artefacts for a new table with some parameters.
But, as Jack would say, let's cut it into pieces.

The parameters `create person` means that you need to create a table to hold
information for each person on your site.
In other words, this means you want to create a table called __persons__ (prefixes
will be added automatically based on your connections configuration).

Perhaps something you may not like about this is the fact you like to say _people_
instead of _persons_, for whatever reason.
If that's the case, you may want to consider this slight change:
```bash
$ php shell.php sys table create person -P people -c name:varchar -c age:int -c children:int -c notes:text -c hair_color:enum:brown:redhead:blonde:black:other
```
This is almost the same command, but now your are saying that the plural of
_person_ is _people_, and also you're asking to name your table __people__.

The rest of the parameters starting with `-c` are columns specifications.
Think of each column specification as a complex parameter with multiple parts
separated by colon characters.
Always the first part is the name without prefixes for you columns.
The second parameter would the type of your column, an these are possible values:

* `int`: similar to `int(11)` or `number(11)`.
* `blob`
* `float`
* `text`
* `timestamp`
* `enum`: A field that can contain a certain list of possible values. When a
column of this type is specified, all sub-parameter from the thrid and on are used
as possible values. If there's no third sub-parameter, __Y__ and __N__ are used as
possible values. Always the first one is considered as a default value.
* `varchar`: similar to `varchar(256)` or `varchar2(256)`.
* other values are assume `varchar`, even when there's no second sub-parameter.

### What do I get?
After you run this command line, you'll get a list of items you can use right
away.

* The first and most important is the JSON specification for your table already
saved in a location where __TooBasic__ can load it and apply it (if your system is
not flagged as installed).

* The second is a list of action to manage information inside your new table and
their URLs may look like this:
	* http://www.example.com/?action=people to list all the items in your table. We
	recommend you start here
		* No pagination included.
	* http://www.example.com/?action=person_add to add a new person.
	* http://www.example.com/?action=person&id=123 to view information of a specific
	person, in this case the one with ID _123_.
	* http://www.example.com/?action=person_edit&id=123 to edit a specific person's
	information.
	* http://www.example.com/?action=person_delete&id=123 to remove a specific person
	from your data base.

* The third set of generated artefacts are an items representation for your table
and also a factory for those representations.
This enables you to do something like this (for example, inside a controller):
```php
<?php
	. . .

	$factory = $this->representation->people;
	debugit($factory->items(), true);

	. . .
```
* When you have routes active, the forth thing you'll get are new routes for each
new action and they may look like these:
	* `people`
	* `person/:id:`
	* `person_add`
	* `person_edit/:id:`
	* `person_delete/:id:`

Also, all required directories are created if they are not present.

__Warning__: Every generated template is Smarty compatible, if you're using a
different view adapter, this may give you a headache.
There is a way in which you can solve this problem, but that is heavy stuff and
this is not the right moment to talk about it -___-

### Database type
Now if you look closely, the JSON file generated with your table specification has
an index for a primary key over an automatic field called `id`.
The problem is, if you're using MySQL, the property `autoincrement` implies an
automatic primary key called `PRIMARY`.
To avoid this kind of issues you can add a parameter specifying your targeted
database system and if there's a trick for it, it will be taken in consideration.
For example, if you do this:
```bash
$ php shell.php sys table create person -P people -c name:varchar --type mysql -c age:int -c children:int -c notes:text -c hair_color:enum:brown:redhead:blonde:black:other
```
That primary key won't be specified and your MySQL database will end up with only
one index for that column.

### Automatics
When a table specification is created by this _sys-tool_ a list of default columns
are generated:

* `id`: An integer autoincremented column to uniquely identify each row.
* `create_date`: A time stamp for the date when each row was inserted.
* `indexed`: A varchar field that may contain __Y__ or __N__ as values (this last
one is the default) and it will be implemented in future versions.

Another automatic concept is the prefix for each column.
By default the first three letters of your table's name are taken and in our
example it would be `peo_`.

### Raw
If your are trying to create a basic table and you don't need those automatic
columns, you can use the parameter `--raw` and it will only create the columns
you've specified.
Of course, this means no controller or representation will be generated.
Only the JSON file specifying your table will be created.

### Removal
Yes you can use `remove` instead of `create` to remove all artefacts.

### Module
Also yes, you can use `--module` to specify a module in which your artefacts has
to be created.

### Connection?
If your new table has to use a different connection configuration, you may specify
its name using `--connection`.

### Bootstrap
If you use the parameter `--bootstrap` all templates will be generated with a
basic Twitter Bootstrap structure.
Visit the album [__TooBasic: Table Scaffold
Screenshots__](https://imgur.com/a/8rQA4) to take look at a few screenshots, and
here's one of those images:
![scaffold_table_03_table_with_data](http://i.imgur.com/4osCZTT.png)

### Predictive
If you take a look around you'll find that [representations](representations.md)
allow the specification of a _name_ column that can act as a unique index inside a
table and then look for entries by name.
_Sys-tool_ `table` allows a way to use this functionality and generate a better
set of files.
Following our examples let's consider the next command:
```bash
$ php shell.php sys table create person -P people -c name:varchar -nf name -c age:int -c children:int -c notes:text -c hair_color:enum:brown:redhead:blonde:black:other
```
Just by adding the parameters `-nf name` (or even `--name-field name`) we saying
that this field has to be used for that unique index.
This commands activates these changes:

* The representation now uses this field as name.
* The factory too and also uses it as order for selections.
* The column gets its own unique index.

Some conditions:

* The column has to be also specified with command `--column`.
* Such column has to be of type `varchar`.
* It's not a _raw_ table.

Now why we call this section predictive?
Happens that this parameter also triggers the generation of a service that allows
the search of entries by a partial value in this column flagged as _name_ and it
can be access with something like this:

>http://www.example.com/mysite/?service=people_predictive&pattern=john

>http://www.example.com/mysite/?service=people_predictive&pattern=john&limit=25

### jQueryUI Autocomplete
Following the predictive functionality, if we add the parameter `-ac` or
`--autocomplete` it will generate a javascript file named
__people_predictive_name.js__ containing instructions to add [_jQueryUI_
autocomplete](http://api.jqueryui.com/autocomplete/) behavior to any HTML tag with
a CSS class named 'people_predictive_name'.

Now, if it is what you wanted, you'll have to consider a few previous steps:

* This JS asset is dependant on jQueryUI so make sure you are including its JS
files in your layout and also its CSS assets.
* You need to include the JS asset called `toobasic_asset` because it holds some
basic functions required by this automatic files.
* And don't forget to include the asset itself :)

### Forms Builder
By default, all forms used by generated views of this _sys-tool_ use _Forms
Builder_ specification.
This means that you view will be using a JSON file, in our examples, called
`table_people.json` stored at `ROOTDIR/site/forms` or a similar folder if you're
generating assets inside a module.

This provides you with the advantage of add, removing and sorting field across
your table's view by touching a single file.

Even though, if you prefer to have a complete HTML form inside each of your view
avoiding the use of _Forms Builder_, you can use this _sys-tool_ adding the
parameter `--no-forms-builder`.

If you want to know more about _Forms Builder_, visit [this link](forms.md).

## Sys-tool _service_
This _sys-tool_ is very similar to _sys-tool_ _controller_ because it also creates
a controller, but in this case it will be accessible at something like
`?service=<service-name>`.
Again the functionality generated will be very simple and will give a place to
start your new service.
For example:
```plain
$ php shell.php sys service create request_info
```
In the example we are trying to create a service called __request_info__ and this
operation will generate these artefacts:

* A new file at __ROOTDIR/site/services/request_info.php__
	* This file will contain the definition of class __RequestInfoService__.

Once you've run this command, you can access this service at, for example:

>http://www.example.com/mysite/?service=request_info

### Features
In the same way you do with controllers, you may specify:

* Cache
```plain
$ php shell.php sys service create request_info --cached small
```
* Parameters
```plain
$ php shell.php sys service create request_info --param id --param username
```
* Module
```plain
$ php shell.php sys service create request_info --module MyPlugin
```

Layout would make no sense because services don't have such concept.

### Removing a service
If you find yourself in the need to destroy a service, you may use this command:
```plain
$ php shell.php sys controller remove request_info
```

## Suggestions
Here are some links you may like to read:

* [Shell Tools and Cron](shelltools.md)
* [Representations](representations.md)
* [Forms Builder](forms.md)

<!--:GBSUMMARY:Facilities & Scaffolds:1:Facilities:-->
