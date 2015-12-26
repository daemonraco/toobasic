# TooBasic: Installation
## Requirements
To be able to install __TooBasic__ you at least need:

* [PHP](http://php.net/) 5.4 or above
* [Git](https://git-scm.com/)
	* This is optional if you use a TooBasic tarball.

We also recommend to install some of this tools depending on what kind of site you
are going to build.

* _Memcache_ or [_Memcached_](http://memcached.org/) server
	* and their PHP libraries.
* [_Redis_](http://redis.io/)
* [_MariaDB_](https://mariadb.org/) or [_MySQL_](https://www.mysql.com/)
	* and MySQL libraries for PHP.
	* perhaps [_phpMyAdmin_](https://www.phpmyadmin.net/) too.
* [_SQLite 3_](https://www.sqlite.org/)
	* and its PHP libraries.
* [_PostgreSQL_](http://www.postgresql.org/)
	* and its PHP libraries.

## Linux preconditions
If you are using a standard [Linux](https://www.linux.com/) installation you'll
have to make sure of installing our required packages using `apt`, `rpm`, `yum` or
whatever _packages manager_ you use.
After that you'll probably end up with a default root directory at
__/var/www/html__ with a simple _index file_.

### Checks
Once you've installed everything make sure that `php` is included in your _PATH_
settings.
Try running something like `which php` in a console and if you are ok it will
return a full path otherwise you'll have to use its full path all the time or
change your _PATH_ settings.

Do the same check with _Git_ running `which git`.

## Microsoft windows
### Installing PHP and others
If you are using a [Windows](http://www.microsoft.com/en-us/windows) installation,
we recommend you to use [XAMPP](https://www.apachefriends.org/index.html) to save
you from more than one headache.
Such package holds many of our requirements and it's really easy to install.

Supposing that you installed it at __C:\xampp__ you'll have a directory at
__C:\xampp\htdocs__ that will be your root directory.

### Installing Git
To install _Git_ you may visit [this link](https://git-scm.com/download/win) and
follow the instructions.

### Checks
Make sure that `php.exe` is included in your _PATH_ settings.
Try running something like `where php.exe` in a command window (`cmd`) and if you
are ok it will return a full path otherwise you'll have to use its full path all
the time or change your _PATH_ settings.
Following our suppositions, `php` should be at __C:\xampp\php\php.exe__.

Do the same check with _Git_ running `where git`

## Installing __TooBasic__
Here we suppose that:

* you've read the above topics
* `php` and `git` are available in your _PATH_ settings, otherwise you know how to
invoke them.
* You known where is you default root directory and we are going to refer to it as
__ROOTDIR__ from now on.

### Step 1: location
Open a terminal and go to your __ROOTDIR__ directory and inside it create a new
folder, for example, one called __mysite__; then go into this new folder.
Generally speaking, do something like this (Linux example):
```text
$ cd /var/www/html
$ mkdir mysite
$ cd mysite
```
If you prefer you can remove all contents inside your __ROOTDIR__ directory and
perform the installation there, but for this guide we are going to use a
sub-folder.

### Step 2: basic installation
After you created your site directory and you are in it, you may clone
__TooBasic__ by running something like this:
```text
$ git clone https://github.com/daemonraco/toobasic.git .
```
This may take some time depending on your connection.

### Step 3: git submodules
The last instalation step will be to download some required submodules, in this
case [_Smarty_](http://www.smarty.net/) and
[_Predis_](https://github.com/nrk/predis).
But don't worry, just run these two commands and wait until they finish (the
second one takes some time):
```text
$ git submodule init
$ git submodule update
```

## Final check
If there were no errors, you should have a working page that you can access at:

>http://localhost/mysite

## Post installation
After you installed __TooBasic__ you may find some known issues that you can solve
as we explain below.

### Linux
After installing under a standard Linux environment you may find some permission
issues.
A simple but not so polite way to solve this would be:
```text
$ sudo chmod -v 0777 /var/www/html/mysite/cache
$ cd /var/www/html/mysite/cache
$ sudo find . -type d | xargs sudo chmod -v 0777
$ sudo find . -type f | xargs sudo rm -v
$ git checkout .
```
This will give access to everyone to some dynamic directories and restart any file
that can cause an issue.
The final line is to avoid _Git_ conflicts.

## Versions
By default, cloning from [_Github_](https://github.com/) installs the master
branch which is our newest but stable branch, let say our _closed for testing_
branch.
If you prefer, you can use a more stable version switching to our branch `v1.0.0`
or if you like the danger, switch to our development branch `dev`.

## Suggestions
Here you have a few links you may want to visit:

* [Troubleshooting](troubleshooting.md)
