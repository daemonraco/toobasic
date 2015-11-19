# TooBasic: Index
## Main Page (README.md):

* [What is it?](README.md#what-is-it)
* [Why would I use this at all?](README.md#why-would-i-use-this-at-all)
* [Basic features?](README.md#basic-features)
* [Folders](README.md#folders)
* [How to create a basic page](README.md#how-to-create-a-basic-page)
	* [Controller](README.md#controller)
	* [Template](README.md#template)
	* [Is that it?](README.md#is-that-it)
	* [But?](README.md#but)
* [Even more basic](README.md#even-more-basic)
* [Even easier?!](README.md#even-easier)
* [Suggestions](README.md#suggestions)
* [Thanks](README.md#thanks)
	* [Smarty](README.md#smarty)
	* [Twitter Bootstrap](README.md#twitter-bootstrap)
	* [jQuery](README.md#jquery)

##  Adapters (docs/adapters.md):

* [What are _adapters_](docs/adapters.md#what-are-adapters)
* [Cache adapters](docs/adapters.md#cache-adapters)
* [Database structure maintainer adapters](docs/adapters.md#database-structure-maintainer-adapters)
* [I want mine!](docs/adapters.md#i-want-mine)
	* [Step 0: into a module](docs/adapters.md#step-0-into-a-module)
	* [Step 1: new adapter](docs/adapters.md#step-1-new-adapter)
	* [Step 2: using the adapter](docs/adapters.md#step-2-using-the-adapter)
	* [Step 3: including the adapter](docs/adapters.md#step-3-including-the-adapter)
* [I want my database structure maintainer adapter](docs/adapters.md#i-want-my-database-structure-maintainer-adapter)
* [View adapters](docs/adapters.md#view-adapters)
	* [Others](docs/adapters.md#others)
* [Suggestions](docs/adapters.md#suggestions)

##  Author's Note (docs/authorsnote.md):

* [TooBasic?!](docs/authorsnote.md#toobasic)
* [One action one controller?!](docs/authorsnote.md#one-action-one-controller)
* [Engrish](docs/authorsnote.md#engrish)

##  Cache (docs/cache.md):

* [We All Know What It Is](docs/cache.md#we-all-know-what-it-is)
* [What is it cached?](docs/cache.md#what-is-it-cached)
* [Adapters](docs/cache.md#adapters)
	* [File cache adapter](docs/cache.md#file-cache-adapter)
	* [Database cache adapter](docs/cache.md#database-cache-adapter)
	* [ Also](docs/cache.md#-also)
	* [Memcached adapter](docs/cache.md#memcached-adapter)
	* [Memcache adapter](docs/cache.md#memcache-adapter)
	* [Redis adapter](docs/cache.md#redis-adapter)
* [Setting an adapter](docs/cache.md#setting-an-adapter)
* [Cached controller](docs/cache.md#cached-controller)
* [Cached services?](docs/cache.md#cached-services)
* [What if you don't want it?](docs/cache.md#what-if-you-dont-want-it)
* [Duration](docs/cache.md#duration)

##  Using Config Files (docs/configs.md):

* [Is it necessary an explanation?](docs/configs.md#is-it-necessary-an-explanation)
* [User defined configuration files](docs/configs.md#user-defined-configuration-files)
* [I want 'em all!](docs/configs.md#i-want-em-all)
* [Automatic config files](docs/configs.md#automatic-config-files)
	* [_config.php_](docs/configs.md#configphp)
	* [_config_shell.php_](docs/configs.md#config_shellphp)
	* [_config_http.php_](docs/configs.md#config_httpphp)
	* [Site config file](docs/configs.md#site-config-file)
	* [Summary](docs/configs.md#summary)
* [Easier with JSON](docs/configs.md#easier-with-json)
	* [Multiple files](docs/configs.md#multiple-files)
* [Suggestions](docs/configs.md#suggestions)

##  Controller Exports (docs/controllerexports.md):

* [What are these?](docs/controllerexports.md#what-are-these)
	* [Warning](docs/controllerexports.md#warning)
* [How to call an exported function](docs/controllerexports.md#how-to-call-an-exported-function)
* [Basic path expansion](docs/controllerexports.md#basic-path-expansion)
	* [_$ctrl->css()_](docs/controllerexports.md#ctrl-css)
	* [_$ctrl->js()_](docs/controllerexports.md#ctrl-js)
	* [_$ctrl->img()_](docs/controllerexports.md#ctrl-img)
	* [_$ctrl->lib()_](docs/controllerexports.md#ctrl-lib)
	* [_$ctrl->link()_](docs/controllerexports.md#ctrl-link)
* [Controller insertion](docs/controllerexports.md#controller-insertion)
* [Snippets](docs/controllerexports.md#snippets)
* [HTML assets](docs/controllerexports.md#html-assets)
	* [HTML assets configuration](docs/controllerexports.md#html-assets-configuration)
	* [Specifics](docs/controllerexports.md#specifics)
	* [Libraries](docs/controllerexports.md#libraries)
* [Ajax insert](docs/controllerexports.md#ajax-insert)
	* [Autoloading](docs/controllerexports.md#autoloading)
	* [Parameters](docs/controllerexports.md#parameters)
	* [Attributes](docs/controllerexports.md#attributes)
	* [Reloading](docs/controllerexports.md#reloading)
* [Suggestions](docs/controllerexports.md#suggestions)

##  Databases (docs/databases.md):

* [What is it, really?](docs/databases.md#what-is-it,-really)
* [Configuration](docs/databases.md#configuration)
	* [Where to Place It?](docs/databases.md#where-to-place-it)
* [Let's Make a Query](docs/databases.md#lets-make-a-query)
* [Defaults](docs/databases.md#defaults)
* [SQLite connection](docs/databases.md#sqlite-connection)
* [PostgreSQL connection](docs/databases.md#postgresql-connection)
* [Suggestions](docs/databases.md#suggestions)

##  Database Structure Specifications (docs/databasespecs.md):

* [What is this?](docs/databasespecs.md#what-is-this)
* [Cross database basic configurations](docs/databasespecs.md#cross-database-basic-configurations)
* [Table structures](docs/databasespecs.md#table-structures)
	* [Fields](docs/databasespecs.md#fields)
	* [Column type](docs/databasespecs.md#column-type)
	* [Override policy](docs/databasespecs.md#override-policy)
* [Indexes](docs/databasespecs.md#indexes)
	* [Override policy](docs/databasespecs.md#override-policy)
* [Initial table data](docs/databasespecs.md#initial-table-data)
	* [Policies](docs/databasespecs.md#policies)
* [Connections](docs/databasespecs.md#connections)
	* [Default connection](docs/databasespecs.md#default-connection)
* [Callbacks](docs/databasespecs.md#callbacks)
	* [What else can I do?](docs/databasespecs.md#what-else-can-i-do)
	* [Indexes](docs/databasespecs.md#indexes)
	* [Why no drop callbacks?](docs/databasespecs.md#why-no-drop-callbacks)
* [Table structures version 2](docs/databasespecs.md#table-structures-version-2)
	* [String specification](docs/databasespecs.md#string-specification)
	* [Extended specification](docs/databasespecs.md#extended-specification)
	* [Indexes](docs/databasespecs.md#indexes)
* [Unknowns](docs/databasespecs.md#unknowns)
* [Performance](docs/databasespecs.md#performance)
* [Suggestions](docs/databasespecs.md#suggestions)

##  Emails (docs/emails.md):

* [Huh?!](docs/emails.md#huh)
* [Layout](docs/emails.md#layout)
	* [_%TOO_BASIC_EMAIL_CONTENT%_](docs/emails.md#%too_basic_email_content%)
	* [Default layout](docs/emails.md#default-layout)
* [Hello email](docs/emails.md#hello-email)
* [Is it right?](docs/emails.md#is-it-right)
* [How do I send it?](docs/emails.md#how-do-i-send-it)
	* [Let's explain things](docs/emails.md#lets-explain-things)
* [Exports](docs/emails.md#exports)
* [Strip tags](docs/emails.md#strip-tags)
* [Origin](docs/emails.md#origin)
* [Suggestions](docs/emails.md#suggestions)

##  Facilities (docs/facilities.md):

* [What is this?](docs/facilities.md#what-is-this)
* [Sys-tool _controller_](docs/facilities.md#sys-tool-controller)
	* [Cache](docs/facilities.md#cache)
	* [Layout](docs/facilities.md#layout)
	* [Parameters](docs/facilities.md#parameters)
	* [Module](docs/facilities.md#module)
	* [Removing an action](docs/facilities.md#removing-an-action)
* [Sys-tool _shell_](docs/facilities.md#sys-tool-shell)
	* [Creating a simple tool](docs/facilities.md#creating-a-simple-tool)
	* [Basic parameters](docs/facilities.md#basic-parameters)
	* [Master parameters](docs/facilities.md#master-parameters)
	* [What do I have?](docs/facilities.md#what-do-i-have)
	* [In a module](docs/facilities.md#in-a-module)
	* [Destroy it](docs/facilities.md#destroy-it)
	* [Cron shell tool](docs/facilities.md#cron-shell-tool)
* [Sys-tool _table_](docs/facilities.md#sys-tool-table)
	* [Creating a table](docs/facilities.md#creating-a-table)
	* [What do I get?](docs/facilities.md#what-do-i-get)
	* [Database type](docs/facilities.md#database-type)
	* [Automatics](docs/facilities.md#automatics)
	* [Raw](docs/facilities.md#raw)
	* [Removal](docs/facilities.md#removal)
	* [Module](docs/facilities.md#module)
	* [Connection?](docs/facilities.md#connection)
	* [Bootstrap](docs/facilities.md#bootstrap)
* [Sys-tool _service_](docs/facilities.md#sys-tool-service)
	* [Features](docs/facilities.md#features)
	* [Removing a service](docs/facilities.md#removing-a-service)
* [Suggestions](docs/facilities.md#suggestions)

##  Languages (docs/language.md):

* [Languages?](docs/language.md#languages)
* [How to configure it](docs/language.md#how-to-configure-it)
* [Adding translations](docs/language.md#adding-translations)
* [Using translations](docs/language.md#using-translations)
* [Advanced](docs/language.md#advanced)
* [Precompiled translations](docs/language.md#precompiled-translations)
	* [Compilation](docs/language.md#compilation)
	* [Configuration](docs/language.md#configuration)
* [Suggestions](docs/language.md#suggestions)

##  Using Layouts (docs/layout.md):

* [What is a layout?](docs/layout.md#what-is-a-layout)
* [Create a site with layout](docs/layout.md#create-a-site-with-layout)
	* [Main content](docs/layout.md#main-content)
	* [Nav bar](docs/layout.md#nav-bar)
	* [Layout](docs/layout.md#layout)
	* [Config](docs/layout.md#config)
* [Doubts](docs/layout.md#doubts)
	* [What the heck is that?](docs/layout.md#what-the-heck-is-that)
	* [Insert?](docs/layout.md#insert)
* [Wrong layout?](docs/layout.md#wrong-layout)
* [Suggestions](docs/layout.md#suggestions)

##  MagicProp (docs/magicprop.md):

* [MagicProp?](docs/magicprop.md#magicprop)
* [How does it work?](docs/magicprop.md#how-does-it-work)
* [Where can I use it?](docs/magicprop.md#where-can-i-use-it)
* [Known properties](docs/magicprop.md#known-properties)
* [My own](docs/magicprop.md#my-own)
	* [MagicPropException](docs/magicprop.md#magicpropexception)
* [Dynamic Properties](docs/magicprop.md#dynamic-properties)
* [Suggestions](docs/magicprop.md#suggestions)

##  Models (docs/models.md):

* [What is a Model in __TooBasic__?](docs/models.md#what-is-a-model-in-toobasic)
* [Example](docs/models.md#example)
* [Model](docs/models.md#model)
* [Using a Model](docs/models.md#using-a-model)
* [Suggestions](docs/models.md#suggestions)

##  Using Redirections (docs/redirections.md):

* [What is a redirection?](docs/redirections.md#what-is-a-redirection)
* [Configuration](docs/redirections.md#configuration)
* [Checking conditions](docs/redirections.md#checking-conditions)
* [Complex redirectors](docs/redirections.md#complex-redirectors)
	* [Parameters](docs/redirections.md#parameters)
	* [Layout](docs/redirections.md#layout)

##  Representations (docs/representations.md):

* [What it this?](docs/representations.md#what-it-this)
* [A table](docs/representations.md#a-table)
* [Row representation](docs/representations.md#row-representation)
	* [CP?](docs/representations.md#cp)
* [Table representation](docs/representations.md#table-representation)
* [Let's use it](docs/representations.md#lets-use-it)
* [Database](docs/representations.md#database)
* [Suggestions](docs/representations.md#suggestions)

##  Routes (docs/routes.md):

* [What are routes?](docs/routes.md#what-are-routes)
* [Before we start](docs/routes.md#before-we-start)
	* [mod_rewrite](docs/routes.md#mod_rewrite)
	* [Allow override](docs/routes.md#allow-override)
	* [Permissions](docs/routes.md#permissions)
	* [The right name](docs/routes.md#the-right-name)
* [Activating routes](docs/routes.md#activating-routes)
* [Our first route](docs/routes.md#our-first-route)
* [Route analysis](docs/routes.md#route-analysis)
	* [Parameters types](docs/routes.md#parameters-types)
	* [Let's write a few more](docs/routes.md#lets-write-a-few-more)
* [Url issues](docs/routes.md#url-issues)
	* [Final result](docs/routes.md#final-result)
* [Modules?](docs/routes.md#modules)
* [Suggestions](docs/routes.md#suggestions)

##  Services (docs/services.md):

* [Service?](docs/services.md#service)
* [Let's use an example](docs/services.md#lets-use-an-example)
* [Creating a service](docs/services.md#creating-a-service)
* [Simpler](docs/services.md#simpler)
	* [May I?](docs/services.md#may-i)
* [Interfaces](docs/services.md#interfaces)
* [CORS](docs/services.md#cors)
	* [Allowing sites](docs/services.md#allowing-sites)
	* [Methods](docs/services.md#methods)
	* [Headers](docs/services.md#headers)

##  Shell Tools and Crons (docs/shelltools.md):

* [What's a Shell Tools?](docs/shelltools.md#whats-a-shell-tools)
* [Creating a shell tool](docs/shelltools.md#creating-a-shell-tool)
	* [Let's make things interesting](docs/shelltools.md#lets-make-things-interesting)
	* [Things we didn't explain](docs/shelltools.md#things-we-didnt-explain)
	* [Recommendation](docs/shelltools.md#recommendation)
* [Cron tools](docs/shelltools.md#cron-tools)
	* [Create a cron tool](docs/shelltools.md#create-a-cron-tool)
	* [How does it work?](docs/shelltools.md#how-does-it-work)
	* [Dead flags](docs/shelltools.md#dead-flags)
* [Profiles](docs/shelltools.md#profiles)
* [Aliases](docs/shelltools.md#aliases)
	* [Configuration](docs/shelltools.md#configuration)
	* [Rules](docs/shelltools.md#rules)
* [Suggestions](docs/shelltools.md#suggestions)

##  Skins (docs/skins.md):

* [What are skins for __TooBasic__?](docs/skins.md#what-are-skins-for-toobasic)
* [How to create a skin](docs/skins.md#how-to-create-a-skin)
* [Different style](docs/skins.md#different-style)
* [How to activate a skin](docs/skins.md#how-to-activate-a-skin)
	* [URL specified](docs/skins.md#url-specified)
	* [By configuration](docs/skins.md#by-configuration)
	* [By session](docs/skins.md#by-session)
* [Debugging](docs/skins.md#debugging)
* [Multiple sites](docs/skins.md#multiple-sites)
* [Suggestions](docs/skins.md#suggestions)

##  Using Snippets (docs/snippets.md):

* [Snippets?](docs/snippets.md#snippets)
* [Pager snippet](docs/snippets.md#pager-snippet)
* [Pager snippet mananger](docs/snippets.md#pager-snippet-mananger)
* [Current problem](docs/snippets.md#current-problem)
	* [Note here](docs/snippets.md#note-here)
* [Adding pages](docs/snippets.md#adding-pages)
* [Explain it!](docs/snippets.md#explain-it)
	* [How to invoke a snippet.](docs/snippets.md#how-to-invoke-a-snippet)
	* [Separated assignments](docs/snippets.md#separated-assignments)
	* [Something good](docs/snippets.md#something-good)
* [Suggestions](docs/snippets.md#suggestions)

##  Troubleshooting (docs/troubleshooting.md):

* [What is this page?](docs/troubleshooting.md#what-is-this-page)
* [General](docs/troubleshooting.md#general)
	* [Have you installed _Smarty_?](docs/troubleshooting.md#have-you-installed-smarty)
	* [Permissions](docs/troubleshooting.md#permissions)
	* [There goes nothing](docs/troubleshooting.md#there-goes-nothing)
	* [Have you installed _Predis_?](docs/troubleshooting.md#have-you-installed-predis)
* [Database](docs/troubleshooting.md#database)
	* [SQLite & autoincrements](docs/troubleshooting.md#sqlite-&-autoincrements)
	* [SQLite & deprecated columns](docs/troubleshooting.md#sqlite-&-deprecated-columns)
* [Environment globals](docs/troubleshooting.md#environment-globals)
	* [Where is my _php.ini_ file?](docs/troubleshooting.md#where-is-my-phpini-file)
* [Smarty version](docs/troubleshooting.md#smarty-version)
* [Suggestions](docs/troubleshooting.md#suggestions)

