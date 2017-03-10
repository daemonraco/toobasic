# TooBasic: Changes
This is a changes log based on Git's log.

## Version 2.3.0:

* Review ItemsStream on RESTFul Logics ([#213](https://github.com/daemonraco/toobasic/issues/213)).
	* Adding a new middle class called `ItemsStream`.
	* `ItemsFactory` implements a new method to make use of `ItemsStream` and also uses it in `itemsBy()`.
	* Adapting methods in `ItemsFactory` to use streams instead of full lists (in other words `fetchAll()`).
		>Methods that where adapted:
		>* `idsBy($conditions, $order = [])`
		>* `idBy($conditions, $order = [])`
		>* `items()`
		>* `itemsBy($conditions, $order = [])`
		>* `itemBy($conditions, $order = [])`
		>
		>New methods related to streams and their similarities:
		>* `stream()`
		>	* `ids()`
		>	* `items()`
		>* `streamBy($conditions, $order = [])`
		>	* `idsBy($conditions, $order = [])`
		>	* `itemsBy($conditions, $order = [])`
		>
		>Methods that remain as they were:
		>* `ids()`
		>* `item($id)`
		>* `idsByNamesLike($pattern)`
		>* `itemByName($name)`
		>* Using items stream.

	* Compatibility bug fixes. I forgot that `SQLite` and `PDOStatement::rowCount()` don't see eye to eye.
	* Using `ItemsStream` on `SearchManager`.
* The internal search Engine:
	* Criteria on Search Engine ([#199](https://github.com/daemonraco/toobasic/issues/199))
		* Adding criteria mechanism.
			* Updating table structures.
			* Updating interfaces.
			* Updating cron tools and services.
			* Minor documentation updates (needs more).
		* Updating and fixing representation:
			* Track Dirty Properties ([#200](https://github.com/daemonraco/toobasic/issues/200))
			* Persisting Calls Order ([#201](https://github.com/daemonraco/toobasic/issues/201))
			* postPersist() is not being called ([#202](https://github.com/daemonraco/toobasic/issues/202))
	* More stats information ([#196](https://github.com/daemonraco/toobasic/issues/196)).
	* Tracks execution time.
	* Returns stats.
	* Search cron tool allows `limit` and `offset`.
	* New constants.
* Use `idsBy()` for `ids()` and others ([#180](https://github.com/daemonraco/toobasic/issues/180))
	* Reusing methods instead of writing separated ones.
* Adapters for MySQL, SQLite and PostgreSQL extend the use of column flags on selects ([#195](https://github.com/daemonraco/toobasic/issues/195)) allowing `>`, `<` and `!`.
* Adding a new debug flag called `debugctrl` ([#192](https://github.com/daemonraco/toobasic/issues/192)).
* Removing deprecated functionalities prior to v2.3.0 ([#188](https://github.com/daemonraco/toobasic/issues/188)).
	* Removing old core properties on representations.
	* Adding configuration checks and exceptions.
	* Updating search engine table representations.
* Simple API Reports dynamic parameters ([#194](https://github.com/daemonraco/toobasic/issues/194)).
* Index Only Pending Items ([#216](https://github.com/daemonraco/toobasic/issues/216))
	* `SearchManager` changes its internal logic to use only pending items when indexing.
	* Representations and searchable representations (and factories) add a new configurable field to point which column holds the indexation status flag.
		* Also methods to get which values mean indexed and unindexed in their respected tables.
	* Addapting JSON specifications.
	* Adding some pending ignores.
* Adding a new method on `SearchManager` to search and remove index entries for lost items ([#198](https://github.com/daemonraco/toobasic/issues/198)).
	* Search cron tools adds an option to call this functionality.
* Adding some useful methods to configs interpreters.
* Catching some more exceptions Simple API Reader sys-tool.
* A bit of PSR-1 on constants.
* Travis CI
	* Flexible PHPUnit version configuration.
	* PHP-7.1 becomes required.
* Adapting test cases.
* Updating submodule versions.
* Updating documentation and code documentation.
* Bug fixes everywhere.
	* ...insert Buzz Lightyear meme here :)

## Version 2.2.0:

* Psychotic Coding: `array()` to `[]`.
* Adding some minified version of some JS files (... yup, I found a friendly way to do this).
* Update libraries/README.md ([#174](https://github.com/daemonraco/toobasic/issues/174))
* Adding a loader for Composer libraries imported in `ROOTDIR/libraries`
	* If you run `composer require somerepo/somelib` inside `ROOTDIR/libraries`, TooBasic will load it automatically.
* Test cases helper manages cookies.
* Interface `SearchableItem` adds a method called `viewLink()` to provide better access to items.
* Updating test cases regarding new exception messages on Smarty.
* Updating submodules' versions.
* Adding json-validator as submodule.
* Documentation.
* Code documentation.
* Disable JSON Specs Validation When Installed ([#176](https://github.com/daemonraco/toobasic/issues/176))
	* Adding a new class to centralize JSON specs validations.
		* It controls installation status.
	* Adapting classes:
		* `Form`
		* `DBStructureManager`
		* `CorePropsJSON`
		* `RestConfig`
		* `SApiManager`
		* `SApiReporter`
* Update jQuery and Bootstrap Libs ([#185](https://github.com/daemonraco/toobasic/issues/185))
* Update jQuery and Bootstrap Libs ([#185](https://github.com/daemonraco/toobasic/issues/185))
* Unify Representation's Core Properties ([#187](https://github.com/daemonraco/toobasic/issues/187))
	* Moving core properties of item representation and items factory to a separated class.
		* Also this class can be avoided using a JSON specification.
		* Adding a interpreter for JSON core properties specifications.
		* Adding JSONValidator specs for JSON specification.
		* Adapting super-object `Paths` and `Names`.
		* For compatibility, all core properties will remain, but they're going to be removed on version _v2.3.0_ (issue #188).
	* Database adapter adds a method called `name()`.
	* Adding test cases for table using version 1 on their specifications.
	* Nicer specs building for tables.
	* Adapting table scaffolds to use the new mechanism.
		* Also test cases.
	* Updating specification checkers.
	* Updating sys-tool `table`.
* List of Dependant Representations ([#186](https://github.com/daemonraco/toobasic/issues/186))
	* Adding association logic for sub-lists.
	* Methods on items factories like `ids()`, `items()`, etc. allow order specification.
* JSON Validator for Database Specs ([#170](https://github.com/daemonraco/toobasic/issues/170))
	* Adding multi-version specification and logic to validate it.
	* Updating test cases.
* RESTful Assets
	* Adding a new JavaScript asset for RESTful resources.
	* Fixing namespace checks on `toobasic_asset.js`.
* RESTful Representations ([#163](https://github.com/daemonraco/toobasic/issues/163))
	* New RESTful artifacts.
		* New manager.
		* New rest config interpreter.
		* New constants.
		* JSON validation specs.
		* JSON initial configuration.
		* New friendly-urls configurations on `.htaccess`.
		* New translations.
	* Adding a way to expand all extended properties of a representation.
	* Adding a new parameters stack that allows modification of super globals.
		* Magic prop `params` uses it for `$_SESSION` and `$_COOKIES`.
	* Adding a way to search items.
* Systool table limit name field ([#182](https://github.com/daemonraco/toobasic/issues/182))
	* Adding specific limit.
* Search By Any Field ([#179](https://github.com/daemonraco/toobasic/issues/179))
	* Adding new methods.
* FormsBuilder Specs ([#171](https://github.com/daemonraco/toobasic/issues/171))
	* Adding a new JSON specifications file.
	* Checking form specs using JSONValidator.
		* Removing unnecessary checks.
	* Adapting test cases.
* New JSON specification for Simple API Reader Reports ([#173](https://github.com/daemonraco/toobasic/issues/173)).
* Unneeded defaults on service sys-tool ([#181](https://github.com/daemonraco/toobasic/issues/181))
* SApiReader ([#172](https://github.com/daemonraco/toobasic/issues/172))
	* Validating JSON against specs (abstract and non-abstract).
	* Adding an abstract spec.
	* Adding translations.
	* Adapting test cases.
* Bug fixes:
	* Fixing some constant values.
	* Fixing exception translation errors.
	* Specs bug fix for #172.
	* `Configs` exceptions.
		* Wrong class namespace.
		* Wrong exception message build.
	* Table scaffods where creating a field called `key` for tables version 2, instead of `keys`.
	* Predictive scripts for jQuery autocompletion fix a issue with ng-model (AngularJS).
	* Bug fix on `SearchManager`, items here wrongly grouped.
	* Updating scaffolds... in other words, bug fix.
	* Sys-tool table was generating forms inside views with wrong attributes.
	* It is `__construct`, not `__constructor`.
	* Minor bug on `DBStructureManager`.

## Version 2.1.0:

* Simple API Tester ([#151](https://github.com/daemonraco/toobasic/issues/151)).
* Controllers and services _sys-tool_ now generates routes considering default values ([#115](https://github.com/daemonraco/toobasic/issues/115)).
* Routes now support the keyword `table` and the table _sys-tool_ now generates routes considering this keyword ([#147](https://github.com/daemonraco/toobasic/issues/147)).
* Almost all exception messages get translation configurations ([#145](https://github.com/daemonraco/toobasic/issues/145)).
* Smarty delimiters can be changed ([#145](https://github.com/daemonraco/toobasic/issues/145)).
* New debug parameter called `debugmagicprop` ([#153](https://github.com/daemonraco/toobasic/issues/153)).
* Redis support gets moved to a external plugin ([#118](https://github.com/daemonraco/toobasic/issues/118)).
* Updating test cases.
* Documentation bug fixes.
* TravisCI tests now include PHP-7.1.
	* Also support for PHP-5.4 gets removed.

## Version 2.0.0:

* Adding a dummy function for `json_last_error_msg()` on PHP versions lower than
  5.5.
* _SApiReader_ and _SApiManager_ add more checks on specification files.
* RoutesManager adds more checks on specification files.
* Forms Builder:
	* Stops catching exceptions and let them be managed by TooBasic default
  mechanisms.
	* Better exception messages.
	* More checks for `enum` fields.
	* Replacing hard-coded names by its already existing constants.
* Fixing background styles
  ([#144](https://github.com/daemonraco/toobasic/issues/144)).
* Fixing misspelling on exception class.
* Fixing messages on some JSON exceptions.
* Improving JSON checks in `RoutesManager` and `Translate` singletons.
* Class alias bug fix in `RoutesManager`.
* `index.php` always checks for exceptions.
* Fixing error behaviors on `ActionsManager`.
* Bug fix in `ActionsManager`. It was not checking for the right controller
  pointer with secondary error.
* Bug fix in `ServicesManager`. It was not build the right response on errors.
* Bug fix in `ErrorController`. There was a parameter hint that in some extreme
  cases it throws an error.
* Debug flag `debugnolayout` becomes `nolayout`
  ([#141](https://github.com/daemonraco/toobasic/issues/141)).
* Adding documentation about module manifest files
  ([#81](https://github.com/daemonraco/toobasic/issues/81)).
* Bug fix in `ManifestsManager` and `toobasicfunctions.php` regarding broken or
  not installed modules.
* Code documentation for Forms Builders
  ([#119](https://github.com/daemonraco/toobasic/issues/119)).
* Some integration with GitBooks.
* Some integration CodeClimate.
* Core function `\TooBasic\objectCopyAndEnforce` gets improved.
* SApiReader Reports ([#135](https://github.com/daemonraco/toobasic/issues/135))
* Converting some hard-coded paths into configurations.
* Adding an exception check in `SApiManager`.
* Fixing a bug in `exception_page.php`.
* Config File Abstractions
	* Adding a new mode to load config files called _Merge_.
		* Also adding a test case for it.
		* And documentation.
* Fixing a bug with a not "happy flow". `ActionsManager` was trying to use a
  controller that it already knew was not loaded.
* Fining a tiny bug in `SearchManager`, just a semi-colon... that made all
  queries to return empty results :)
* Less @ flags, if it explodes, I wanna see it :)
* Adding a reset mechanism to singleton `Paths`.
* Bug fix in class `OptionsStack`, `throw` is not `return`, duh!.
* Adding some pending help text
  ([#123](https://github.com/daemonraco/toobasic/issues/123)).
* Fixing uninitialized variables bug
  ([#122](https://github.com/daemonraco/toobasic/issues/122)).
* Removing unused variables.
* Removing eval usage in class `ComplexConfig`.
* Background image recompression.
* Forms Builder ([#117](https://github.com/daemonraco/toobasic/issues/117))
* Extendable `ControllerExports`
  ([#116](https://github.com/daemonraco/toobasic/issues/116))
* Paths Search Priority ([#114](https://github.com/daemonraco/toobasic/issues/114)).
* Adding a parameter called `debugconfigs` to debug how config files are loaded
  and way.
* Better looks in `debugdebugs`.
* Better looks in tables generated by sys-tool table.
* Suggesting known (kinda stable) modules in documentation.
* Options in Params ([#113](https://github.com/daemonraco/toobasic/issues/113)).

## Previous
To get information about previous version go to [this
link](https://github.com/daemonraco/toobasic/commits/master).
