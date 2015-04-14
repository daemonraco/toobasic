# TooBasic: Representations
## What it this?
Similar to other systems, representations is an abstraction of database tables and their rows, where these are viewed as objects.

If you are not familiar with this it may seem heavy stuff, but it's not. Let's go through an example and see if it helps.

## A Table
Let's suppouse you have a table in your database called __ss_people__ (__ss__ is the prefix for all your tables) and it has this fields on each row:

* ppl_id: Numeric unique identifier, also primary key.
* ppl_name: Characters string.
* ppl_age: Numeric
* ppl_username: Unique characters string identifier.
* ppl_children: How many kids a represented person has.

Now let's suppouse you have this rows inside your table:

| ppl_id | ppl_fullname | ppl_age | ppl_username | ppl_children |
|:------:|--------------|:-------:|--------------|-------------:|
|   1    | John Doe     |   35    | deadpool     |            0 |
|   2    | Juan Perez   |   46    | hulk         |            2 |
|   3    | Jane Doe     |   27    | ironman      |            1 |

## Row Representation
The first thing you need to represent is each row as an object and to accomplish that we'll create a file with the next code and save it in __ROOTDIR/site/models/representations/PersonRepresentation.php__:
```php
<?php
class PersonRepresentation extends \TooBasic\ItemRepresentation {
	protected $_CP_IDColumn = "id";
	protected $_CP_ColumnsPerfix = "ppl_";
	protected $_CP_Table = "people";
}
```

As you may noticed, there are a few class properties that look rather important and related to your table, so let's explain them along others that may come in handy:

* `$_CP_IDColumn`: This is the column name (without its prefix) for an unique identifier of each row. This implies that your table must have a primary key composed by a single column.
* `$_CP_ColumnsPerfix`: To make things cleaner, this property specifies the prefix used on every column. We recommend this practice to make your queries more readable.
* `$_CP_Table`: This is the table name where all your represented rows are stored.
* `$_CP_NameColumn`: If you have a column that can point specifically each row you might want to set its name on this property (without prefix). In our example it would be __username__.
* `$_CP_ReadOnlyColumns`: It's a list of column names (without prefix) of columns that cannot be altered due to internal reason. In our example, let's say __age__ can't be modified because there's a different mechanism in charge of that.

With all this, out example mey become this:
```php
<?php
class PersonRepresentation extends \TooBasic\ItemRepresentation {
	protected $_CP_IDColumn = "id";
	protected $_CP_ColumnsPerfix = "ppl_";
	protected $_CP_Table = "people";
	protected $_CP_NameColumn = "username";
	protected $_CP_ReadOnlyColumns = array("age");
}
```
### CP?
Yup, "Core Property" :'(

## Table Representation
The second and last thing we need to represent is the table itself, and for that we take a similar action writing a code like the next one and storing it at __ROOTDIR/site/models/representations/PeopleFactory.php__ (sounds wierd to say "people factory", just ignore that fact):
```php
<?php
class PeopleFactory extends \TooBasic\ItemsFactory {
	protected $_CP_IDColumn = "id";
	protected $_CP_ColumnsPerfix = "rep_";
	protected $_CP_RepresentationClass = "person";
	protected $_CP_Table = "people";
}
```
Here you have some properties you already know, so let's explain those you don't

* `$_CP_RepresentationClass`: This is a way to point the class we've created in the previous step and it will be used to obtain row representations. For examples, the proper nane is "person" and it will translate into "PersonRepresentation" as a class name.
* `$_CP_OrderBy`: It's a piece of SQL code you may write to sort your results when their are fetched for database. In our example, it may by `protected $_CP_OrderBy = "ppl_fullname asc,ppl_username asc";`.

## Let's Use It
Now, for the sake of our example, we'll create a model that updates the amount of kids a person has. Let's write the next code and save it in __ROOTDIR/site/models/Kids.php__
```php
<?php
class KidsModel extends \TooBasic\Model {
	public function setPersonKids($personId,$kidsCount) {
		$person = $this->representation->people->item($personId);
		if($person) {
			$person->kids = $kidsCount;
			$person->persist();
		}
	}
	protected function init() {}
}
```
And that's it. Now, what just happend here?:

* First, we used the short access `$this->representation->people` to load and use our class __PeopeFactory__.
	* Also, this short access can be used inside a controller, a service or a shell tool.
* Then, we use a method of it called __item()__ to obtain a represented row for an specific id.
* We've checked if we actually obtained a row, that's our __if__.
* We've accessed one of its fields and changed its value (virtually, not in the database).
* and we've finally sent those changes to the data base (methods __persist()__).

As you can see here, you can access a row columns as if they were properties and without the need of their prefixes.

## Requirements
An obvious requirement is the use of a database, so you probably want to check on that.

Which database? the one you've set as default.

## Suggestions
If you want or need, you may visit this documentation pages:

* [Models](models.md)
* [Database Connections](databases.md)
