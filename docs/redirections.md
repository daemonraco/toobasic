# TooBasic: Using Redirections
## What is a redirection?
A redirection is a mechanism inside __TooBasic__ that allows controllers to check
some conditions and based on that decide if it should continue or redirect the
user to a different page.
Imagin your site manages users and there are some controllers that can be accessed
only when there's a user session active.
For example, if a user want to access its own profile, it's obvious that it must
be logged in, otherwise, the page should faild and redirect it to a login page, or
perhaps the landing page.

__TooBasic__ provides a rather simple but flexible mechanism to capture these
events and redirect them to the right place, and we're going to explain how to use
it based on the given example.

## Configuration
For our example we are going to suppose we have a controller called __my_profile__
that allows a user to review its profile information and a controller called
__login__ through which a user may sign-in.
In our site, whenever a condition of a not-logged-in user is reached, we should
send such user to a login page, and to achieve that we're going to define a
redirect condition (a.k.a _redirector_) called __login__.
Open your site's configuration file at __ROOTDIR/site/config.php__ and add
something like this:
```php
$Defaults[GC_DEFAULTS_REDIRECTIONS]['login'] = array(
	GC_AFIELD_ACTION => 'login'
);
```
This simple step will configure a new redirector that redirects to our __login__
controller.

## Checking conditions
Now that we have our redirector configured, we need to check when it must be used
and for that we're going to edit our controller __my_profile__ (it might by at
__ROOTDIR/site/controllers/my_profile.php__) and add a public method called
`checkRedirectors()`. Something like this:

```php
<?php
class MyProfileController extends \TooBasic\Controller {

	. . .

	public function checkRedirectors() {
		$redirector = false;
		if(!$this->model->session->isLoggedIn()) {
			$redirector = 'login';
		}
		return $redirector;
	}
	protected function basicRun() {

	. . .
}
```
Here we are supposing there's a model called `Session` that provides all the
functionality to determine if a user is logged in or not.
In the case there's no user logged in, this new method returns the name of a
redirector configuration to be processed.

For example, if a URL like this one is called with no user logged in:

>http://www.example.com/mysite/?action=my_profile

I will be automatically redirected to:

>http://www.example.com/mysite/?action=login&redirectedfrom=%2Fmysite%2F%3Faction%3Dmy_profile

And as you can see, there's a way to know where it comes from in any case it must
be re-redirected after logging-in.

## Complex redirectors
Beside `GC_AFIELD_ACTION`, redirector's configurations may accept some other
parameters that we're going to explain in this section.

### Parameters
Let's say your login page is capable of displaying a specific message when a user
arrives to it after a redirection saying things like "_You got here because
'reasons'._".
To do that you may search for something in the URL parameter __redirectedfrom__ or
write something like this:
```php
$Defaults[GC_DEFAULTS_REDIRECTIONS]['login'] = array(
	GC_AFIELD_ACTION => 'login',
	GC_AFIELD_PARAMS => array(
		'showmessage' => 'reasons'
	)
);
```
In this way, when a user gets redirected, it may end up with this URL:

>http://www.example.com/mysite/?action=login&showmessage=reasons&redirectedfrom=%2Fmysite%2F%3Faction%3Dmy_profile

### Layout
Layout allows a redirector to set a specific layout when changing the URL.
For example:
```php
$Defaults[GC_DEFAULTS_REDIRECTIONS]['login'] = array(
	GC_AFIELD_ACTION => 'login',
	GC_AFIELD_LAYOUT => 'errorlayout',
	GC_AFIELD_PARAMS => array(
		'showmessage' => 'reasons'
	)
);
```
With this a user may end up at:

>http://www.example.com/mysite/?action=login&layout=errorlayout&showmessage=reasons&redirectedfrom=%2Fmysite%2F%3Faction%3Dmy_profile
