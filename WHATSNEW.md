# TooBasic: What's New

This is a changes log based on issues and logic changes.

## Version 2.1.0:

* Simple API Tester ([#151](https://github.com/daemonraco/toobasic/issues/151))
>Now it's possible to use things like:
>```text
>php shell.php sys sapitester
>```
* Scaffolds with default values in routes ([#115](https://github.com/daemonraco/toobasic/issues/115))
>Controllers and services _sys-tools_ generate multiple routes depending on parameter defaults.
* Keyword 'table' for routes ([#147](https://github.com/daemonraco/toobasic/issues/147))
>Routes add the keyword `table`.
* Debug Parameter for MagicProps ([#156](https://github.com/daemonraco/toobasic/issues/156))
>New debug parameter called `debugmagicprop`.
* Exception translations ([#145](https://github.com/daemonraco/toobasic/issues/145))
>Almost all exception messages use translations.
* Set Smarty Delimiters ([#153](https://github.com/daemonraco/toobasic/issues/153))
>Smarty delimiters can be changed.
* Move Redis Support Into A Module ([#118](https://github.com/daemonraco/toobasic/issues/118))
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
