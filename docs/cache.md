# TooBasic: Cache
## We All Know What It Is
True, we all know what a
[cache](http://en.wikipedia.org/wiki/Cache_%28computing%29) system is, the real
question is *what does __TooBasic__ have to say about it?*

## What is it cached?
Internally, __TooBasic__ separates the stuff it caches in two kinds:

* __Computing Cache__: This can also be called _controller cache_ because it
stores all the assignments made by a controller.
* __View Cache__: You can see this as a HTML cache where you store the final
result of rendering a controller call.

Why is it separated? well, if recall a cache controller but requesting a different
format, you wouldn't want to run all your logics and queries and etc. when you
only need a different format.

## Adapters
From factory, __TooBasic__ provides three types of cache adaptations you can
choose on for your site and we are going to describe them.

### File Cache Adapter
The most basic (and default) cache adapter provided by __TooBasic__ is a class
called __\TooBasic\CacheAdapterFile__ and it stores data inside a files. When
active, you'll find files coming and going inside __ROOTDIR/cache/filecache__.

### Database Cache Adapter
A not very polite way of caching data is to store it inside a database, but in
some cases it could be useful (I hope _Mr.Potato_ won't kill for suggesting this
mechanism).
For those cases, __TooBasic__ provides an abstract adapter class called
__\TooBasic\CacheAdapterDB__ and a specification for __MySQL__ called
__\TooBasic\CacheAdapterDBMySQL__.
This adapter makes use of a table called __cache__ (if there's a table prefix it
will use it) inside the database configured in
`$Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_CACHE]` (or
`$Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_DB]` if none) and
its structure will be created with a query like the next one (if __MySQL__):
```sql
create table if not exists cache (
        cch_key  varchar(256) collate utf8_bin not null,
        cch_data blob not null,
        cch_date timestamp not null default current_timestamp,
        primary key(cch_key)
) engine=myisam default charset=utf8 collate=utf8_bin
```
By default, __TooBasic__ will attempt to create this table every time it's
invoked, if you want to avoid this behavior you can set the global
`$Defaults[GC_DEFAULTS_INSTALLED]` to `true`.

### Memcached Adapter
A better approach is to use something like
[__Memcached__](http://php.net/manual/en/book.memcached.php) where you can store
data in memory inside a cache service prepared for that task.
For this, __TooBasic__ provides a cache adapter class called
__\TooBasic\CacheAdapterMemcached__ and it requires settings like these:
```php
<?php
$Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_SERVER] = 'localhost';
$Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_PORT] = 11211;
$Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_PREFIX] = '';
```
If you are wondering about
`$Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_PREFIX]`, well when you
have more than one __TooBasic__ based site using the same __Memcached__ service,
you may find some collision problems and this global allows you to tag each cache
key with a prefix depending on your site.

### Memcache Adapter
If you are using _memcache_ libraries (not _memcached_), try this:
```php
<?php
$Defaults[GC_DEFAULTS_CACHE_ADAPTER] = '\\TooBasic\\CacheAdapterMemcache';
$Defaults[GC_DEFAULTS_MEMCACHE][GC_DEFAULTS_MEMCACHE_SERVER] = 'localhost';
$Defaults[GC_DEFAULTS_MEMCACHE][GC_DEFAULTS_MEMCACHE_PORT] = 11211;
$Defaults[GC_DEFAULTS_MEMCACHE][GC_DEFAULTS_MEMCACHE_PREFIX] = '';
```

### Redis Adapter
An alternative to __Memcached__ is [__Redis__
](https://en.wikipedia.org/wiki/Redis) and you can make use of it by using a cache
adapter called __\TooBasic\CacheAdapterRedis__ and settings these values:
```php
<?php
$Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_SCHEME] = 'tcp';
$Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_HOST] = 'localhost';
$Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_PORT] = 6379;
$Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_PREFIX] = '';
```

## Setting Adapter
We've been talking about cache adapter classes but we haven't said how to use
them, well, that's the easy part, you just need to set something like the next
piece of code and that's all:
```php
<?php
$Defaults[GC_DEFAULTS_CACHE_ADAPTER] = '\\TooBasic\\CacheAdapterMemcached';
```

## Cached Controller
Now that you know all this about __TooBasic__'s cache system, you need to know how
to configure it for your controller. Once again, let's use a simple example,
suppose we have a controller that shows a user information... something like this:
```php
<?php
class UserinfoController extends \TooBasic\Controller {
    protected function basicRun() {
        $user = $this->representations->users->item($this->params->get->userid);
        $this->assign('info', $user->toArray());
        $this->assign('bgcolor', isset($this->params->get->bgcolor) ? $this->params->get->bgcolor : 'red');
        return true;
    }
    protected function init() {
        parent::init();
        $this->_requiredParams['GET'][] = 'userid';
    }
}
```
Now, let's say we want to cache this controller's called based on the _user ID_
and a parameter called _bgcolor_. For that we need to do two things:

* First, we need to activate the use of cache:
```php
<?php
class UserinfoController extends \TooBasic\Controller {
    protected $_cache = true;
    . . .
}
```
* And second we need to specify which parameters to take in consideration when
generating a cache key:
```php
<?php
class UserinfoController extends \TooBasic\Controller {
    . . .
    protected function init() {
        . . . 
        $this->_cacheParams['GET'][] = 'userid';
        $this->_cacheParams['GET'][] = 'bgcolor';
        . . . 
    }
}
```

All together will look like this:
```php
<?php
class UserinfoController extends \TooBasic\Controller {
    protected $_cache = true;
    protected function basicRun() {
        $user = $this->representations->users->item($this->params->get->userid);
        $this->assign('info', $user->toArray());
        $this->assign('bgcolor', isset($this->params->get->bgcolor) ? $this->params->get->bgcolor : 'red');
        return true;
    }
    protected function init() {
        parent::init();
        $this->_cacheParams['GET'][] = 'userid';
        $this->_cacheParams['GET'][] = 'bgcolor';
        $this->_requiredParams['GET'][] = 'userid';
    }
}
```
And that's it, from now on, our controller saves cache entries and calls like
these will have different entries:

* http://www.example.com/?action=userinfo&userid=10&bgcolor=green
* http://www.example.com/?action=userinfo&userid=10&bgcolor=blue
* http://www.example.com/?action=userinfo&userid=12&bgcolor=green
* http://www.example.com/?action=userinfo&userid=10

What about the last one? well, if you don't pass a parameter called _bgcolor_ it
will consider it as empty and generate a different key.

## What if you don't want it?
Yes, what if you don't want any cache system and the use of `&debugresetcache` in
the url annoys you?
Well, there's an adapter called __CacheAdapterNoCache__ that acts as a dummy
providing you with the solution.
It will interact as any other cache adapter but it won't do a thing and you'll
be working without cache.

## Duration
Before version 0.3.0, every cache entry had a duration of 3600 seconds (one hour)
and it couldn't be changed, but now it is possible adding something like this in
your sites configuration file:
```php
<?php
$Defaults[GC_DEFAULTS_CACHE_EXPIRATION] = 86400; // 1 day
```
You can also change your controllers and use something like this:
```php
<?php
class MyactionController extends \TooBasic\Controller {
	protected $_cached = \TooBasic\CacheAdapter::ExpirationSizeLarge;
	public function basicRun() {
		. . .
```
This constant will tell __TooBasic__ to use an expiration time as long as it's
configured.

Available constants are:

* `\TooBasic\CacheAdapter::ExpirationSizeDouble`: Double of
`$Defaults[GC_DEFAULTS_CACHE_EXPIRATION]`.
* `\TooBasic\CacheAdapter::ExpirationSizeLarge`: Same size as
`$Defaults[GC_DEFAULTS_CACHE_EXPIRATION]`.
* `\TooBasic\CacheAdapter::ExpirationSizeMedium`: A half of
`$Defaults[GC_DEFAULTS_CACHE_EXPIRATION]`.
* `\TooBasic\CacheAdapter::ExpirationSizeSmall`: A quarter of
`$Defaults[GC_DEFAULTS_CACHE_EXPIRATION]`.

Any other value will be considered as
`\TooBasic\CacheAdapter::ExpirationSizeLarge`, including the boolean `true` used
in versions before 0.3.0.
