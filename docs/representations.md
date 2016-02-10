# TooBasic: Representations
## What it this?
Similar to other systems, representations is an abstraction of database tables and
their rows, where these are viewed as objects.

If you are not familiar with this it may seem heavy stuff, but it's not. Let's go
through an example and see if it helps.

## A table
Let's suppose you have a table in your database called __ss_people__ (__ss__ is
the prefix for all your tables) and it has these fields on each row:

* `ppl_id`: Numeric unique identifier, also primary key.
* `ppl_fullname`: Characters string.
* `ppl_age`: Numeric.
* `ppl_username`: Unique characters string identifier.
* `ppl_children`: How many kids a represented person has.

Now let's suppose you have these rows inside your table:

| ppl_id | ppl_fullname | ppl_age | ppl_username | ppl_children |
|:------:|--------------|:-------:|--------------|-------------:|
|   1    | John Doe     |   35    | deadpool     |            0 |
|   2    | Juan Perez   |   46    | hulk         |            2 |
|   3    | Jane Doe     |   27    | ironman      |            1 |

## Row representation
The first thing you need to represent is each row as an object and to accomplish
that we'll create a file with the next code and save it in
__ROOTDIR/site/models/representations/PersonRepresentation.php__:
```php
<?php
class PersonRepresentation extends \TooBasic\Representations\ItemRepresentation {
	protected $_CP_IDColumn = 'id';
	protected $_CP_ColumnsPerfix = 'ppl_';
	protected $_CP_Table = 'people';
}
```

As you may noticed, there are a few instance properties that look rather important
and are related to your table, so let's explain them along with others that may
come in handy:

* `$_CP_IDColumn`: This is the column name (without its prefix) for an unique
identifier of each row. This implies that your table must have a primary key
composed by a single column.
* `$_CP_ColumnsPerfix`: To make things cleaner, this property specifies the prefix
used on every column. We recommend this practice to make your queries more
readable.
* `$_CP_Table`: This is the table name where all your represented rows are stored.
* `$_CP_NameColumn`: If you have a column that can point specifically each row by
a characters string you might want to set its name on this property (without
prefix). In our example it would be __username__.
* `$_CP_ReadOnlyColumns`: It's a list of column names (without prefix) of columns
that cannot be altered due to internal reason. In our example, let's say __age__
can't be modified because there's a different mechanism in charge of that.
* `$_CP_ColumnFilters`: It's a list of column names (without prefix) associated
with a field filter to apply when it's read or saved.

With all this, out example may become this:
```php
<?php
class PersonRepresentation extends \TooBasic\Representations\ItemRepresentation {
	protected $_CP_IDColumn = 'id';
	protected $_CP_ColumnsPerfix = 'ppl_';
	protected $_CP_Table = 'people';
	protected $_CP_NameColumn = 'username';
	protected $_CP_ReadOnlyColumns = array('age');
}
```
### CP?
Yup, "Core Property" :'(

## Table representation
The second and last thing we need to represent is the table itself, and for that
we take a similar action writing a code like the next one and storing it at
__ROOTDIR/site/models/representations/PeopleFactory.php__ (it sounds weird to say
"people factory", let's just ignore that fact):
```php
<?php
class PeopleFactory extends \TooBasic\Representations\ItemsFactory {
	protected $_CP_IDColumn = 'id';
	protected $_CP_ColumnsPerfix = 'rep_';
	protected $_CP_RepresentationClass = 'person';
	protected $_CP_Table = 'people';
}
```
Here you have some properties you already know, so let's explain those you don't

* `$_CP_RepresentationClass`: This is a way to point the class we've created in
the previous step and it will be used to obtain row representations. In our
example, the right value would be __person__ and it will translate into
__PersonRepresentation__ as a class name.
* `$_CP_OrderBy`: It's a list of fields to use as sorting condition associated to
a sorting direction. In our example, it may by
`protected $_CP_OrderBy = array('fullname'=>'asc','username'=>'asc');`.

## Let's use it
Now, for the sake of our example, we'll create a model that updates the amount of
children a person has. Let's write the next code and save it in
__ROOTDIR/site/models/Kids.php__:
```php
<?php
class KidsModel extends \TooBasic\Model {
	public function setPersonKids($personId, $childrenCount) {
		$person = $this->representation->people->item($personId);
		if($person) {
			$person->children = $childrenCount;
			$person->persist();
		}
	}
	protected function init() {}
}
```
And that's it. Now, what just happend here?:

* First, we used the short access `$this->representation->people` to load and use
our class __PeopeFactory__.
	* Also, this short access can be used inside a controller, a service or a
shell tool.
* Then, we use a method of it called `item()` to obtain a represented row for an
specific id.
* We've checked if we actually obtained a row, that's our `if`.
* We've accessed one of its fields and changed its value (virtually, not in the
database).
* And we've finally sent those changes to the data base (methods `persist()`).

