# TooBasic: Database Structure Specifications
## What is this?
_Database Structure Specifications_ is mechanism used by __TooBasic__ to specify
how one or more databases used by a site should look like.
For example, if your site has users, you probably have a table where you store
your users, their authentication information, their emails, etc.
If that's the case, __TooBasic__ provides a way to specify such table, its fields
and indexes, and based on it, keep it always healthy.

Why would you care to write a rather long JSON file when you can manage your
database directly? well, it's not completely necessary that you use this mechanism
on your site, but if you do, you know that if a table index or column gets removed
by accident, __TooBasic__ will re-create it (nonetheless data will be lost in most
cases).

What can you specify? __TooBasic__ provides a way to specify four things that
we're going to explain later on:

* Cross database basic configurations
* Tables structures
* Indexes
* Initial table data

## Cross database basic configurations
As you're already guessing from the title, these are very specific parameters that
affect all your database specifications.
Anyway, don't be afraid, they are not so many and __TooBasic__ already has them
with default values.

If you open the file __ROOTDIR/config/dbspecs.json__ you'll find something like
this:
```json
{
	"configs": {
		"prefixes": {
			"index": "idx_",
			"key": "unq_",
			"primary": "pk_"
		}
	}
}
```

`configs` indicates what you are trying to specify core settings and the only
thing it supports right now are index prefixes for:

* indexes with unique values (`key`).
* indexes with duplicated values (`index`).
* primary keys (`primary`).

This file is included before any other specification which means you can write
your own in, for example, __ROOTDIR/site/db/myspec.json__ and override those
__TooBasic__ defaults.

Here you have to take something in consideration, the section `configs` is always
overridden when another specification file has such section and only the last will
survive.
Be careful with every specification section because each one has its own
overriding policy.

## Table structures
This is the main section of _database structure specifications_ and may look like
this:
```json
{
	"tables" : [{
		"connection": "seconddb",
		"name": "users",
		"prefix": "usr_",
		"fields": [{
			"name": "id",
			"type": {
				"type": "int",
				"precision": 11
			},
			"autoincrement": true
		}, {
			"name": "username",
			"type": {
				"type": "varchar",
				"precision": 32
			},
			"autoincrement": false
		}, {
			"name": "password",
			"type": {
				"type": "varchar",
				"precision": 50
			}
		}]
	}]
}
```

_What's this?!_ let's explain it.
First of all, `tables` is a list of object specifications representing each table,
but you already got that.
Each table has three mandatory fields you should never forget:

* `name`: The table name without prefixes. Prefixes will be added later depending
on the connection used.
* `prefix`: This is the general prefix to prepend on each field name.
* `fields`: The list of table fields/columns with their own specs.

You may also provide these fields:

* `connection`: The name of the database connection through which a table should
be maintained.
	* When _null_ or not given, it takes the default connection (read more
	about it in further sections).
* `comment`: Table description text.
	* Some databases may not support this and it will be ignored.
* `engine`: The engine to use with a table, for example __myisam__. But, this is
useless if your not using _MySQL_.

### Fields
Each table field/column has its own list of mandatory fields:

* `name`: Column name without prefixes. Relax and write the name as you know it
and let the system add the prefix later.
* `type`: Column type specification (read more about it below).

And you may also provide these:

* `null`: This is a boolean field and configures a column to accept null values or
not. When not present, it's assumed `false`.
* `autoincrement`: This is a boolean field and it indicates if current column
increments its value automatically.
	* This also means this is a primary key field.
	* When not given it's assumed `false`.
* `comment`: Column description text.
* `default`: The value to be taken when no value is given for certain column.

### Column type
Column types have their own specification mechanism with this fields:

* `type`<sup>mandatory</sup>: The type it self.
	* _blob_
		* `precision` may be _false_.
	* _enum_
	* _float_
	* _int_
	* _text_
	* _timestamp_
		* `precision` may be _false_.
	* _varchar_
* `precision`<sup>mandatory almost every time</sup>: Indicates how log a
field is.
* `values`<sup>mandatory when type is _enum_</sup>: List of values to be used by a
enumerative field.

### Override policy
The override policy for tables is to replace everything, except `fields`.
`fields` has a different behavior where every field is appended, unless it's
duplicated.
This allows you to specify a table for your site while a module adds some extra
fields to the same table without the need to redefine the entire table.

