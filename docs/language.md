# TooBasic: Languages
## Languages?
One of the functionalities __TooBasic__ provides is the ability to translate text
across controllers, templates, services, etc.
In other words, you can provide a page that says _Welcome to our site_ and
_Bienvenido a nuestro sitio_ based on the current language.

## How to configure it
The first thing you would want to do is to set the default language to use.
If you haven't done it yet, it will be `en_us` and it may be what you need,
otherwise can add something like this in your site's configuration at
__ROOTDIR/site/config.php__:
```php
$Defaults[GC_DEFAULTS_LANG] = 'es_ar';
```
In the example, you'll be setting the default language as _Spanish_, specifically
the _Spanish_ spoken in _Argentina_.

## Adding translations
Translations are based on keys, which means somewhere in your site there's a key
that points to a translated text.
Let's say we're going to use a key called `welcome_site` to translate into those
texts we've mentioned at the beginning of this page.
The first thing to do is to create a file at __ROOTDIR/site/langs/en_us.json__
with something like this inside:
```json
{
    "keys": [{
        "key": "welcome_site",
        "value": "Welcome to our site"
    }]
}
```
Also, we're going to create a file at __ROOTDIR/site/langs/es_ar.json__ for
Spanish translations:
```json
{
    "keys": [{
        "key": "welcome_site",
        "value": "Bienvenido a nuestro sitio"
    }]
}
```

This configurations provide the flexibility to welcome user in your page based on
the current language.

## Using translations
Now we know how to configure translations, but how do we use them?
Let's say you want to use this new translation key inside a controller and assign
it to some view value, for that you may write something like this:
```php
	protected function basicRun() {
		. . .

		$this->assign('welcome', $this->tr->welcome_site);

		. . .

		return $this->status();
	}
```
As you can see, the magic property `tr` allows you to use your translation key
directly.
This is also available inside models, services, emails and others.

Now if you're already inside a template (for Smarty templates), you may write
something like this:
```html
	<body>
		<header>
			<h2>{$tr->welcome_site}</h2>
		</header>

		. . .
```

## Advanced
All of this may seem easy, but the problem comes when your translation have
variable pieces in the middle of it, and those pieces are in different places
depending on your language.
For example, if you want to formally address a person by its last name, in English
and Spanish it would mean to set a translation key for _Mr._ and _Sr._, and then
paste it before the last name.
But if you also serve your site in Japanese, the translated key goes after the
last name.

In these cases is where you need to make an advanced usage of translation keys.
To handle our example let's write something like this at
__ROOTDIR/site/langs/en_us.json__
```json
{
    "keys": [{
        "key": "mr_name",
        "value": "Mr. %name%"
    }]
}
```
And somethig like this at __ROOTDIR/site/langs/ja_jp.json__
```json
{
    "keys": [{
        "key": "mr_name",
        "value": "%name%さん"
    }]
}
```
Then you can write something like this inside a controller:
```php
		. . .
		$this->assign('welcome', $this->tr->mr_name('name', $user->last_name));
		. . .

```
Or:
```php
		. . .
		$this->assign('welcome', $this->tr->mr_name(array(
			'name' => $user->last_name
		)));
		. . .

```
Or even in a view:
```html
	<body>
		<header>
			<h2>{$tr->welcome_site}, {$tr->mr_name('name', $usr_last_name)}</h2>
		</header>

		. . .
```

## Precompiled translations
As you may be expecting from __TooBasic__, not only the site can define
translation keys, also each module/plugin may define its own translation keys or
even override some.
This is something interesting and useful, but it might also become a burden for
your site's performance because if you translate at least one key, it will load
and analyze all translation files for the current language.

Fortunately, this happens for the first translation, the rest will use what is
loaded.
Nonetheless each and every request were there's a translation will trigger this
loading process.

To reduce the performance problem, __TooBasic__ provides a way to pre-analyze all
language specifications and store them as one per each language.
This is not a mandatory functionality, but if you're having problems with
performance, it may help a little.

### Compilation
The first thing to do is to precompile all languages and for that __TooBasic__
provides a _shell tool_ called __translate__ that can be used in this way:
```bash
$ php shell.php tool translate --compile
```
This tool will load and analyze each translation file for every language and
generate a single file per language and store them at __ROOTDIR/cache/langs/__.

__Note__: If you are using precompiled translations, remember to run this tool
everytime you install or remove a module and when you add, remove or change
translations in your site.

### Configuration
Once you have your language configuration files compiled, you must add something
like this to your site's configuration file:
```php
$Defaults[GC_DEFAULTS_LANGS_BUILT] = true;
```
This configuration tells __TooBasic__ to use precompiled files instead of normal
translation files.

## Suggestions
You may also visit these documentation pages:

* [Magic Properties](magicprop.md)
* [Shell Tools](shelltools.md)
