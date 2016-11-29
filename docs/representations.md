# TooBasic: Representations
## What it this?
Similar to other systems, representations is an abstraction of database tables and
their rows, where these are viewed as objects.

If you are not familiar with this it may seem heavy stuff, but it's not. Let's go
through an example and see if it helps.

## A table
Let's suppose you have a table in your database called `ss_people` (`ss_` is the
prefix for all your tables) and it has these fields on each row:

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

## Core properties
Each table representation is given by three artifacts:

* And a container that holds all properties that define a representation.
* A class that may represent each row.
* A class that represents the table.

The first one is what we call _core properties_ and it is a JSON file in which we
define all configurations for our representation.

Following the example, we can create a file at
`ROOTDIR/site/models/representations/people.json` with this content:
```json
{
	"table": "people",
	"representation_class": "person",
	"columns_perfix": "ppl_",
	"columns": {
		"id": "id",
		"name": "username"
	},
	"order_by": {
		"fullname": "asc",
		"username": "asc"
	},
	"read_only_columns": [
		"age"
	]
}
```

This specification configures a table called `people` where each column has the
prefix `ppl_`.
Also it configures the to get get ids from column `ppl_id` and names from column
`ppl_username`.
Plus, whenever rows are retrieve, they'll be returned order by column
`ppl_fullname` and then by `ppl_usename`, both in ascending order.

We're also saying that column `ppl_age` can be changed using this representation,
perhaps because we have other means to charge it.

## Row representation
The next thing you need to represent is each row as an object and to accomplish
that we'll create a file with the next code and save it in
`ROOTDIR/site/models/representations/PersonRepresentation.php`:
```php
<?php
class PersonRepresentation extends \TooBasic\Representations\ItemRepresentation {
	protected $_corePropsHolder = 'people';
}
```
The property `$_corePropsHolder` tells our representation to load all its
configuration from the JSON file we've created in the previous step.