## Indexes
As expected, you can specify what indexes have to be there in your database and
such specification may look like this:
```json
{
	"indexes": [{
		"name": "username",
		"table": "users",
		"type": "key",
		"fields": ["username"]
	}]
}
```

Each index has four mandatory fields:

* `name`: Index name.
* `table`: Name of the table on which to create the index. This name has to be
without prefix.
* `type`: Index type:
	* _key_
	* _index_
	* _primary_
* `fields`: List of columns name to use. Each name has to be without prefix.

And some not mandatory:

* `connection`: The name of the database connection through which an index should
be maintained.
	* When _null_ or not given, it takes the default connection (read more
	about it in further sections).

Something to have in mind is that after every _database structure specification_
is loaded, if an index specification points to a non specified table, it will be
ignored.

### Override policy
When an index is specified more than once, the last one survives.

## Initial table data
Let's suppose your database represents some kind of items and those items have a
_status_ indicated with a numeric ID.
If your site is polite enough, you'll have a table of statuses in which you
associate that with a displayable name and a description.

Something you'll probably want to ensure is that your system always has some basic
statuses, for example these:

| sts_id | sts_name | sts_description              |
|:------:|:--------:|:-----------------------------|
|   1    | New      | Newly created item.          |
|   2    | Working  | The item is being processed. |
|   3    | Done     | Item's tasks are completed.  |

Assuming __sts_id__ is a primary key of such table, you may write an specification
like this one:
```json
{
	"data": [{
		"table": "statuses",
		"checkfields": ["id"],
		"entries": [{
			"id": 1,
			"name": "New",
			"description": "Newly created item."
		},{
			"id": 2,
			"name": "Working",
			"description": "The item is being processed."
		}, {
			"id": 3,
			"name": "Done",
			"description": "Item's tasks are completed."
		}]
	}]
}
```

_Now what's going on here?_ As you can see, every element inside `entries` is a
row specification and every column name is given without its prefix because that
will depend on its table.

_But what is `checkfields`?_ This specification is a maintenance specification,
which means _it must check if an entry exists and insert it when it doesn't_.
`checkfields` is the list of columns to use when checking if an entry is already
there.
In our example, we know __sts_id__ is a primary key, so we assume that searching
entries by their *id*s would be enough.

The rest is:

* `table`: for the table name. Mandatory and without prefixes.
* `connection`: To optionally specify a database connection to use.

### Policies
A few policies to have in mind:

* The _overriding policy_ for data specification is to append all new entries and
replace those that were already given.
* If some data specification points to an unknown table it will be ignored.

## Connections
We've been talking about a configuration field called `connection` for some time
now but we haven't talked much about its real purpose, so let's talk.

Let's say you have a complex site that uses more than one database and two or more
of them have a table called 'users', each one with a different structure and
equally important so you want to specify them in your _database structure
specification_.
This configuration field will allow you to separate each table specification for
the right connection.
Also when you specify an index or some initial data, it will point to the right
connection and table too.

