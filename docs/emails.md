# TooBasic: Emails
## Huh?!
No, we are not redifining the concept of an email, we're just making it easier
inside __TooBasic__.

Let's say your site keeps getting better and now you would like to send emails to
your users due to account updates and issues, or maybe just advertisment.
For this you created a beautiful page with your site's logo at the top and a some
important licences related texts in the footer.
And in the middle you want to insert the real thing you want to say to your
recipient.

_Wait, this sounds like a controller and a layout?_
Well yes, it is.
For __TooBasic__ an email is manage in a similar way to any controller and it can
also use layouts.

Let's follow this example and let's create such mail.

## Layout
The first thing we may want to create is the layout, but it's not mandatory, you
can have email controllers without layouts in the same way than any controller.
Let's write the next code and save it at __ROOTDIR/site/emails/basic_layout.php__:
```php
<?php
class BasicLayoutEmail extends \TooBasic\EmailLayout {
	protected function basicRun() {
		return true;
	}
	protected function simulation() {
		return true;
	}
}
```
And a view at __ROOTDIR/site/templates/email/basic_layout.html__:
```html
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
	</head>
	<body>
		<header>
			<a href="{$ctrl->link('?action=home')}">
				<img alt="My Site" src="{$ctrl->img('site-logo')}"/>
			</a>
		</header>
		<div>
%TOO_BASIC_EMAIL_CONTENT%
		</div>
		<footer>
			<p>
				Read your license agreement before doing something
				wrong.
			</p>
		</footer>
	</body>
</html>
```
These two elements will provide with a base for your emails.

### _%TOO_BASIC_EMAIL_CONTENT%_
Yet again a weird keyword and in this case is the place where a controller
rendering result will be inserted, similar to basic controllers and their layouts.

### Default layout
Yes there is a way to set a default layout for your emails and that's achieved by
setting `$Defaults[GC_DEFAULTS_EMAIL_LAYOUT]`.

## Hello email
We we're going to create the actual mail and for that we're going to write this
code at __ROOTDIR/site/emails/hello.php__:
```php
<?php
class HelloEmail extends \TooBasic\Email {
	protected $_layout = 'basic_layout';
	protected function basicRun() {
		return true;
	}
	protected function simulation() {
		return true;
	}
}
```
And this next one at __ROOTDIR/site/templates/email/hello.html__:
```html
<h4>Hello friend, we miss you at our site</h4>
```

With these two you have a mail to say hello to your users and tell them that you
miss them.

## Is it right?
Now we have a mail and its layout, but how do I know if it's well created or if
I'm missing something?
For this purpose you'll need to use a debug parameter on your site, something like
this:
>http://www.example.com/mysite/?debugemail=hello

This won't show a normal page on your browser, instead it will render and display
your email along with its layout.
In this way you can see how your email may look when it arrives.

This is what we call _email simulation_ and here is where the method
`simulation()` makes more sence.
As you may already guest, `basicRun()` is where you place your logic and
assignments to be used in the view, but when you are simulating, there's no real
information to base your logic and instead of looking at how beautiful your email
is you'll end up looking at how it explodes.
`simulation()` allows you to make all the required assignments with dummy values
and let __TooBasic__ use them to render your mail.

For example, you may have this:
```php
<?php
class HelloEmail extends \TooBasic\Email {
	protected $_layout = 'basic_layout';
	protected function basicRun() {
		$manager = $this->model->users;
		$user = $manager->currentUser();
		$this->assign('name', $user->name);
		return true;
	}
	protected function simulation() {
		$this->assign('name', 'Some User');
		return true;
	}
}
```
And:
```html
<h4>Hello {$name}, we miss you at our site</h4>
```

