# TooBasic: Query Adapter
## What's a Query Adapter?
If you are making use of [Representations](../representations.md) somewhere in
your site, you may noticed that it always builds its queries with the proper
syntax for each known database type.
This kind of flexibility is achieve through query adapters, a set of complex
classes that let you build queries regardless of the current database type.

Through representations, this behavior is hidden, but you can make use of this
flexibility and we're going to explain it.

## Examples
Let's suppose these two tables for our examples in this page.
First we are going to use a list of people:

| ppl_id | ppl_fullname | ppl_age | ppl_children |
|:------:|--------------|:-------:|-------------:|
|   1    | John Doe     |   35    |            0 |
|   2    | Juan Perez   |   46    |            2 |
|   3    | Jane Doe     |   27    |            1 |

And then a list of invoices payed by these people.

| piv_id | piv_person | piv_payed | piv_data |
|:------:|:----------:|:---------:|----------|
|   1    |      3     |     Y     |   BLOB   |
|   2    |      2     |     Y     |   BLOB   |
|   3    |      2     |     N     |   BLOB   |

Of course, this structure is way too simple for a real system, but it will suffice
for our examples.

Also, in our examples we are going to suppose you have a model called `People`
stored at __ROOTDIR/site/models/People.php__ where we are going to place our
tables interactions with an initial code like this:
```php
<?php
class PeopleModel extends \TooBasic\Model {
	protected $_db = false;
	protected $_dbprefix = '';
	protected function init() {
		$this->_db = \TooBasic\Managers\DBManager::Instance()->getDefault();
		$this->_dbprefix = $this->_db->prefix();
	}
}
```
As you may already guest, we're going to need database access so we added a few
shortcuts to our model.

## Select
The first thing you may need is a way to obtain every record from certain table so
let's add a method like the next one:
```php
public function all() {
	$prefixes = array(
		GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
		GC_DBQUERY_PREFIX_COLUMN => 'ppl_'
	);
	$query = $this->_db->queryAdapter()->select('people', array(), $prefixes);
	$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
	return $stmt->execute($query[GC_AFIELD_PARAMS]) ? $stmt->fetchAll() : false;
}
```
Now, _what is this "thing" we wrote?_

One of the first things to have in mind is that a query adapter enforces the use
of prepared SQL statements because it is more efficient for database engines.
Therefore, this adapter method always provide at least two thing, a query string,
and a parameters array useful for statement executions.

Let's take a step by step look at our code:
```php
$prefixes = array(
	GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
	GC_DBQUERY_PREFIX_COLUMN => 'ppl_'
);
```
This part sets some required prefixes a simple select need to work.
After calling the adapter, this list may change change so be warned.
```php
$query = $this->_db->queryAdapter()->select('people', array(), $prefixes);
```
This step is the actual call to our query adapter asking to build a select query.
As you can see it is obtained from a database connection letting you relax about
which one to use.
The parameters you are seeing are:

* Table name.
* List of `where` conditions.
* Required prefixes.

Here remember that this returned query is not a simple string.

```php
$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
```
This piece uses a generated query string and prepares a statement.

```php
return $stmt->execute($query[GC_AFIELD_PARAMS]) ? $stmt->fetchAll() : false;
```
This final part uses the list of parameters and returns every found row.
Of course, this part returns everything with prefixes so be careful when using it.

### Order
Let's say your results are a mess and you want to sort it, so let's do this:
```php
public function all() {
	$prefixes = array(
		GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
		GC_DBQUERY_PREFIX_COLUMN => 'ppl_'
	);
	$query = $this->_db->queryAdapter()->select('people', array(), $prefixes, array(
		'fullname' => 'asc'
	));
	$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
	return $stmt->execute($query[GC_AFIELD_PARAMS]) ? $stmt->fetchAll() : false;
}
```
Here your results will be returned ordered by _fullname_.
You can also use `desc` to get a reverse order.

### Limit
Too many rows?
Let's limit it:
```php
public function all() {
	$prefixes = array(
		GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
		GC_DBQUERY_PREFIX_COLUMN => 'ppl_'
	);
	$query = $this->_db->queryAdapter()->select('people', array(), $prefixes, array(
		'fullname' => 'asc'
	), 100);
	$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
	return $stmt->execute($query[GC_AFIELD_PARAMS]) ? $stmt->fetchAll() : false;
}
```

### Specific condition
We've mentioned `where` conditions but we haven't used it, so let's suppose we
need a method to obtain a specific person and write something like this:
```php
public function person($id) {
	$prefixes = array(
		GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
		GC_DBQUERY_PREFIX_COLUMN => 'ppl_'
	);
	$query = $this->_db->queryAdapter()->select('people', array(
		'id' => $id
	), $prefixes);
	$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
	return $stmt->execute($query[GC_AFIELD_PARAMS]) ? $stmt->fetchAll() : false;
}
```
This code is almost the same, the difference here is the fact that we are giving a
`where` condition to filter a specific ID.

