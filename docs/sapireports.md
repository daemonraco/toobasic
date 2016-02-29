# TooBasic: Simple API Reports
## What is SApiReports?
_Simple API Reports_ is somehow an extension for _Simple API Reader_ and it allows
you to display some API information as a report, in other words, as a table inside
a view.

Let's revisit an example we gave [before](sapireader.md) and let's say you want to
have a page where every one can see a table with the most popular pages in _One
Piece Wikia_.
For that we're going to need two things, an _Simple API Reader_ configuration and
another for our report, and that's what we are going to talk about in the next
sections.

## API configuration
The first thing we need is to create a configuration to access most popular
articles in _One Piece Wikia_ and for that we're going to write the next
configuration at `ROOTDIR/site/sapis/onepiece.json`:
```json
{
	"name": "One Piece Wikia",
	"description": "One Piece Wikia Page (Non Official)",
	"url": "http://onepiece.wikia.com/api/v1",
	"type": "json",
	"services": {
		"popular": {
			"method": "GET",
			"uri": "/Articles/Popular?expand=1&limit=:limit&offset=:offset",
			"params": [":limit", ":offset"],
			"defaults": {
				":offset": 0,
				":limit": 50
			}
		}
	}
}
```

## Report configuration
The second thing is to write a configuration file for our report based on the API.
For that we're going to write the next configuration at
`ROOTDIR/site/sapis/reports/onepiece_popular.json`:
```json
{
	"api": "onepiece",
	"service": "popular",
	"listPath": "items",
	"params": [
		10
	],
	"columns": [
		{
			"title": "ID",
			"path": "url",
			"type": "link",
			"label_field": "id",
			"link": {
				"prefix": "http://onepiece.wikia.com"
			}
		}, {
			"title": "Thumb",
			"path": "thumbnail",
			"type": "image",
			"attrs": {
				"style": {
					"height": "auto",
					"width": "64px"
				},
				"class": "img-thumbnail"
			}
		}, {
			"title": "Title",
			"path": "title"
		}, {
			"title": "Type",
			"path": "type",
			"type": "code"
		}, {
			"title": "Brief",
			"path": "abstract",
			"type": "text"
		}, {
			"title": "Last Reviewer",
			"path": "revision/user",
			"label_field": "revision/user",
			"label": "Reviewer Page",
			"type": "link",
			"link": {
				"prefix": "http://onepiece.wikia.com/wiki/User:"
			},
			"attrs": {
				"target": "_blank"
			}
		}
	],
	"exceptions":[
		{
			"path": "id",
			"exclude": [1882]
		}
	]
}
```
Don't worry if you don't understand it because where are going to explain it in
detail in further sections.
In the mean time, this is how it may look like 

![One Piece Wiki: Popular Articles](images/sapis/sapireports.png)

## Mandatory fields
Each specification requires some fields to be present in order to render a report.
These fields are:

* `api`: The name of the _Simple API Reader_ configuration. In our example this
will be `onepiece`.
* `service`: The name of the service to call defined by the _Simple API Reader_
configuration file.
* `columns`: The list of columns to show in the report along with their
configuration.

## Optional fields
Beside the required parameters, there's a list of fields that can be given to
alter the way a report render.

### Field _listPath_
By default _Simple API Reports_ expect each API response to be a list (an array)
of items to show, but most of the time, the actual list is the value of some
property deep inside the response object.
In our example, the information will be returned like this:
```json
{
	"items": [
		. . .
	]
}
```
This is the reason why our configuration has the line `"listPath": "items"`.

If your API is more complex, you can specify a full path writing something like
`"listPath": "complex/property/path"` and __TooBasic__ will look for the list at
that position.

__Note__: Almost every time (unless specified) object paths can be noted in this
way.

### Field _params_
If the API service is expecting some parameters, we can provide them as a list
using this field.
In our example, we are setting the first parameter to be `10` with will be change
the limit of items returned.

### Field _type_
This field specifies how the report is rendered, and the possibilities are:

* `basic`: A simple table without styles.
* `Bootstrap`: A table using Twitter Bootstrap styles.

By default it's assumed to be `basic` but it can be change either by defining the
field or specifying it when invoked from a view.

### Field _name_
This field is used for some internal purposes and also to set the attribute `id`
in the report's table.
By default, this field takes the configuration file's name unless it's specified.

### Field _attrs_
This parameter allows you specify attributes HTML attributes to inject in the tag
`<table>` when the report is being generated.

For example, let's say you want your table to have a fixed `width` of `80%`, an
arbitrary CSS class called `MyCSSClass`, some attribute required for internal
use called `data-someattr` with the value `report-table` and also force the `id`
to be `MyID`.
With all of this we can add this at the end of our specification:
```json
	. . .
	"attrs": {
		"style": {
			"width": "80%"
		},
		"class": "MyCSSClass",
		"data-someattr": "report-table",
		"id": "MyID"
	}
}
```
This will end-up generating something like this:
```html
<table id="onepiece_popular" style="width:80%;" data-someattr="report-table" class="table table-striped MyCSSClass">
```

_What happend here?_

* The attribute `style` was build using a complex mechanism that allow
sub-properties.
	* This behavior can be applied to any attribute.
* The attribute `data-someattr` was build using a simpler mechanism and inserted
with the specified value.
* The attribute class, was merge with some internal values, this is a specific
behavior for this attribute.
* Even though we specified an ID, it was ignored because this attribute is
generated in a different way.

## Column specifications
@TODO

## Exclusions
@TODO

## Show the report
@TODO

## Suggestions
If you want or need it, you may visit these documentation pages:

* [Simple API Reader](sapireader.md)

<!--:GBSUMMARY:Tools:2:Simple API Reports:-->
