# TooBasic: Modules
## What are modules?
Modules are plugins, in other words, modules are a small set of codes that can be
added to your site providing some specific functionality.
Sometimes they are a bunch of controllers, maybe the views of certain skin or even
a single file for an specific _model_.

To keep it simple, let's say each module acts in the same way that your folder
`ROOTDIR/site`.
In fact, aside of one or two files, it shares the same structure.

## An example
Let's make an example to explain how a module works.
Let's say you have a site with users and you want to be able to insert an user's
avatar in any view based on the email address you have and information retrieved
from [_Gravatar_](https://en.gravatar.com/).
Also, this is something you need for the current site you are developing but it's
something you're going to use in other sites.
This is functionality is a candidate to be developed as a module.

For our example we're going to need a few thing:

* A new template function for views, something like `{$ctrl->avatar('a@b.com')}`.
* An API reader to connect with _Gravatar_ and extract the image URL.
* A model to concentrate all the heavy logic.
* And of course, a name for our module, so let's use `gravatar`.

### Folders
First of all, let's create our module, which is as simple as creating a new folder
at `ROOTDIR/modules/gravatar`.
Yes, that is enough to have a new module; remember, _too basic_ :)

Also create these folders, we're going to need them:

* `ROOTDIR/modules/gravatar/configs`
* `ROOTDIR/modules/gravatar/models`
* `ROOTDIR/modules/gravatar/sapis`

### Gravatar API
To access _Gravatar_, we're going to create a [_Simple API
Reader_](../sapireader.md) specification file to make thing easier.
Just create a file at `ROOTDIR/modules/gravatar/sapis/gravatar.json` with this
content:
```json
{
	"name": "Gravatar API",
	"description": "Basic Gravatar API representation",
	"url": "http://en.gravatar.com",
	"headers": {
		"User-Agent": "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.86 Safari/537.36",
		"Accept": "application/json"
	},
	"services": {
		"hashInfo": {
			"method": "GET",
			"uri": "/:hash.json",
			"params": [":hash"]
		}
	},
	"type": "json"
}
```

### Model
As we said before, we need a [model](../models.md) to hold the heavy logic of extracting a
thumbnail URL from _Gravatar_'s API response.
So, let's create a model also called `Gravatar` at
`ROOTDIR/modules/gravatar/models/Gravatar.php` with this content:
```php
<?php

class GravatarModel extends \TooBasic\Model {
	public function emailToHash($email) {
		return md5(strtolower(trim($email)));
	}
	public function emailThumbnail($email) {
		$hash = $this->emailToHash($email);
		$info = $this->sapireader->gravatar->hashInfo($hash);
		$entry = isset($info->entry[0]) ? $info->entry[0] : false;

		return $entry ? $entry->thumbnailUrl : '';
	}
	protected function init() {
	}
}
```
This model provides a method that takes an email, retrieves information about it
from _Gravatar_ using our _Simple API Reader_ specifications, and returns the
related avatar thumbnail.

### Controller exports
Now we need to give a way to invoke this method from a view and inject an images
with such URL.
For that we're going to create a file at
`ROOTDIR/modules/gravatar/configs/config_http.php` with this content:
```php
<?php

function injectAvatar($email) {
	$url = \TooBasic\MagicProp::Instance()->model->gravatar->emailThumbnail($email);
	return "<img src=\"{$url}\"/>";
}
$Defaults[GC_DEFAULTS_CTRLEXPORTS_EXTENSIONS]['avatar'] = '\\injectAvatar';
```
As you may already known, this special kind of configuration file loads at the
beginning of each request and only when it's a web request (visit [Configuration
files](../configs.md).

This configuration sets a function called `injectAvatar()` that uses our model and
returns a piece of HTML code for an image tag with our avatar.
Then it defines a controller export called `avatar` the forward the call to our
function.

With all of this, we can now go to any of our views and write something like this:
```html
{$ctrl->avatar('myemial@someserver.com')}
```
And if everything goes according to plan, you may get something like this:
```html
<img src="http://1.gravatar.com/avatar/73637a0ce840cb5b4df6d97c5fdd6209"/>
```

## Manifests
By default, all modules have some kind of manifest file in which a lot of
information about it is given, like its name, its requirements, its icon, etc.
In out previous example we haven't done a thing about manifests, but if go to
`?debugmanifests` you'll see that it appears listed with some information about
its name and version, an universal code,a list of requirement and also an icon.
If you are wondering how, just think that this is the basic information that
__TooBasic__ assumes for your module.

Now, if you consider that your module is _awesome_ and you want to share it with
the rest of the work, you may want to provide a manifest file to avoid possible
errors like collision, incompatibilities or dependencies on other modules.

### UCode
The first thing to do is to carefully choose a universal code that no one is using
in an already existing __TooBasic__ module.
This code provides a way to identify you module among others and also give a
general way other modules to validate dependencies against your module.

At this moment there's no centralized database to check for knwon modules, but
[Google](https://goo.gl/Oza6U5) should be enough... I hope :/

For our example, let's pick the name `my-gravatar-example`, just as an example.

Other thing we should say is, if you don't set a _UCode_ in your manifest file,
__TooBasic__ will assume your modules folder as such.

### Basic manifest
To create a basic manifest file, following our examples, you can create JSON file
at `ROOTDIR/modules/gravatar/manifest.json` with something like this:
```json
{
	"name": "Gravatar",
	"ucode": "my-gravatar-example",
	"version": "1.0.0",
	"description": "This module provides access to Gravatar avatars",
	"icon": "my-gravatar-example-icon-512px",
	"author": {
		"name": "Myself",
		"page": "http://mysite.com/info"
	},
	"copyright": "2016",
	"license": "MIT",
	"url": "http://mysite.com/my-gravatar-example/",
	"url_doc": "http://mysite.com/my-gravatar-example/docs",
	"required_versions": {
		"php": "5.5",
		"toobasic": "2.0"
	}
}
```
Not let's explain these fields:

* `name`: A friendly name for module (default is the folders name).
* `ucode`: The universal code be mentioned in the previous section.
* `version`: Current version number of this module.
* `description`: A brief description of what your module does.
* `icon`: A `png` image used as icon for your module and stored at
`ROOTDIR/modules/<yourmodule>/images`
* `author`: This filed provides information about the author of this module.
	* `name`: Author's full name.
	* `page`: Author home page.
* `copyright`: This is is the copyright year.
* `license`: License under which this module is distributed.
* `url`: Home page for this module.
* `url_doc`: Page where this module's documentation can be found.
* `required_versions`: List of required version. If one of this fails, your site
will prompt an exception page.

Remember, all of this fields are optional.

### Required versions
The parameter `required_versions` is an associative list where keys are the name
of what is required and values are the minimum version required.
In our example we are requesting that:

* PHP version has to be at least _5.5_.
* and __TooBasic__ version has to be at least _2.0_.

If any of this is below requirements it will trigger an exception.

Also, you can specify a dependency against another __TooBasic__ module doing
something like this:
```json
{
	"required_versions": {
		"php": "5.5",
		"toobasic": "2.0",
		"mod:toobasic-logger": "0.6.15"
	}
}
```
This will mean that your module requires another module with the _UCode_
`toobasic-logger` and you need it to be at least version `0.6.15`.

## Suggestions
Here you have a few links you may want to visit:

* [Simple API Reader](../sapireader.md)
* [Models](../models.md)
* [Configuration Files](../configs.md)

<!--:GBSUMMARY:Others:5:Modules:-->