As you can see here, you can access a row columns as if they were properties and
without the need of their prefixes.

## Database
An obvious requirement is the use of a database, so you probably want to check on
that.

_Which database?_
The one you've set as default would be the first option, but you may change this
behavior by acquiring the factory in a different way, check the next example:
```php
class KidsModel extends \TooBasic\Model {
	public function childrenChanged($personId) {
		$personNew = $this->representation->people->item($personId);
		$personOld = $this->representation->people('backup')->item($personId);
		return !personOld || $personNew->children != $personOld->children;
	}
	protected function init() {}
}
```
In the example we've created a method that works with two database connections.
The variable `$personNew` represents a person store inside the default database
while `$personOld` may represent the same person stored in a backup database.
Based on that idea, the method `childrenChanged()` allows us to know if the
current person had changes in its children count since the last time the backup
was updated.

This is how you can obtain a people factory pointing to a different database, in
our case __backup__.

## New entries
A representation also allows you to create new entries and then modify its
properties.
For example:
```php
public function addPerson($name, $age) {
	$id = $this->representation->people->create();
	$person = $this->representation->people->item($personId);
	if($person) {
		$person->fullname = $name;
		$person->username = strtolower(preg_replace('/([ _-]+)/', '', $name));
		$person->age = $age;
		$person->children = 0;
		$person->persist();
	}
}
```
Here you see the use of a method called `create()` which inserts a new record in
your table and returns the inserted ID.
Of course, this magic has a few condition before it can work:

* The table requires to have an auto-incremental column.
* Each column must either have a default value or allow `NULL` values.
	* Except the auto-incremental one.

The reason behind these conditions is that __TooBasic__ attempts to insert a
completely empty row and expects to obtain an ID.
This also explains why you should retrieve this new row and set its values almost
immediately.

### Disabling empty creation
If for any reason you think that creating new entries the way __TooBasic__ does it
doesn't fit with your needs, you can disable this mechanism setting the _core
property_ `$_CP_DisableCreate` to `true`.
If you do so, every time something calls to `create()` you'll get an exception
with the next message allowing you to track the place where you should write some
code:

>Method 'create()' cannot be called directly.

Also, if you set a method's name instead of `true` to such _core property_ you'll
get an exception with a message similar to this (let's suppose you set its value
as `createWithName`):

>Method 'create()' cannot be called directly. Use 'createWithName()' instead.

## Field Filters
Let's suppose that our table gets a little more complex and it looks like this:

| ppl_id | ppl_fullname | ppl_age | ppl_username | ppl_children | ppl_active |           ppl_info          |
|:------:|--------------|:-------:|--------------|-------------:|:----------:|:----------------------------|
|   1    | John Doe     |   35    | deadpool     |            0 |     Y      | {"address":"street 236(B)"} |
|   2    | Juan Perez   |   46    | hulk         |            2 |     Y      | {"address":false}           |
|   3    | Jane Doe     |   27    | ironman      |            1 |     N      | {}                          |

As you can see, we've added two new fields:

* `ppl_active`: To indicate if our user can log in or not.
* `ppl_info`: Some arbitrary data stored as a JSON string.

Based on this, we should add some kind of logic to manage `ppl_active` with only
two values (a _boolean_) and `ppl_info` as string decoded into an object and back
to string before saving.

