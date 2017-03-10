# TooBasic: What's New
This is a changes log based on issues and logic changes.

## Version 2.3.0:

* __ItemsStream ([#212](https://github.com/daemonraco/toobasic/issues/212))__
>Major improvement in the way _Representation Fatories_ retrieve and provide items
>Giving a better way to walk over them improving memory usage.
>Related issues:
>* Review ItemsStream on RESTFul Logics ([#213](https://github.com/daemonraco/toobasic/issues/213))
>* Review ItemsStream on Search Tools and Services ([#215](https://github.com/daemonraco/toobasic/issues/215))
* __Track Dirty Properties ([#200](https://github.com/daemonraco/toobasic/issues/200))__
>_Representations_ add a way to know which properties were modified and are
>pending persistence. This allow the user to make better decision on methods like
>'prePersist()'.
* __Remove Lost Items on Search Engine Tables ([#198](https://github.com/daemonraco/toobasic/issues/198))__
>The internal search engine adds a mechanism to search and clean indexed entries
>that where removeds.
* __Index Only Pending Items ([#216](https://github.com/daemonraco/toobasic/issues/216))__
>Improves the way the internal search engine indexes items. Basically it use to
>check every item for their indexation status and now it solves that with a simple
>query and then indexes only pending items. In other words, the previous loging
awful, awful indeed.
* __Criteria on Search Engine ([#199](https://github.com/daemonraco/toobasic/issues/199))__
>Adds a way to somehow tag indexed entries and separate search result in groups.
* __Search Engine Improvements ([#196](https://github.com/daemonraco/toobasic/issues/196))__
>Adds a few improvements:
>* Tracks search times.
>* Provides statistics from searches.
>* Searchesallow limit and offsets.
* __Set Zero on NULL fields ([#197](https://github.com/daemonraco/toobasic/issues/197))__
>An old and never solved bug.
* __Extend Query Adapter Flags on Conditions ([#195](https://github.com/daemonraco/toobasic/issues/195))__
>Query adapters add the use of `>`, `<`, `!` on query conditions specification.
* __Simple API Reports Dynamic Parameters ([#194](https://github.com/daemonraco/toobasic/issues/194))__
>Giving more flexibility when invoking a Simple API Report. Visit
>[Dynamic parameters](docs/sapireports.md#dynamic-parameters) for more information
>about it.
* __Debug Parameter for Controller Exports ([#192](https://github.com/daemonraco/toobasic/issues/192))__
>Adding a new debug parameter called `debugctrl` to review functionlities exported
>from the controller in views.
* __Deprecated Functionalities (v2.3.0) ([#188](https://github.com/daemonraco/toobasic/issues/188))__
>Removing functionalities flagged as deprecated mostrly related to changes in
>'Representations' and some old core functions added to provide compatibility.
* __Use 'idsBy()' for 'ids()' and others ([#180](https://github.com/daemonraco/toobasic/issues/180))__
>Representations avoids duplicated code by using even more its internal and more
>generic methods.
* __Code quality__
>Improvements in code quality validated by [SensioLabs](https://insight.sensiolabs.com/projects/a78eb001-d887-4214-a390-3a1993fc6d3c).
> This changes are related to issues:
>* [#203](https://github.com/daemonraco/toobasic/issues/203)
>* [#204](https://github.com/daemonraco/toobasic/issues/204)
>* [#205](https://github.com/daemonraco/toobasic/issues/205)
>* [#206](https://github.com/daemonraco/toobasic/issues/206)
>* [#207](https://github.com/daemonraco/toobasic/issues/207)
>* [#208](https://github.com/daemonraco/toobasic/issues/208)
>* [#209](https://github.com/daemonraco/toobasic/issues/209)
>* [#210](https://github.com/daemonraco/toobasic/issues/210)
>* [#211](https://github.com/daemonraco/toobasic/issues/211)
* __Bug fixes everywhere__


-- ---------------------------------------------------------------------------- --

## Version 2.2.0:

* __RESTful Representations ([#163](https://github.com/daemonraco/toobasic/issues/163))__
>Providing restful access to all resources even considering authorization checks.
* __Unify Representation's Core Properties ([#187](https://github.com/daemonraco/toobasic/issues/187))__
>All main properties of item representations and items factories get factorized
>into a single and centralized file.
* __List of Dependant Representations ([#186](https://github.com/daemonraco/toobasic/issues/186))__
>If an item representation acts as grouping item for other representation, there's
now a way to access its children through a method.
* __Update jQuery and Bootstrap Libs ([#185](https://github.com/daemonraco/toobasic/issues/185))__
>Default libraries for jQuery and Bootstrap get a version update.
* __Systool table limit name field ([#182](https://github.com/daemonraco/toobasic/issues/182))__
>To avoid issues with database index restrictions, name fields on tables get
>restricted to 64 characters (only when created as scaffold).
* __Search By Any Field ([#179](https://github.com/daemonraco/toobasic/issues/179))__
>Representations add a way to retrieve items specifying any column and value.
* __Use JSON Validator ([#166](https://github.com/daemonraco/toobasic/issues/166))__
>Adding a JSON validation library.
* __JSON Validator for SApiReader ([#172](https://github.com/daemonraco/toobasic/issues/172))__
>Validating Simple API Reader JSON specifications ussing the new library.
* __JSON Validator for SApiReports ([#173](https://github.com/daemonraco/toobasic/issues/173))__
>Validating Simple API Reports JSON specifications ussing the new library.
* __JSON Validator for FormsBuilder Specs ([#171](https://github.com/daemonraco/toobasic/issues/171))__
>Validating FormBuilder JSON specifications ussing the new library.
* __JSON Validator for Database Specs ([#170](https://github.com/daemonraco/toobasic/issues/170))__
>Validating database JSON specifications ussing the new library.
* __Disable JSON Specs Validation When Installed ([#176](https://github.com/daemonraco/toobasic/issues/176))__
>JSON specifications are not checked when the site is flagged as _installed_.
* __Update libraries/README.md ([#174](https://github.com/daemonraco/toobasic/issues/174))__
>Updated internal documentation.
* __Unneeded defaults on service sys-tool ([#181](https://github.com/daemonraco/toobasic/issues/181))__
>Bug fix.
* __Remove All Routes When Destroying a Controller or Service ([#160](https://github.com/daemonraco/toobasic/issues/160))__
>Bug fix.

## Version 2.1.0:

* __Simple API Tester ([#151](https://github.com/daemonraco/toobasic/issues/151))__
>Now it's possible to use things like:
>```text
>php shell.php sys sapitester
>```
* __Scaffolds with default values in routes ([#115](https://github.com/daemonraco/toobasic/issues/115))__
>Controllers and services _sys-tools_ generate multiple routes depending on parameter defaults.
* __Keyword 'table' for routes ([#147](https://github.com/daemonraco/toobasic/issues/147))__
>Routes add the keyword `table`.
* __Debug Parameter for MagicProps ([#156](https://github.com/daemonraco/toobasic/issues/156))__
>New debug parameter called `debugmagicprop`.
* __Exception translations ([#145](https://github.com/daemonraco/toobasic/issues/145))__
>Almost all exception messages use translations.
* __Set Smarty Delimiters ([#153](https://github.com/daemonraco/toobasic/issues/153))__
>Smarty delimiters can be changed.
* __Move Redis Support Into A Module ([#118](https://github.com/daemonraco/toobasic/issues/118))__
>Redis support in no longer a required part of __TooBasic__.

## Version 2.0.0:

* __Shell Tools Aliases ([#65](https://github.com/daemonraco/toobasic/issues/65))__
>Now it's possible to use things like:
>```text
>php shell.php newctrl hello
>```
* __Empty Item Creation Method Disable Mechanism ([#69](https://github.com/daemonraco/toobasic/issues/69))__
>Representations factories can disable the empty item creation method `create()`
>and show a proper message indicating the right method to use.
* __Dependencies Between Modules ([#71](https://github.com/daemonraco/toobasic/issues/71))__
>Modules may now specify dependencies with other modules version.
* __Sys-tools: Separate Nav from layout ([#73](https://github.com/daemonraco/toobasic/issues/73))__
>_Sys-tool_ `layout` now creates a separate template for navigation on _Twitter
>Bootstrap_ layouts.
* __Configs Manager ([#74](https://github.com/daemonraco/toobasic/issues/74))__
>Configuration files get a manager to access and use them as objects.
* __Dynamic MagicProp ([#75](https://github.com/daemonraco/toobasic/issues/75))__
>Any module or even the site may define and add magic properties.
* __Table Specs v2 ([#76](https://github.com/daemonraco/toobasic/issues/76))__
>Database table specifications get a simpler definition mechanism.
* __HTML Assets in Sys-tool Layout ([#77](https://github.com/daemonraco/toobasic/issues/77))__
>_Sys-tool_ `layout` creates layouts with dynamic asset inclusion code and all
>required configuration PHP codes.
* __Specific Config Interpreters ([#78](https://github.com/daemonraco/toobasic/issues/78))__
>Configuration files may use a specific class to handle their contents and
>behavior.
* __Excepted Paths ([#79](https://github.com/daemonraco/toobasic/issues/79))__
>Internal path searches add a way to avoid specific files or even entire folders.
* __Document Modules ([#81](https://github.com/daemonraco/toobasic/issues/81))__
>Documentation about modules, how to create them and how to specify their
>manifests.
* __Disable Debug Parameters ([#84](https://github.com/daemonraco/toobasic/issues/84))__
>Adding a mechanism to disable debug parameters improving security for production
>environments.
* __Like in Query adapters ([#86](https://github.com/daemonraco/toobasic/issues/86))__
>Queries created using __TooBasic__ generic adapters allow partial value
>specification.
* __Custom Paths Search ([#87](https://github.com/daemonraco/toobasic/issues/87))__
>Internal path searches add a mechanism to search custom paths.
* __Simple API Reader ([#88](https://github.com/daemonraco/toobasic/issues/88))__
>Adding a mechanism access external API as internal objects based on a JSON
>specification.
>For more information read [this documentation](docs/sapireader.md).
* __ItemsFactory::idsByNamesLike() ([#91](https://github.com/daemonraco/toobasic/issues/91))__
>Items factories add a way to retrieve not specific names.
* __Sys-tool Table & Predictive Search ([#92](https://github.com/daemonraco/toobasic/issues/92))__
>_Sys-tool_ `table` may generate services and configurations to enable predictive
>searches on tables.
* __Service Transaction Tracking ([#93](https://github.com/daemonraco/toobasic/issues/93))__
>When calling a service, a specific field called `transaction` can be specified
>to track a specific response when many calls are made to the same service.
* __Representation Field Filters ([#96](https://github.com/daemonraco/toobasic/issues/96))__
>Representations add a mechanism to consider specified columns to have certain
>behavior. For example, a column with same text can be consider as a serialized
>JSON object, allowing the user to access it as an object, but loading and
>storing it as a string.
* __Search Engine ([#97](https://github.com/daemonraco/toobasic/issues/97))__
>__TooBasic__ add a simple search engine that can be used in command line or as
>a object inside models, controllers and others.
>For more information read [this documentation](docs/searchengine.md).
* __PHP Writing for Scaffolds ([#99](https://github.com/daemonraco/toobasic/issues/99))__
>Scaffolds can now add configurations in PHP code.
* __Routes for Services ([#100](https://github.com/daemonraco/toobasic/issues/100))__
>Services now also support route specifications.
* __Multi-table Selects in Query Adapters ([#102](https://github.com/daemonraco/toobasic/issues/102))__
>Queries created using __TooBasic__ generic adapters allow multiple tables.
* __New README File ([#107](https://github.com/daemonraco/toobasic/issues/107))__
>Better landing document :D
* __Sub-representation expansion ([#110](https://github.com/daemonraco/toobasic/issues/110))__
>Representation may specify that one or more colums are IDs from other
>representation and then have them expanded as objects when loaded.
* __Options in Params ([#113](https://github.com/daemonraco/toobasic/issues/113))__
>Shell option can now be used as params from magic properties.
* __Paths Search Priority ([#114](https://github.com/daemonraco/toobasic/issues/114))__
>Fixing some issues with path searches and their priorities.
* __Extendable 'ControllerExports' ([#116](https://github.com/daemonraco/toobasic/issues/116))__
>Controllers add a way to export more methods into views.
* __Forms Builder ([#117](https://github.com/daemonraco/toobasic/issues/117))__
>__TooBasic__ adds a generic way to specify forms based on JSON configurations
>and some shell tools to make this process simpler.
>For more information read [Quick Forms (for Forms Builder)](docs/qforms.md)
>and [Forms Builder](docs/forms.md).
* __SApiReader Reports ([#135](https://github.com/daemonraco/toobasic/issues/135))__
>__TooBasic__ adds an extension for _Simple API Reader_ that allows to create a report table
>based on an API response.
>For more information read [this document](docs/sapireports.md).
* Off course, checks and bug fixes everywhere.

Visit [this link](https://github.com/daemonraco/toobasic/issues?q=milestone%3Av2.0.0+is%3Aclosed) for a
complete list of issues.

## Previous
To get information about previous version you'll have to check on
[commits](https://github.com/daemonraco/toobasic/commits/master) and other
[issues](https://github.com/daemonraco/toobasic/issues?q=is%3Aclosed).
