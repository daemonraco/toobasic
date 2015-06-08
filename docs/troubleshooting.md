# TooBasic: Troubleshooting
## What is this page?
This page won't be the _hitchhiker's guide to the galaxy_ for __TooBasic__, but
_don't panic_.
Here we are going to talk about some usual problem found when installing
__TooBasic__ in your server.

## Have you installed _Smarty_?
If you used a `git clone` instead of downloading the recommended release package,
you'll end up with an almost empty folder at __ROOTDIR/libraries__.
This means your site will start throwing error everywhere because it's looking for
Smarty.

What to do? Well there's the easy way and the hard way.
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
* copy the folder `libs` (from inside the pack) into __ROOTDIR/libraries__ and
* rename it to __ROOTDIR/libraries/smarty__.

## Permissions
There are a few folders that need writing permission and __TooBasic__ will try to
tell you this when possible, but just to be sure check this folders:

* `ROOTDIR/cache`
* `ROOTDIR/cache/filecache`
* `ROOTDIR/cache/langs`
* `ROOTDIR/cache/shellflags`

Of course, writing permission means that the system user your HTTP server uses has
to be able to write inside this folders.
If you use a standard *nix system with apache, it usually is __www-data__.

## There goes nothing
Your site shows no errors and your browser is an empty white page.
Well this doesn't mean there are no errors, perhaps they're been shown in a file
you haven't checked yet.
If you are under a standard *nix installation using apache, check these files:
>/var/log/apache2/error.log

>/var/log/apache2/access.log

They may give a hint of your problem.

If you are not on a standard *nix system, google where you can find this files.
