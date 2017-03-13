# TooBasic: Adapters
## What are _adapters_
We won't say that _you've come to the wrong neighborhood_, but if you are here is
because you're looking for a way to do something interesting.

Let's first talk about what is an _adapter_ for __TooBasic__.
One of the ideas of this framework is to be able to work with more than one
technology providing you with a more flexible support.

For example, let's say you can't afford to have MySQL, for whatever reason, in
your site but SQLite will be great.
For this cases, __TooBasic__ has a generic way to interact with database
structures and, before talking to the real database, it relays actions to a
middleman, a specific class with the knowledge to translate those intended action
into the real deal. This classes are _adapters_ and we're going to explain them
in further sections and try to tell you how to create your own.

Anyway, if you are here, you probably know what _adapter pattern_ and _strategy
pattern_ are, and __TooBasic__ uses a bit of both.
If you don't know them, you may read these links:

* [Adapter Pattern](http://en.wikipedia.org/wiki/Adapter_pattern)
* [Strategy Pattern](http://en.wikipedia.org/wiki/Strategy_pattern)
* [Software Design Patterns](http://en.wikipedia.org/wiki/Software_design_pattern)

## Cache adapters
The most simple and common adapters inside __TooBasic__ are cache adapters and
they provide a way to store temporary data through several mechanisms.

Cache adapters provided by __TooBasic__ are:

* `TooBasic\Adapters\Cache\File`
* `TooBasic\Adapters\Cache\DBMySQL`
* `TooBasic\Adapters\Cache\Memcached`
* `TooBasic\Adapters\Cache\Memcache`
* `TooBasic\Adapters\Cache\NoCache`

If you want you can read more about them in the [cache documentation](../cache.md).

## Database structure maintainer adapters
Something that is always different between database engines is _how_ they provide
reflection of elements inside a database.
While you can use something like the next query to obtain tables in MySQL:
```sql
select  distinct(table_name) as name
from    information_schema.statistics
where   table_catalog = 'def'
 and    table_schema  = database();
```
For SQLite it would be:
```sql
select  distinct name;
from    sqlite_master
where   type = 'table'
```
And Oracle would be another story.

This kind of differences beg for adapters and that's the reason why __TooBasic__
provides these adapters:

* `TooBasic\Adapters\DB\SpecMySQL`
* `TooBasic\Adapters\DB\SpecSQLite`
* `TooBasic\Adapters\DB\SpecPosgreSQL`

For you this may be transparent, if you use a connection to a SQLite database you
won't need to do a thing, __TooBasic__ will choose the right adapter based on the
engine of your connection configuration.

## I want mine!
Let's say you want to use [CouchBase](http://www.couchbase.com/) instead of
_Memcached_ because it's what you need and because you already paid the license :)

You may try using `TooBasic\Adapters\Cache\Memcached` because _CouchBase_ provides
an access with the same interface, but that's not what you need.
In other words, you need a cache adapter for _CouchBase_.

To achieve this you have to go through the next 3 steps:

### Step 0: into a module
For this example will suppose you're creating a module/plugin for __TooBasic__
named __CouchBasePlugin__ and you'll have it deployed at
__ROOTDIR/modules/CouchBasePlugin__ with this basic folders list:

* `ROOTDIR/modules/CouchBasePlugin/configs`
* `ROOTDIR/modules/CouchBasePlugin/includes`

### Step 1: new adapter
The first thing is to create the adapter itself.
Let's write the next code at
__ROOTDIR/modules/CouchBasePlugin/includes/cacheadapter.php__:
```php
<?php
class CacheAdapterCouchBase extends \TooBasic\Adapters\Cache\Adapter {
	public function delete($prefix, $key) {

		. . .

	}
	public function get($prefix, $key, $delay = self::EXPIRATION_SIZE_LARGE) {
		$data = null;

		. . .

		return $data;
	}
	public function save($prefix, $key, $data, $delay = self::EXPIRATION_SIZE_LARGE) {

		. . . 

	}
}
```
Of course, you'll have to fill this method with some actual logic.

### Step 2: using the adapter
Now that you have an adapter, you need to change your site's configuration.
If you visit [this link](../cache.md), you'll find how to do this, but to save you
same time, add this line at __ROOTDIR/site/config.php__:
```php
<?php
$Defaults[GC_DEFAULTS_CACHE_ADAPTER] = '\\CacheAdapterCouchBase';
```

### Step 3: including the adapter
At this point you have an adapter and you have it set to be used, but your site is
dead.
This is because __TooBasic__ doesn't know where to get this adapter.
To solve this, add the next line at
__ROOTDIR/modules/CouchBasePlugin/configs/config.php__:
```php
<?php
$SuperLoader['CacheAdapterCouchBase'] = "{$Directories[GC_DIRECTORIES_MODULES]}/CouchBasePlugin/includes/cacheadapter.php";
```
This will tell __TooBasic__'s _super loader_ to get your adapter's class from the
right file.

## I want my database structure maintainer adapter
We told you how to add a cache adapter, but if you created your own database
structure maintainer adapter for Oracle inherited from
`\TooBasic\Adapters\DB\SpecAdapter`, you'll have to do something a little
different in the step 2:
```php
<?php
$Database[GC_DATABASE_DB_ADAPTERS]['oci'] = '\\MyDBSpecAdapterOracle';
```

## View adapters
All around this documentation we talk about Smarty as the engine to render HTML
templates as if it were the corner stone of __TooBasic__, but even though it saves
us a lot of headaches, it is just a library used through a view adapter.
This means you can create your own view adapter writing a class inherited from
`\TooBasic\Adapters\View\Adapter`.

Once you have your own adapter, you can change the default view adapter by adding
this line to your site's configuration:
```php
<?php
$Defaults[GC_DEFAULTS_VIEW_ADAPTER] = '\\TooBasic\\Adapters\\View\\MyEngine';
```

Also, if you are not sure if it works or not, you can just add it as a format
interpreter by doing this:
```php
<?php
$Defaults[GC_DEFAULTS_FORMATS]['myformat'] = '\\TooBasic\\Adapters\\View\\MyEngine';
```
And then access your URLs like this:

>http://www.example.com/mysite/?action=myaction&format=myformat

### Others
Other view adapters are:

* `\TooBasic\Adapters\View\Smarty`: The not-so-corner-stone :)
* `\TooBasic\Adapters\View\Dump`: Displays your controllers as `var_dump()`
results (`?format=dump`).
* `\TooBasic\Adapters\View\JSON`: Displays your controllers in JSON fromat
(`?format=json`).
* `\TooBasic\Adapters\View\Printr`: Displays your controllers as `print_r()`
results (`?format=print`).
* `\TooBasic\Adapters\View\Serialize`: Displays your controllers as `serialize()`
results (`?format=serialize`).
* `\TooBasic\Adapters\View\XML`: Displays your controllers in XML fromat
(`?format=xml`).
	* _Warning_: This depends on some PHP functions in status BETA.

## Suggestions
We suggest you visit these links:

* [Database Structure Specifications](../databasespecs.md)
* [Cache](../cache.md)
* [Query Adapter](queryadapter.md)

<!--:GBSUMMARY:Others:4:Adapters:-->