## How do I send it?
Now is time that we talk about _email payloads_.
Somewhere in all this document pages we said that we don't like controllers that
talk between each other and we still don't like it, but in this case, when a model
or a controller triggers a email send, it has to tell something to an _emails
manager_ and finally to the email itself.
Now, because we don't like it, we've created a mechanism called _email payload_
which transport such information between somethin class and the _emails manager_.
Let's take a look to an example with a possible _model_ in which we send our
_hello_ mail:
```php
<?php
class MailsModel extends \TooBasic\Model {
	public function helloCurrentUser() {
		// Loading current user information
		$manager = $this->model->users;
		$user = $manager->currentUser();
		// Creating and setting email payload.
		$payload = new \TooBasic\EmailPayload();
		$payload->setName('hello');
		$payload->setSubject('Hello Friend');
		$payload->setEmails($user->email);
		$payload->name = $user->name;
		// Rendering and sending email.
		$manager = \TooBasic\Managers\EmailsManager::Instance();
		$manager->setEmailPayload($payload);
		$manager->run();
		return $manager->send();
	}
	protected function init() {
	}
}
```
Also we're going to change a little our email controller:
```php
<?php
class HelloEmail extends \TooBasic\Email {
	protected $_layout = 'basic_layout';
	protected function basicRun() {
		$this->assign('name', $this->_payload->name);
		return true;
	}
	protected function simulation() {
		$this->assign('name', 'Some User');
		return true;
	}
}
```

### Let's explain things
The first thing to understand is the payload it self
```php
		. . .

		// Creating and setting email payload.
		$payload = new \TooBasic\EmailPayload();
		$payload->setName('hello');
		$payload->setSubject('Hello Friend');
		$payload->setEmails($user->email);
		$payload->name = $user->name;

		. . .
```
* Creates a new payload object to exchange with an email controller.
* Sets teh name of the email controller, view and other things related it it.
* Sets what subject you the email will have when it arrives, in this case 'Hello
Friend'.
* Sets the email address.
* And, this is the tricky part, it sets a value that can be used inside the email
controller. This is a dynamic behavior we don't like but it could be worse :/
	* If you take a look at the changes we made to our mail, it now uses
	information in the payload.

The second thing to understand is how we actually send it:
```php
		. . .

		// Rendering and sending email.
		$manager = \TooBasic\Managers\EmailsManager::Instance();
		$manager->setEmailPayload($payload);
		$manager->run();
		return $manager->send();

		. . .
```
* Invokes the _emails manager_.
* Set the payload to send the right mail to the right recipient.
* Renders the email.
* Sends the email return the operation result.
	* Remember, if it's true, it only means the email was sent to an email
	queue, it doesn't mean it was actually delivered. Asynchronous mumbo-jumbo
	that can give you a little headache.

## Exports
Can I use `{$ctrl->...` in the same way I do on controllers?
Yes, you can (except for `insert()`) and perhaps the only difference here is the
server name.
If you check the email you've sent, all URL have the server name and this is
because your mail won't be displayed inside your page which requires that you send
full URLs.

If for any reason, your server names is being wrongly calculated, you can force it
when you create the _email payload_ with something like this:
```php
		. . .

		$payload->setServer('https://www.example.com/');

		. . .
```

## Strip tags
If you google a little about HTML emails you'll find a common problem with HTML
tags like `<style>` and `<script>` where they get removed by email clients to
avoid problems.
If you want to have control of that and strip those tags before sending it, you
can alter your _email payload_ in this way:
```php
		. . .

		$payload->setStripTags(true);

		. . .
```
And you can debug it calling to an URL like this one:
>http://www.example.com/mysite/?debugemail=hello&debugemailstriptags

## Origin
If you look at your email headers you're going to find some default values you may
not like, header like:

* __From__: _somewhere@example.com_
* __Replay to__: _noreplay@example.com_

We strongly recommend you to change these values into something that suites your
site.
For that you can set this default values:
```php
$Defaults[GC_DEFAULTS_EMAIL_FROM] = 'mailer@mysite.com';
$Defaults[GC_DEFAULTS_EMAIL_REPLAYTO] = 'no-replay@mysite.com';
```

## Suggestions
If you want, you may visit these links:

* [Basic Controllers Layouts](uselayout.md)
* [Models](models.md)
