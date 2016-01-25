# TooBasic: Forms Builder
## What is Forms Builder?
_Forms Builder_ is an internal mechanism of __TooBasic__ that allows to specify
HTML forms a JSON configuration.
The main idea in it is to create single specification for certain form and be able
to show it in more than one mode.

For example, if create a form to display an item in the database, other to edit
it, other when you have to remove it and another to create a new item, the first
thing you'll notice is that it is the same form where the only change is it's
behavior and maybe how many buttons it has at the end.
For those cases, _Forms Builder_ provides a way to specify fields once and some
specific properties depending on how the form should be built.

## Example
Before we start using _Form Builder_, let's think an example to work with.
Let's suppose you created a table in your system called `rooms` and it's
representation artifacts (_item-reprensentation_ and _items-factory_) representing
some part of some house.
Also you created these controllers (along with it's and views):

* `rooms`: To list all rooms.
* `room`: To show a room.
* `room_create`: To create a new room.
* `room_edit`: To modify a room.
* `room_delete`: To destroy a room.

Now let's say that you table `rooms` has these fields:

* `id`
* `name`
* `description`: a long text.
* `width`
* `height`
* `status`: Possible values are:
	* `AVAILABLE`
	* `OCCUPIED`
	* `REPAIR`
	* `UNKNOWN`

Now, _what would be the problem here?_
Let's say you want to use a single form definition for all your room controllers
(except controller `rooms`) and then invoke it inside your view.
The next sections will guide you in how to do such a thing.

## Using forms
### Creating a form
the first thing to do is to create an initial definition for our form and for that
we're going to run this command:
```text
$ php shell.php sys forms create table_rooms
```
This command will generate a new _Forms Builder_ specification file at
`ROOTDIR/site/forms/table_rooms.json` with some basic structure (not yet useful).
We won't go into specifics regarding the actual configuration, for that you can
click [this link](tech/forms.md) and get more information about it.

### Form name
Something we suggest you to set is the form's name, this simple property will be
used as form ID when building a HTML version and also in it's inputs IDs.
If you want you can omit this step because the previous one already used the file
name to set this property, but if you want to change it you can run this command
(it won't change the file name):
```text
$ php shell.php sys forms --set-name my_form --form table_rooms
```
From now on, will assume you've run this command.

### Form action and method
By default all your form are going to be sent to `#` (same page) and they will use
`GET` as sending method, but you can change this using commands like this:
```text
$ php shell.php sys forms --set-action '?action=someaction' --form table_rooms
$ php shell.php sys forms --set-method 'post' --form table_rooms
```
This will get you something like this:
```html
<form id="my_form" action="?action=someaction" method="post">
```
But of course, this could be what you want, but only for some usage of your form,
not for everything.
For example, if you use this form to make a creation _dry-test_ and only then you
need these values, you can run something like this:
```text
$ php shell.php sys forms --set-action '?action=someaction' --form table_rooms --mode dry_test
$ php shell.php sys forms --set-method 'post' --form table_rooms --mode dry_test
```
With this, you'll be using default values unless you build your form for
`dry_test` mode.

Something to have in mind is that `dry_test` is not an standard (known by
__TooBasic__ mode) which means may not apply some useful behaviors, but you can
use it anyway.
Standard modes are:

* `create`
* `edit`
* `remove`
* `view`

### Form type
The form type indicates how your form is going to be build, basically what HTML
structure it's going to have. You can set it this way:
```text
$ php shell.php sys forms --set-type bootstrap --form table_rooms
```
Available types are:

* `basic` (default): Simple structure where each label is prompted (in the HTML
code) alongside it's form control without any encasing.
* `table`: Inside the form, a table is prompted with two columns, left for labels
and right for form controls.
* `bootstrap`: This type follows the encasing suggested by _Bootstrap_ ar [this
link](http://getbootstrap.com/css/#forms).

### Form attributes
Something else you can set on a form are attributes for the HTML tag `<form>`, for
example:
```text
$ php shell.php sys forms --set-attribute role --value form --form table_rooms
```
This will generate something like this:
```html
<form id="my_form" action="#" method="get" role="form">
```
And you can also run a command like this:
```text
$ php shell.php sys forms --set-attribute onsubmit --value "return confirm('Are you sure you want to remove this room?')" --form table_rooms --mode remove
```
This will show a confirmation alert when your form is submitted if it was build
for _remove_ mode.

Something you should know about setting attributes is that you can specify
attributes without values, for example this _Angular.js_ flag:
```text
$ php shell.php sys forms --set-attribute ng-non-bindable --true --form table_rooms
```
This will give you something like this:
```html
<form id="my_form" action="#" method="get" role="form" ng-non-bindable>
```

### Form fields
Now that we have our basic structure, we need to add fields and for that we're
going to run these commands:
```text
$ php shell.php sys forms --add-field id --type hidden --form table_rooms
$ php shell.php sys forms --add-field name --type input --form table_rooms
$ php shell.php sys forms --add-field description --type text --form table_rooms
$ php shell.php sys forms --add-field width --type input --form table_rooms
$ php shell.php sys forms --add-field height --type input --form table_rooms
$ php shell.php sys forms --add-field status --type enum:AVAILABLE:OCCUPIED:REPAIR:UNKNOWN --value UNKNOWN --form table_rooms
```
This with specify our six fields with their types, and in the case of `status`, it
will also tell which values are accepted and the default value for it.

But let's go a little further, since we're using a for of type `bootstrap`, we can
add some nice classes to our form controls, for example:
```text
$ php shell.php sys forms --set-field-attribute id --name class --value input-sm --form table_rooms
$ php shell.php sys forms --set-field-attribute name --name class --value input-sm --form table_rooms
$ php shell.php sys forms --set-field-attribute description --name class --value input-sm --form table_rooms
$ php shell.php sys forms --set-field-attribute width --name class --value input-sm --form table_rooms
$ php shell.php sys forms --set-field-attribute height --name class --value input-sm --form table_rooms
$ php shell.php sys forms --set-field-attribute status --name class --value input-sm --form table_rooms
```
Now our form controls will appear thinner :)





@TODO







## Views
Now that you have your definition, you can use it in your view and we are going to
explain the difference for each one.
Now _modes_ have a meaning.

### Create view
Let's edit you view `room_create.html` and write something like this inside (using
_Smarty_):
```html
{$ctrl->formFor('table_rooms', false, 'create')}
```
This simple command will trigger the building of form `table_rooms` and generate
for mode `create`, this will basically means that all form controls will be shown
with their default values.

## Removing

## Extras parameters
_Sys-tool_ `forms` also accept parameters like:

* `--module`: to indicate that all artifacts have to be generated inside certain module.

## Suggestions
If you want or need it, you may visit these documentation pages:

* [Forms Specifications](tech/forms.md)