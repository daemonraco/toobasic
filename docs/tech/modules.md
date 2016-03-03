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

### _Gravatar_ API
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







## Suggestions
Here you have a few links you may want to visit:

* [Simple API Reader](../sapireader.md)

<!--:GBSUMMARY:Others:5:Modules:-->