### Partial condition
Up until now, our `select` adapter is rather strict, but if we need to obtain
persons the a the last name _Doe_, we can use this trick:
```php
public function searchPeople($pattern) {
	$prefixes = array(
		GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
		GC_DBQUERY_PREFIX_COLUMN => 'ppl_'
	);
	$query = $this->_db->queryAdapter()->select('people', array(
			'*:fullname' => $pattern
		), $prefixes, array(
			'fullname' => 'asc'
		));
	$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
	return $stmt->execute($query[GC_AFIELD_PARAMS]) ? $stmt->fetchAll() : false;
}
```
After our requested query will have something similar to `ppl_fullname like
'%Doe%'` among its `where` conditions.
The trick here is the use of an asterisk as a field flag.

## Multi-table select
A more complex but common query is one that joins two or more tables connecting
them by some fields.
Our query adapters provide a way to do this that, in essence is the same to what
we've seen therefore we're going to focus on the differences.

First, let's write an example:
```php
public function allFull() {
	$prefixes = array(
		GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix
	);
	$query = $this->_db->queryAdapter()->select(array(
			'people',
			'invoices'
		), array(
			'C:piv_person' => 'ppl_id'
		), $prefixes, array(
			'ppl_fullname' => 'asc'
		), 100);
	$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
	return $stmt->execute($query[GC_AFIELD_PARAMS]) ? $stmt->fetchAll() : false;
}
```

* The first difference is our list of required prefixes, in this case we don't
provide a column prefix and that means we need to be more specific when naming
fields.
* The second is a list of table names instead of a single name.
* The third and perhaps the more important the use of a field flag named `C`.
This flag tells our adapter to treat this condition as a field to field
association.
	* You can use other filters as we explained before and also with partial
conditions.
* And among all, each table column is specified writing it's prefix.

## Insert
Our query providers also provide a way to insert new records and here we're going
to write an example.
Let's say you want to insert a new invoice for certain person, this means writing
something like this:
```php
public function addInvoice($person, $data, $status = 'N') {
	$prefixes = array(
		GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
		GC_DBQUERY_PREFIX_COLUMN => 'piv_'
	);
	$query = $this->_db->queryAdapter()->insert('invoices', array(
			'person' => $person,
			'data' => $data,
			'payed' => $status
		), $prefixes);
	$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
	return boolval($stmt->execute($query[GC_AFIELD_PARAMS]));
}
```
Again, let's talk about what we don't known.
This time we're using `insert()` and the thing that actually different is its
second parameter because it is a list of column names (without prefix) associated
with which values we want to insert.
And the last line now returns `true` unless there's an SQL error while executing.

Notice that we haven't mentioned field ID, that because where are supposing it is
an auto-incremented field.

__Trick__:
Something extra you may get from adapting a query is `$query[GC_AFIELD_SEQNAME]`.
This is usually empty, but in some cases like _SQLite_ it can be useful to run
things like `$this->_db->lastInsertId($query[GC_AFIELD_SEQNAME])`.

## Delete
To delete invoices, we can create a method like this:
```php
public function deleteInvoice($id) {
	$prefixes = array(
		GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
		GC_DBQUERY_PREFIX_COLUMN => 'piv_'
	);
	$query = $this->_db->queryAdapter()->delete('invoices', array(
			'id' => $id
		), $prefixes);
	$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
	return boolval($stmt->execute($query[GC_AFIELD_PARAMS]));
}
```
Here, the parameter that have changed is the second one.
In this case it is a `where` condition similar to a `select` adaptation and yes,
it may use partial conditions.

## Update
If you need to update an invoice pay status, you may write this:
```php
public function setInvoicePayed($id, $status = 'Y') {
	$prefixes = array(
		GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
		GC_DBQUERY_PREFIX_COLUMN => 'piv_'
	);
	$query = $this->_db->queryAdapter()->update('invoices', array(
			'payed' => $status
		), array(
			'id' => $id
		), $prefixes);
	$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
	return boolval($stmt->execute($query[GC_AFIELD_PARAMS]));
}
```
This kind of adaptation is a kind of mixed between a `select` and an `insert`.
The second parameter is the list of fields to be modified with their new values.
And the third one is a `where` condition with the behavior we already know.

## __TooBasic__ philosophy
Yes, sometimes your queries are more complex than this and these adaptation are
useless, well, remember that __TooBasic__ is a _too basic_ framework.
It provides you with many solutions, but not against the universe, of course.

## Suggestions
Here you have a few links you may want to visit:

* [Adapters](adapters.md)
* [Databases](../databases.md)
* [Representations](../representations.md)
