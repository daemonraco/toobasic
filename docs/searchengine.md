# TooBasic: Search Engine
## What is __TooBasic__'s Search Engine?
First of all, don't spec [Google Search](https://goo.gl/DCbLr1) power embedded in
__TooBasic__'s code.
*__TooBasic__'s Search Engine* is just a basic mechanism that gives you a way to
index elements in your site and then search for them.

## Database
This of course requires a default database connection configured and by default it
will create two new tables:

* `tb_search_items`
* `tb_search_terms`

Don't worry about space usage because, unless you use something from this engine,
these tables will remain empty.

## Searchable items
When something has to be indexed you have to keep in mind three concepts:

* A search item code.
* An item class to be indexed.
* A factory (singleton) capable of list indexable items.

### Search item code
Since all indexed items are handle as the same thing, this engine requires a code
to differentiate them when they have to be converted back to their original form.
Such code has to be a string no longer than ten characters.

In further sections will show where to use these codes.

### Searchable items
Each object that can be indexed has to implement the interface
`TooBasic\Search\SearchableItem` because it enforces all require methods.
Also, here you have to make sure that you implement the method `type()` returning
the _search item code_ you've chose before.

### Searchable item factories
Something that this engine requires is a way to access all items of some type.
To achieve this it's required to have a factory (a singleton) for each type that
implements the interface `TooBasic\Search\SearchableFactory`.

## Examples
All that we said sound like a bunch of confusing theory, so let's give a quick
example.

Let's go back to an example we used with [representations](representations.md) and
suppose this table:

| ppl_id | ppl_fullname | ppl_age | ppl_username | ppl_children | ppl_indexed |
|:------:|--------------|:-------:|--------------|-------------:|:-----------:|
|   1    | John Doe     |   35    | deadpool     |            0 |      N      |
|   2    | Juan Perez   |   46    | hulk         |            2 |      N      |
|   3    | Jane Doe     |   27    | ironman      |            1 |      N      |

What we are going to do here is to index this in our search engine and then look
for something.

Notice the addition of field `ppl_indexed`, this is a requirement of __TooBasic__
Search Engine.

### Representation
The first thing to create is a representation class, but this time we are going to
use a different parent class:
```php
<?php
class PersonRepresentation extends \TooBasic\Search\SearchableItemRepresentation {
	protected $_CP_IDColumn = 'id';
	protected $_CP_ColumnsPerfix = 'ppl_';
	protected $_CP_Table = 'people';
	
	public function terms() {
		return "{$this->fullname} {$this->username}";
	}
	public function type() {
		return 'PERSON';
	}
}
```
The class `TooBasic\Search\SearchableItemRepresentation` is a special abstract
representation that incorporates some basic behavior required by
`TooBasic\Search\SearchableItem`.

Here we assumed that our _search item code_ will be `PERSON` and defined the
method `type()` with it.
Also, we redefined the method `terms()` in order to use values from fields
`ppl_fullname` and `ppl_username` as search terms.
Don't worry about merging those two fields with just a space because the search
engine will sanitize them later.

If you don't define `terms()` it won't be a problem, it will use your name field.

_Where to store it?_
Try at __ROOTDIR/site/models/representations/PersonRepresentation.php__.

### Factory
We've talk about the need of a factory, therefore we're going to create a items
factory in this way:
```php
<?php
class PersonRepresentation extends \TooBasic\Search\SearchableItemsFactory {
	protected $_CP_IDColumn = 'id';
	protected $_CP_ColumnsPerfix = 'ppl_';
	protected $_CP_Table = 'people';
	protected $_CP_NameColumn = 'username';
	protected $_CP_ReadOnlyColumns = array('age');
}
```
`TooBasic\Search\SearchableItemsFactory` is also a special class that takes care
of many methods required by `TooBasic\Search\SearchableFactory`.

_Where to store it?_
Try at __ROOTDIR/site/models/representations/PeopleFactory.php__.

### Configuration?
At this point it seems easy but there's a configuration matter we need to attend.
__TooBasic__'s search engine requires to know which factories are to be indexed
and which search item code they use.
For this we are going to add something like this to, for example, our site's
configuration file at __ROOTDIR/site/config.php__:
```php
\TooBasic\MagicProp::Instance()->representation->people;
$Search[GC_SEARCH_ENGINE_FACTORIES]['PERSON'] = 'PeopleFactory';
```
Let's explain this weird configuration:

* The most important part is the fact that we associated a factory class name with
a search item code, in this case, `PeopleFactory` with `PERSON`.
* Now the weirdest part, the first line, is a way to force the singleton
`PeopleFactory` to be loaded. You can also use a `require_once` sentence, but this
one is more generic.

## Where is my search?
Yes, we've explain a lot of things and created some "useful" classes, but where is
the index?, and more important, how do I search?

### Indexation
The first thing to do is to update our index and for that we are going to run this
command:
```text
$ php shell.php cron search --update
```
This command will load every item pending for indexation and process them.
This may take some time at first, but from the second run and on it will only
depend on how many not indexed items you have.
We recommend to add this command to your scheduled cron operation so you won't
have to worry later.

### Search
Now that your index is updated, you can run a simple search by running a command
like this one:
```text
$ php shell.php cron search --search 'JOHN dOe'
```
In our example it will return two items because it actually looks for 'john' and
then for 'doe'. Also, the list will be sorted showing at the top those items that
hit more terms.

### In code
Of course, all this trouble to end up with a command line is not worth it.
Well, you can make use of this search functionality anywhere in your code just by
accessing the engine's manager in this way:
```php
protected function basicRun() {
	debugit(\TooBasic\Managers\SearchManager::Instance()->search('JOHN dOe'), true);
	return $this->status();
}
```
### Service
You can also access a basic service that allows provides you with the same basic
search functionality:

>http://www.example.com/mysite/?service=search&terms=JOHN%20dOe

## Suggestions
If you want or need, you may visit these documentation pages:

* [Representations](representations.md)
* [Database Connections](databases.md)

<!--:GBSUMMARY:Tools:3:Search Engine:-->