Here is where the concept of _field filters_ comes in handy.
Let's change our representation definition into something like this:
```php
<?php
class PersonRepresentation extends \TooBasic\Representations\ItemRepresentation {
	protected $_CP_IDColumn = 'id';
	protected $_CP_ColumnsPerfix = 'ppl_';
	protected $_CP_Table = 'people';
	protected $_CP_NameColumn = 'username';
	protected $_CP_ReadOnlyColumns = array('age');
	protected $_CP_ColumnFilters = array(
		'active' => GC_DATABASE_FIELD_FILTER_BOOLEAN,
		'info' => GC_DATABASE_FIELD_FILTER_JSON
	);
}
```
This simple modification tells __TooBasic__ to manage these fields the way we need
and the next time we write something like `$person->info` we are going to obtain a
`stdClass` object.

### Persistence policies
Every time you configure a JSON filter for a field, it's representation will
always act as persistence pending (a.k.a. _dirty_).
This strange behavior is cause due to a lack of control over the object in the
field.

Nonetheless, this doesn't mean that your database is constantly updated, remember
that you decide when to call `persist()`.

## Sub-representations
After some time coding tables you'll find rather common to have certain column in
a table that holds ids in another table.
For example, let's suppose these two tables:

* A table called `people`:

| ppl_id | ppl_fullname | ppl_age | ppl_country |
|:------:|--------------|:-------:|:-----------:|
|   1    | John Doe     |   35    |      1      |
|   2    | Juan Perez   |   46    |      1      |
|   3    | Jane Doe     |   27    |      2      |

* And another called `countries`:

| cou_id | cou_name  |
|:------:|-----------|
|   1    | Argentina |
|   2    | Germany   |
|   3    | Findland  |

A simple thing may want to do here is to get a person's entry and access its
related country object without writting a lot of code.

### Representation definition
To achieve this relationship we need to write a few specification in our person
representation.
```php
<?php
class PersonRepresentation extends \TooBasic\Representations\ItemRepresentation {
	protected $_CP_IDColumn = 'id';
	protected $_CP_ColumnsPerfix = 'ppl_';
	protected $_CP_Table = 'people';
	protected $_CP_ExtendedColumns = array(
		'country' => array(
			GC_REPRESENTATIONS_FACTORY => 'countries'
		)
	);
}
```
If you look at the _core property_ `$_CP_ExtendedColumns` you'll find a list with
two important things.

* The key of each entry is the name (without prefixes) of a column that holds id's
in another table.
* Each entry contains an array with specifications of how to manage the
relationship.

__Note__: This configuration assumes that you already created a representations
factory class called `CountriesFactory`.

### Relationship specifications
Each relationship specifications may have these values:

* `GC_REPRESENTATIONS_FACTORY` (required): Specifies the name of an items factory
that can solve ids in this relationship.
* `GC_REPRESENTATIONS_METHOD` (optional): Allows to define a specific name for the
method that will attend request for the column. This option solves possible method
name collisions.

### Usage
_Now, how do I use it?_
Once you have the configuration suggested above, you can write something like this
in your codes.
```php
class KidsModel extends \TooBasic\Model {
	public function promptCountry($personId) {
		$person = $this->representation->people->item($personId);
		if($person) {
			debugit(array(
				'ID only' => $person->country,
				'full object' => $person->country()
			), true);
		} else {
			debugit("Unknown id '{$personId}'.", true);
		}
	}
	protected function init() {}
}

### toArray()
Something you need to have in mind is that every time you access an associated
column and it returns a valid object, it will affect the results of calling
`toArray()` and instead of seeing just an ID you'll get and expanded object also
filter through its `toArray()` method.

### Setter
Yes, you can use these _magic methods_ to set new values with something like
`$person->country($otherCountry)`, but remember to give a valid representation
object as parameter (in our case a valid country).

## Suggestions
If you want or need, you may visit these documentation pages:

* [Models](models.md)
* [Database Connections](databases.md)
* [TooBasic's Search Engine](searchengine.md)

<!--:GBSUMMARY:Databases:2:Representations:-->
