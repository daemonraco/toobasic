## Libraries
When download __TooBasic__ you get a list of default libraries that you can use.
Some of them are required by internal logics, and some are suggested.

__Required libraries__:

* [JSON Validator](https://github.com/daemonraco/json-validator)
* [Smarty](http://www.smarty.net/download)

__ Not so required libraries__:

* [jQuery](https://jquery.com/download/)
* [Bootstrap](http://getbootstrap.com/getting-started/#download)

## Git submodules
Some required libraries are downloaded using [_git
sumodules_](https://git-scm.com/docs/git-submodule) which requires that you run a
few commands on your site root directory.

```sh
git submodule init
git submodule update
```

## Composer
If you use libraries downloaded using `Composer`, you may run these commands
inside this folder and __TooBasic__ will recognize the `autolaod.php` file
automatically from `ROOTDIR/libraries/vendor/autolaod.php`.