## Table representation
The last thing we need to represent is the table itself, and for that we take a
similar action writing a code like the next one and storing it at
`ROOTDIR/site/models/representations/PeopleFactory.php`
(it sounds weird to say "people factory", let's just ignore that fact):
```php
<?php
class PeopleFactory extends \TooBasic\Representations\ItemsFactory {
	protected $_corePropsHolder = 'people';
}
```

## Let's use it
Now, for the sake of our example, we'll create a model that updates the amount of
children a person has. Let's write the next code and save it in
`ROOTDIR/site/models/Kids.php`:
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
And that's it. Now, what just happened here?:

* First, we used the short access `$this->representation->people` to load and use
our class `PeopeFactory`.
	* Also, this short access can be used inside a controller, a service or a
shell tool, and any object making use of __MagicProps__.
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
<?php
class KidsModel extends \TooBasic\Model {
	public function childrenChanged($personId) {
		$personNew = $this->representation->people->item($personId);
		$personOld = $this->representation->people('backup')->item($personId);
		return !personOld || $personNew->children != $personOld->children;
	}
	protected function init() {}
}
```
In this example we've created a method that works with two database connections.
The variable `$personNew` represents a person stored inside the default database
while `$personOld` may represent the same person stored in a backup database.
Based on that idea, the method `childrenChanged()` allows us to know if the
current person had changes in its children count since the last time the backup
was updated.

This is how you can obtain a _people_ factory pointing to a different database, in
our case `backup`.

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
Here you see the use of a method called `create()` that inserts a new record in
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
property_ `disable_create` to `true` in your JSON file at
`ROOTDIR/site/models/representations/people.json`.
If you do so, every time something calls to `create()` you'll get an exception
with the next message allowing you to track the place where you should write some
code:

>Method 'create()' cannot be called directly.

Also, if you set a method's name instead of `true` to such _core property_ you'll
get an exception with a message similar to this (let's suppose you set its value
as `createWithName`):

>Method 'create()' cannot be called directly. Use 'createWithName()' instead.

## Field Filters
Let's suppose that our table gets a bit more complex and it looks like this:

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
Let's change our _core properties_ configuration into something like this:
```json
{
	"table": "people",
	"representation_class": "person",
	"columns_perfix": "ppl_",
	"columns": {
		"id": "id",
		"name": "username"
	},
	"order_by": {
		"fullname": "asc",
		"username": "asc"
	},
	"read_only_columns": [
		"age"
	],
	"column_filters": {
		"active": "boolean",
		"info": "json"
	}
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

A simple thing you may want to do here is to get a person's entry and access its
related country object without writing a lot of code.

### Representation definition
To achieve this relationship we need to write a few specification in our person's
_core properties_ specification:
```json
{
	"table": "people",
	"representation_class": "person",
	"columns_perfix": "ppl_",
	"columns": {
		"id": "id"
	},
	"order_by": {
		"fullname": "asc"
	},
	"extended_columns": {
		"country": {
			"factory": "countries"
		}
	}
}
```
If you look at the _core property_ `extended_columns` you'll find a list with
two important things:

* The key of each entry is the name (without prefixes) of a column that holds id's
in another table.
* Each entry contains an array with specifications of how to manage the
relationship.

__Note__: This configuration assumes that you already created a representations
factory class called `CountriesFactory`.

### Relationship specifications
Each relationship specifications may have these values:

* `factory`<sup>required<sup>: Specifies the name of an items factory that can
solve ids in this relationship.
* `method`<sup>optional<sup>: Allows to define a specific name for the method that
will attend request for the column. This option solves possible method name
collisions.

### Usage
_Now, how do I use it?_
Once you have the configuration suggested above, you can write something like this
in your codes.
```php
<?php
class KidsModel extends \TooBasic\Model {
	public function promptCountry($personId) {
		$person = $this->representation->people->item($personId);
		if($person) {
			debugit([
				'ID only' => $person->country,
				'full object' => $person->country()
			], true);
		} else {
			debugit("Unknown id '{$personId}'.", true);
		}
	}
	protected function init() {}
}
```

### toArray()
Something you need to have in mind is that every time you access an associated
column and it returns a valid object, it will affect the results of calling
`toArray()` and instead of seeing just an ID you'll get and expanded object also
filter through its `toArray()` method.

### Setter
Yes, you can use these _magic methods_ to set new values with something like
`$person->country($otherCountry)`, but remember to give a valid representation
object as parameter (in our case a valid country).

## Sub-lists
Based on the previous example for _sub-representations_ we may want to reach all
people of certain country.
If that's the case we may use another _core property_ called `sub_lists` and write
something like this inside our _core property_ JSON specification:
```json
{
	"table": "people",
	"representation_class": "person",
	"columns_perfix": "ppl_",
	"columns": {
		"id": "id"
	},
	"order_by": {
		"fullname": "asc"
	},
	"extended_columns": {
		"country": {
			"factory": "countries"
		}
	},
	"sub_lists": {
		"person": {
			"column": "country",
			"plural": "people"
		}
	}
}
```
This configuration provides three methods inside our _country_ representation that
can be used in this way:
```php
public function basicRun() {
	$country = $this->representation->countries->item(1);
	debugit([
		'people ids'   => $country->personIds(),
		'people items' => $country->people(),
		'a person' => $country->person(1)
	], true);
. . .
```
Yes, that configuration created three methods called `personIds()`, `person()` and
`people()` that will interact with our _people_ representation factory and return
a list of ids, one or even a list of fully loaded items.

Such configuration allows these fields:

* `column`<sup>required</sup>: Name of the column where this representation is
referred in the other table.
* `plural`: By default, __TooBasic__ assumes the sub-list name plus a `s` as
plural name, but if it's something different this parameter allows the change.
* `factory`: When the factory does not match the plural name, this option let's
you specify it.
* `id_method`: By default, __TooBasic__ assumes the sub-list name plus `Ids` as
method name to retrieve a list of ids, but if you want something else, you may use
this option.
* `items_method`: By default, __TooBasic__ assumes the plural name as method name
to retrieve a list of fully loaded items, but if you want something else, you may
use this option.
* `item_method`: By default, __TooBasic__ assumes the sub-list name as method name
to retrieve one fully loaded item, but if you want something else, you may use
this option.

## Suggestions
If you want or need, you may visit these documentation pages:

* [Models](models.md)
* [Database Connections](databases.md)
* [TooBasic's Search Engine](searchengine.md)
* [MagicProps](magicprop.md)

<!--:GBSUMMARY:Databases:2:Representations:-->
