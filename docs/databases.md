# TooBasic: Databases
## What is it, really?
In a complicated way we may say it is a "wrapper's wrapper" and it won't make any sence, but it's the truth because __TooBasic__ database administration is a wrapper for [PDO](http://php.net/manual/en/book.pdo.php), which itself is a generic wrapper for databases.
So if you are familiar using PDO, you may find this easy to understand.

## Configuration
By default, __TooBasic__ has no database configuration and it requires some specifica setting we will try to explain.

The first thing you need to know is the existence of a global array called `$Connections` in which every connection setting must be added.
As an example we're going to suppouse that we have a database called __census__ stored in a [MySQL](http://dev.mysql.com/doc/) server in out current server.
Also, we're going to suppouse that our database credentials area __censususr__ for username and the same for password, and that every table has a prefix __ss___.
Based on all of this, you might end with a configuration simillar to this:
```php
<?php
$Connections[GC_CONNECTIONS_DB]["census"] = array(
	GC_CONNECTIONS_DB_ENGINE => "mysql",
	GC_CONNECTIONS_DB_SERVER => "localhost",
	GC_CONNECTIONS_DB_PORT => false,
	GC_CONNECTIONS_DB_NAME => "census",
	GC_CONNECTIONS_DB_USERNAME => "censususr",
	GC_CONNECTIONS_DB_PASSWORD => "censususr",
	GC_CONNECTIONS_DB_PREFIX => "ss_"
);
```
In this way, you create a configuration to connect to a database just using its name as reference.

### Where to Place It?
Probably, the best place to write this configuration is in your site's configuration file at __ROOTDIR/site/config.php__.

## Let's Make a Query
Now that we have a database configuration, we'll try to obtain some information from it and for that we are going to create a quick model:
```php
<?php
class SomeModel extends \TooBasic\Model {
	public function printSomeData(){
		$db = \TooBasic\DBManager::Instance()->census;
		$prefix = $db->prefix();
		$result = $db->query("select * from {$prefix}sometable");
		foreach($result->fetchAll() as $row) {
			debugit($row);
		}
	}
	protected function init(){}
}
```
This will executes a __select__ in your database __census__ and print out every row.
Not very usefull and rubust, but gives an idea of how it works.

Also, you can go further and write something like this:
<?php
class SomeModel extends \TooBasic\Model {
	public function printSomeData($type) {
		$db = \TooBasic\DBManager::Instance()->census;
		$prefix = $db->prefix();
		$stmt = $db->query("select * from {$prefix}sometable where smt_type = :type");
		$stmt->execute(array(":type"=>$type));
		foreach($stmt->fetchAll() as $row) {
			debugit($row);
		}
	}
	protected function init(){}
}
```
This makes use of prepared queries  improve your database performance.
