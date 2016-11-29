# TooBasic: Troubleshooting
## What is this page?
This page won't be the _hitchhiker's guide to the galaxy_ for __TooBasic__, but
_don't panic_, this won't be [vogon poetry](http://i.imgur.com/MDIPz9j.jpg).
Here we are going to talk about some usual problem found when installing
__TooBasic__ in your server.

## General
### Have you installed _Smarty_?
If you used a `git clone` instead of downloading the recommended release package,
you'll end up with an almost empty folder at `ROOTDIR/libraries`.
This means your site will start throwing errors everywhere because it's looking
for Smarty.

_What to do?_
Well there's the easy way and the hard way.
The easy way is to run (we're supposing you used `git clone`):
```plain
$ git submodule init
$ git submodule update
```
This two commands will set your clone to have in mind some modules and then check
them out, in our case, Smarty.

The hard way is:

* Go to [Smarty's download page](http://www.smarty.net/download) and download the
latest version,
* unpack it,
* copy the folder `libs` (from inside the pack) into `ROOTDIR/libraries` and
* rename it to `ROOTDIR/libraries/smarty`.

### Permissions
There are a few folders that need writing permission and __TooBasic__ will try to
tell you this when possible, but just to be sure check these folders:

* `ROOTDIR/cache`
* `ROOTDIR/cache/filecache`
* `ROOTDIR/cache/langs`
* `ROOTDIR/cache/shellflags`

Of course, writing permission means that the system user your HTTP server uses has
to be able to write inside this folders.
If you use a standard _unix-like_ system with apache, it usually is `www-data`.

### There goes nothing
Your site shows no errors and your browser is an empty white page.
Well this doesn't mean there are no errors, perhaps they're been shown in a file
you haven't checked yet.
If you are under a standard _unix-like_ installation using apache, check these
files:

>/var/log/apache2/error.log

>/var/log/apache2/access.log

They may give a hint of your problem.

If you are not on a standard _unix-like_ system,
[google](https://www.google.com/search?q=apache+logs+location) where you can find
this files.

## Database
### SQLite & autoincrements
_"I specified a database field with autoincrement and nothing happends!"_
Well, if you're using a database engine that is not MySQL or PostgreSQL, this
feature has no meaning. You probably would need to find another way to emulate
this.

### SQLite & deprecated columns
If you removed or changed the specification of a table column and your SQLite
database doesn't applied the change, you should know SQLite does not support this
kind of changes in a data base.
You can add columns, but you can't change them or modify them.

If you want some more information, visit [SQL As Understood By
SQLite](http://sqlite.org/lang_altertable.html).

## Environment globals
If you are developing a shell tool and for some reason you can't use
`\TooBasic\Params::Instance()->env->myprop`, even though you exported it in your
command line, you probably have an security issue in your `php.ini` file.

Open your `php.ini` file and look for something like:
```
variables_order = "GPCSE"
```
The `E` in there means you have the super global `$_ENV` active, if not, add it,
reload your configuration in your server and it should work.
The super global `$_ENV` is the one that enables the use of
`\TooBasic\Params::Instance()->env`.

### Where is my _php.ini_ file?
If you don't know where your `php.ini` file is located, run something like this
and it will give you a hint:
```
$ php --ini
```

Remember, the PHP configuration you use in a console may not be the same used by
your PHP web server.

## Smarty version
Sometimes, when the submodule _Smarty_ is updated, you may experience some file
permissions errors and to solve this, remove the folder located at
`ROOTDIR/cache/smarty` along with its contents, don't worry, __TooBasic__ will
recreate it automatically when it's required.

## Strange configuration issue?
If you added or removed a module, or maybe added or removed a configuration file,
you get an strange error saying the some configuration file cannot be loaded or
something like it.
If that's the case, try removing the file
`ROOTDIR/cache/system/config-priorities.json` this is an automatically generated
file that is known to case this kind of troubles.

Don't worry about removing it, it'll come back again on it's own, hopefully fixed
:)

## Suggestions
If you want, you may visit these documentation pages:

* [Author's Note](authorsnote.md)

<!--:GBSUMMARY:Appendix:1:Troubleshooting:-->