### Default connection
_How about that default connection we named somewhere above?_ As you may expect,
the default connection is that one you named in
`$Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_DB]`.
But in some cases you may want to have a connection to access your default
database with only [DML](https://en.wikipedia.org/wiki/Data_manipulation_language)
access permissions and another connection for
[DDL](https://en.wikipedia.org/wiki/Data_definition_language) operations, if
that's the case, you may use the configuration field `connection` or simple set
its name in
`$Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_INSTALL]`.

## Callbacks
Callbacks is rather an easy topic that allows allows you to execute one or more
SQL queries before and after a _DML_ operation is run.

Let's say you're releasing version 2.0 of your site and from the database point of
view, it is the same expect for a new table called __user_history__ where you now
register your users' transactions.
The first thing you may want to do is to create this table and insert every user
registration date as the first entry in their history.
For that you've created a SQL file with the proper query and now you need a way to
execute it right after the table is created and only then.

Here is where a callback comes in handy.
Let's consider changing your specification to something like this:
```json
{
	"tables" : [{
		"name": "user_history",
		"prefix": "uhy_",
		"callbacks": {
			"after_create": "uhy_first_entries"
		},
		"fields": [{
	. . . 
```

_Now, what the heck is that thing there?_
This specification adds a callback to be executed right after the table is
created in the database.
__uhy_first_entries__ would be the name for a file store along side with other
JSON database specs (for example at __ROOTDIR/site/db/uhy_first_entries.sql__).

You may also do something like this:
```json
{
	"tables" : [{
		"name": "user_history",
		"prefix": "uhy_",
		"callbacks": {
			"after_create": [
				"uhy_first_entries",
				"uhy_second_entries"
			]
		},
		"fields": [{
	. . . 
```

### What else can I do?
Well you can also use this field called _callbacks_ inside a table column
specification.
And as you may be supposing, `after_create` is not the only spec you can give.
This is the complete list:

* `before_create`: Before creating a table or table column.
* `after_create`: After creating a table or table column.
* `before_update`: Before altering the structure of a table or table column.
* `after_update`: After altering the structure of a table or table column.

### Indexes
You can use callback for index specs too, but in this case you'll have less
possible callback types:

* `before_create`: Before creating an index.
* `after_create`: After creating an index.

### Why no drop callbacks?
That's an interesting question and the answer comes from the way these specs work.
When you want to remove a table, column or index, you just remove its
specification and it will get remove by the system, this means there's no place to
write _drop callback_ specs.

## Table structures version 2
__TooBasic__ version _1.1.0_ introduced a simpler way to specify tables and its
indexes which is called version 2.
Basically it is the same than version 1 (the default), but the behavior of
`fields` is much simpler and it also adds three new fields called `keys`,
`indexes` and `primary`.

Following our first examples we may write a specification like the next one based
on version 2 syntax:
```json
{
    "tables": [{
            "version": 2,
            "connection": "seconddb",
            "name": "users",
            "prefix": "usr_",
            "fields": {
                "id": {
                    "type": "int",
                    "autoincrement": true
                },
                "username": "varchar:32",
                "password": "varchar:50"
            },
            "keys": {
                "username": ["username"]
            }
        }]
}
```
The main difference is that `fields` is no longer a list of specification, it has
become an object where each of its fields are considered to be table fields and
their values are their specifications.

Here we have to be careful of adding the field `version` with the right value,
otherwise it will be considered as version 1 and it may cause bit of a mess.

### String specification
When a field's values is just a string as in `password` and `username` from our
example, it is considered to be _type_ specification and the rest is assumed with
default values.
For example `username` is specified as `varchar:32` this will be similar to say
this in version 1:
```json
{
    "name": "username",
    "type": {
        "type": "varchar",
        "precision": 32
    },
    "autoincrement": false,
    "null": false
}
```
As you can see, the value is used as `type` and `precision`.
This behavior is also applied to _int_ and _float_ types and ignored for the rest
of available types.
The only type that has a rather different behavior is _enum_ in which case one can
specify `enum:Y:N` and version 2 will consider it as a type followed by a list
possible values which may be something like this in version 1:
```json
{
    "name": "username",
    "type": {
        "type": "enum",
        "values": ["Y", "N"]
    },
    "autoincrement": false,
    "null": false
}
```

When no precision is provided, these are the assumed values:

* float: 11
* int: 11
* varchar: 256

### Extended specification
In our example, if look at `id` you'll see that it doesn't use a string
specification and it uses an extended one instead.
This kind of specifications may have these fields:

* `type`: the same value of a _string specification_.
* `autoincrement`: A boolean value indicating auto incremental behavior.
* `null`: A boolean value indicating if it allows NULL values.
* `default`: Default value for such field.

### Indexes
Version 2 provides a quick way to specify indexes reusing a lot of values already
specified for the table.
The three possible lists of index are `indexes` for simple indexes, `keys` for
indexes with unique values and `primary` from the table's primary key (this can
have only one entry).

In our example we're using a list of indexes called `keys` and it will trigger
logics in the same way this next specification would do in version 1:
```json
{
    "indexes": [{
            "name": "usr_username",
            "table": "users",
            "type": "key",
            "fields": ["username"]
        }]
}
```

## Unknowns
This database maintenance mechanism is rather violent and it might destroy unknown
tables, columns and indexes (data is always kept as it is) and only lets live
those inside the _database structure specification_.
To avoid such trouble, you may set
`$Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_KEEPUNKNOWNS]` to
_true_ and no drop will be run.

## Performance
Yes _performance_ is an issue here, all these checks will eat your system alive
and make everything slower, but we encourage you to use it because it will keep
your databases healthy and basically because you can turn it off and temporarily
back on again when you install a new module or when you have the feeling that
something went wrong inside your databases.

_How?_
When your site is flagged as installed, this checks are avoided.

## Suggestions
If you want or need it, you may visit this documentation pages:

* [Databases](databases.md)
