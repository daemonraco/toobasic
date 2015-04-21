# TooBasic: Shell Tools and Crons
## What's a Shell Tools?
Let's say your site is so complex it requires some kind of external process to maintain it, some kind of script you can run directly in the server and not through an URL.
A _shell tool_ is one of those processes written in a way __TooBasic__ can handle.

Once again, let's use a simple example to understand how it works.
Suppose we have a strict site where inactive users get removed, so, if you haven't logged in the last 3 months you loose your account.
For this, you created a _model_ saved at __ROOTDIR/site/models/Users.php__ that allows you to remove inactive users and your tried to use it in your log-in service.
Using it there was a good idea at first but your site now have thousands and thousands of users and that operation is taking a long time.
Let's port that idea into a _shell tool_ so it could be executed in the server whenever your site admin sees it fit.

## Creating a Shell Tool
Following the example we're going to write the next code and save it in __ROOTDIR/site/shell/tools/users.php__:
```php
<?php
class UsersTool extends \TooBasic\Shell\ShellTool {
	protected function mainTask($spacer = "") {
		echo "{$spacer}Removing invalids: ";
		$this->model->Users->removeInvalids();
		echo "Done\n";
	}
	protected function setOptions() {}
	protected function taskInfo($spacer = "") {}
}
```

Now we can execute this tool directly in our server with a command like this one:
```html
$ sudo -u www-data php /var/www/mysite/shell/main.php tool users
```

Assumptions:

* You are using a [*nix](https://en.wikipedia.org/wiki/Unix-like) operation system.
* `php` is inside a directory named in `$PATH`.
* Your document root is `/var/www`.
* You installed __TooBasic__ in `/var/www/mysite`.
* Your web user is `www-data`

Why `sudo`? well, we don't want to have access problems later.

## Let's Make Things Interesting
Let's suppose your _model_ allows you to know which users are going to be removed and you want to list them before anything.
For this we're going to add the option `-l` and give it some meaning.
```php
<?php
class UsersTool extends \TooBasic\Shell\ShellTool {
	protected function mainTask($spacer = "") {
		echo "{$spacer}Removing invalids: ";
		$this->model->Users->removeInvalids();
		echo "Done\n";
	}
	protected function setOptions() {
		$this->_options->setHelpText("This tool allows to perform certain tasks related with users.");
		$opt = new \TooBasic\Shell\Option("ListInvalids");
		$opt->setHelpText("Prompts a list of users that have to be removed due to inactivity.");
		$opt->addTrigger("-l");
		$opt->addTrigger("--list");
		$this->_options->addOption($opt);
	}
	protected function taskInfo($spacer = "") {}
	protected function taskListInvalids($spacer = "") {
		echo "{$spacer}Invalids users:\n";
		foreach($this->model->Users->invalids() as $user) {
			echo "{$spacer}\t- {$user->name} ({$user->id})\n";
		}
	}
}
```
Now we can execute this command:
```html
$ sudo -u www-data php /var/www/mysite/shell/main.php tool users -l
```

What's going on here?:

* We added a help text to explain our tool.
* We created a new option with its help text and triggers, and added it to out options.
* And we created a methods `taskListInvalids()` for that option.

Now we can do this:
```html
$ sudo -u www-data php /var/www/mysite/shell/main.php tool users -l
Invalids users:
	- john42 (6532)
	- daemonraco (666)
$ sudo -u www-data php /var/www/mysite/shell/main.php tool users -h
This tool allows to perform certain tasks related with users.

	--help, -h
		Shows this help text.

	--version, -V
		Shows this tool's version number.

	--info, -I
		Shows this tool's information.

	-l, --list
		Prompts a list of users that have to be removed due to inactivity.

```

But, How? Yes, you may not see the connection here. A shell tool assumes that every option has a method that responds to it, if not `mainTask()` is called.
In our example we created an option called __ListInvalids__, that means there may be a method called `taskListInvalids()` to attend it.

And `-h`? By default, every _shell tool_ has some general options to show:

* Its help text (`--help` or `-h`).
* Its version number (`--version` or `-V`).
* Some extra information about it (`--info` or `-I`).

## Things We Didn't Explain
When you are creating a _shell tool_ you must first pick a name in lower-case without special characters (geeky info: `/([a-z0-9_]+)/`), in our example it was __users__.
Then you must create a class using that name (in capital-case) and the suffix `Tool`, something like __UsersTool__. It must inherit from `\TooBasic\Shell\ShellTool`.
Finally you must store it in __ROOTDIR/site/shell/tools/users.php__ and that's it.

## Recommendation
We recommend you to create a tool like this in this way:
```php
<?php
use \TooBasic\Shell\Option as TBS_Option;
class UsersTool extends \TooBasic\Shell\ShellTool {
	protected function setOptions() {
		$this->_options->setHelpText("This tool allows to perform certain tasks related with users.");
		$this->_options->addOption(TBS_Option::EasyFactory("ListInvalids", array("-l","--list"), TBS_Option::TypeNoValue, "Prompts a list of users that have to be removed due to inactivity."));
		$this->_options->addOption(TBS_Option::EasyFactory("RemoveInvalids", array("-rm","--remove-invalids"), TBS_Option::TypeNoValue, "Removes users that have become invalid due to inactivity."));
	}
	protected function taskInfo($spacer = "") {}
	protected function taskListInvalids($spacer = "") {
		echo "{$spacer}Invalids users:\n";
		foreach($this->model->Users->invalids() as $user) {
			echo "{$spacer}\t- {$user->name} ({$user->id})\n";
		}
	}
	protected function taskRemoveInvalids($spacer = "") {
		echo "{$spacer}Removing invalids: ";
		$this->model->Users->removeInvalids();
		echo "Done\n";
	}
}
```
In this way you force the user to give a parameter specifying _what_ they want to do.
Also, you are using a simpler way to add options.

## Cron Tools
What is a _cron tool_? well, basicly is a _shell tool_ but it controls that only one instance of a task is running at the same time.

In our example, if more than one user or an automatic process is using the option to remove invalid users, it may cause collisions when removing historic files, cleaning database tables, etc.
This is an excellent example of a task that must run one at a time.

## Create a Cron Tool
At this point we've realized that our _shell tool_ must be a _cron tool_ and we're going to make some changes:

* Move it to __ROOTDIR/site/shell/crons/users.php__.
* Change its class' name and inheritance:
```php
<?php
class UsersCron extends \TooBasic\Shell\ShellCron {
	. . .
```

Now you can call it this way:
```html
$ sudo -u www-data php /var/www/mysite/shell/main.php cron users -rm
```

## How Does It Work?
Every time you call a _cron tool_ task, it:

* creates a flag file in __ROOTDIR/cache/shellflags__ to refer the running task (for example __UsersCron_taskRemoveInvalids.flag__),
* execute the task and
* removes the flag file.

If a _cron tool_ task is called and there's already a flag file, it avoids executing the task and prompts an error.

## Dead Flags
Sometimes when a _cron tool_ fails due to unexpected errors or development bugs, the _cron tool_ may not remove the flag file and return and error of _other instance running_ when there's none.
For those cases you can add the flag `-CF` and it will clear the flag (this doesn't execute the actual task), something like this:
```html
$ sudo -u www-data php /var/www/mysite/shell/main.php cron users -rm -CF
```
