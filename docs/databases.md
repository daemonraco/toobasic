# TooBasic: Databases
## What is it, really?
In a complicated way we may say it is a "wrapper's wrapper" and it won't make any
sense, but it's the truth because __TooBasic__ database administration is a
wrapper for [PDO](http://php.net/manual/en/book.pdo.php), which itself is a
generic wrapper for databases.
So if you are familiar using PDO, you may find this easy to understand.

## Configuration
By default, __TooBasic__ has no database configuration and it requires some
specific setting we will try to explain.

The first thing you need to know is the existence of a global array called
`$Connections` in which every connection setting must be added.
As an example we're going to suppose that we have a database called __census__
stored in a [MySQL](http://dev.mysql.com/doc/) server inside our current server.
Also, we're going to suppose that our database credentials are __censususr__ for
username and the same for password, and that every table has a prefix __ss___.
Based on all of this, you might end with a configuration similar to this:
```php
<?php
$Connections[GC_CONNECTIONS_DB]['census'] = [
	GC_CONNECTIONS_DB_ENGINE => 'mysql',
	GC_CONNECTIONS_DB_SERVER => 'localhost',
	GC_CONNECTIONS_DB_PORT => false,
	GC_CONNECTIONS_DB_NAME => 'census',
	GC_CONNECTIONS_DB_USERNAME => 'censususr',
	GC_CONNECTIONS_DB_PASSWORD => 'censususr',
	GC_CONNECTIONS_DB_PREFIX => 'ss_'
];
```
In this way, you create a configuration to connect to a database just using its
name as reference.

### Where to Place It?
Probably, the best place to write this configuration is in your site's
configuration file at __ROOTDIR/site/config.php__.

## Let's Make a Query
Now that we have a database configuration, we'll try to obtain some information
from it and for that we are going to create a quick model:
```php
<?php
class SomeModel extends \TooBasic\Model {
	public function printSomeData(){
		$db = \TooBasic\Managers\DBManager::Instance()->census;
		$prefix = $db->prefix();
		$result = $db->query("select * from {$prefix}sometable");
		foreach($result->fetchAll() as $row) {
			debugit($row);
		}
	}
	protected function init(){}
}
```
This will execute a __select__ in your database __census__ and print out every
row.
Not very useful and robust, but gives an idea of how it works.

Also, you can go further and write something like this:
```php
<?php
class SomeModel extends \TooBasic\Model {
	public function printSomeData($type) {
		$db = \TooBasic\Managers\DBManager::Instance()->census;
		$prefix = $db->prefix();
		$stmt = $db->prepare("select * from {$prefix}sometable where smt_type = :type");
		$stmt->execute([':type' => $type]);
		foreach($stmt->fetchAll() as $row) {
			debugit($row);
		}
	}
	protected function init(){}
}
```
This makes use of prepared queries improving your database performance.

## Defaults
To make things a little bit easer, there are a few extra configuration you can
make to set a database as default:

* __Main Database__: You can set the name of your default database configuration
in `$Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_DB]`. This is
the connection to be used on every database access where no database-name is
specified.
    * It's recommended to set this global to avoid error when using databases.
* __Cache Database__: If you site uses cache on database, you may want to set such
information into a different connection. If that's the case, you can configure a
database-name into
`$Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_CACHE]`.
    * The default behavior is to use the default database.

## SQLite connection
A connection to a SQLite database should be transparent in most cases but its
configuration is slightly different, take a look to this example:
```php
<?php
$Connections[GC_CONNECTIONS_DB]['census'] = [
	GC_CONNECTIONS_DB_ENGINE => 'sqlite',
	GC_CONNECTIONS_DB_SERVER => "{$Directories[GC_DIRECTORIES_CACHE]}/census.sqlite3",
	GC_CONNECTIONS_DB_PORT => false,
	GC_CONNECTIONS_DB_NAME => false,
	GC_CONNECTIONS_DB_USERNAME => false,
	GC_CONNECTIONS_DB_PASSWORD => false,
	GC_CONNECTIONS_DB_PREFIX => 'ss_'
];
```
The main difference is we don't have a server providing our databases, we have a
binary file instead, that's why our server parameters have changed this way.
In the example, our database is located inside a known writable directory, but you
can store it wherever you need in your server.

## PostgreSQL connection
A connection to a PostgreSQL database would have a similar configuration to MySQL
databases, and it may look like this example:
```php
<?php
$Connections[GC_CONNECTIONS_DB]['census'] = [
	GC_CONNECTIONS_DB_ENGINE => 'pgsql',
	GC_CONNECTIONS_DB_SERVER => 'localhost',
	GC_CONNECTIONS_DB_PORT => false,
	GC_CONNECTIONS_DB_NAME => 'census',
	GC_CONNECTIONS_DB_USERNAME => 'censususr',
	GC_CONNECTIONS_DB_PASSWORD => 'censususr',
	GC_CONNECTIONS_DB_PREFIX => 'ss_'
];
```
As you can see, the main difference is the engine/driver to use.

## Suggestions
If you want or need it, you may visit these documentation pages:

* [Representations](representations.md)
* [Database Structure Specifications](databasespecs.md)
* [Query Adapter](tech/queryadapter.md)

<!--:GBSUMMARY:Databases:1:Databases:-->
