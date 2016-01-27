# TooBasic: Forms Builder Specifications
## This document
By now you've probably read our initial document on [_Forms Builder_](../forms.md)
and [_Quick Forms_](../qforms.md) so you should have a fair idea of what we're
talking about.
Following that idea, we're going to use this document to explain in more detail
the JSON specification file of _Forms Builder_.

## Basic structure
In order to be considered valid, all forms specification requires a basic that may
look like this:
```json
{
	"form": {
		"fields": {}
	}
}
```
And even though it's not shown by this example, there should be at least one field
defined, otherwise it wouldn't be much of a form, right? :)

## From properties
Each form specification may have these properties:

* `name`: A virtual form name that is used when generating field and buttons IDs
amount other default values.
* `type`: Name of the builder to use when generating the HTML code. Available
types are:
	* `basic`
	* `table`
	* `bootstrap`
* `action`: URL where this form should be submitted.
	* By default it is `#`.
* `method`: Mechanism through which this form should be submitted.
	* By default it is `get`.
* `attrs`: List of attributes to be add inside the HTML tag `<form>`.
* `modes`: List of modes associated with their specific configurations.
* `fields`<sup>(required)</sup>: List of fields to be shown.
* `buttons`: List of buttons to be shown unless certain mode overrides them with
it's own list of buttons.

### Default type
If a form specification does not provide a type name, it will use the default type
configured by the __TooBasic__ parameter `$Defaults[GC_DEFAULTS_FORMS_TYPE]`.

### Attributes
Each form specification may set a list of required attributes to be built in the
HTML tag `<form>`.
For example, given something like this:
```json
{
	"form": {
		"attrs": {
			"role": "form",
			"ng-non-bindable": true
		}
		"fields": {}
	}
}
```
Your form header may look like this:
```html
<form action="#" method="get" role="form" ng-non-bindable>
```
Something to have in mind is that attributes `action`, `id` and `method` can be
defined but they'll be ignored when generating the HTML tag, this is because
they're already managed by other properties.

## Fields
Each field can be defined in to ways.
The easy one:
```json
{
	"form": {
		"fields": {
			"name": "input"
		}
	}
}
```
And the complex one:
```json
{
	"form": {
		"fields": {
			"name": {
				"type": "input"
			}
		}
	}
}
```
Form now on, we're going to focus on the complex way and try to explain all that
you can do with it.

### Field type
_Forms Builder_ supports an specific list of field types that are listed bellow:

* `input`: A HTML tag `<input/>` of type `text`.
* `password`: A HTML tag `<input/>` of type `password`.
* `hidden`: A HTML tag `<input/>` of type `hidden`.
* `text`: A HTML tag `<textarea></textarea>`.
* `enum`: A HTML tag `<select></select>`.
	* This type requires other parameters where are going to explain later.

__Note__: This is a required parameter.

### Field label
By default, all fields use its name along with the prefix `label_formcontrol_` to
to generate a translation key and use it when displaying a field's label.
Nonetheless, these label keys can be changed by using the the field property
`label` as shown in this example:
```json
{
	"form": {
		"fields": {
			"name": {
				"type": "input"
				"label": "my_label_key_for_name"
			}
		}
	}
}
```

### Field attributes
In the same way a form can define attributes, each field can do the same having
the same result, with the simple exception that the only ignore attribute will be
`id`.

### Excluded modes
Each field can define the property `excludedModes` with a list of mode names in
which a field should not be shown.
For example, something like this will exclude the field `id` from modes `create`
and `view`:
```json
{
	"form": {
		"fields": {
			"id": {
				"type": "hidden"
				"excludedModes": ["create", "view"]
			}
		}
	}
}
```

### Default values
When your form is being shown and it's not using a specific item to fill its
fields, it will always fill then with whatever the browser chooses to, but you can
set a specific value to show instead by setting the property `value`.

### Enumerative type
When a field is of type `enum`, there are a few more fields that take importance
and this will be the list of them:

* `values` <sup>(required)</sup>: a list of all possible values.
* `emptyOption` <sup>(optional)</sup>: configuration for the first element. It has
this sub-parameters:
	* `value` <sup>(required)</sup>
	* `label` <sup>(optional)</sup>

For example, let's suppose you have a configuration like this one:
```json
{
	"form": {
		"fields": {
			"status": {
				"type": "enum",
				"values": [
					"AVAILABLE",
					"OCCUPIED",
					"REPAIRING"
				],
				"value": "",
				"emptyOption": {
					"label": "select_option_NOOPTION",
					"value": ""
				},
				"label": "table_column_status",
				"attrs": {
					"class": "input-sm"
				}
			}
		}
	}
}
```
This will build something like this (for mode `create` for example and form type
`basic`):

```html
<label for="my_form_status">@table_column_status</label>
<select id="my_form_status" name="status" class="input-sm">
	<option value="" selected="selected">@select_option_NOOPTION</option>
	<option value="AVAILABLE">@select_option_AVAILABLE</option>
	<option value="OCCUPIED">@select_option_OCCUPIED</option>
	<option value="REPAIRING">@select_option_REPAIRING</option>
</select>
```

Now let's explain it a little:

* The first option is generated by `emptyOption` and it always goes at the
beginning.
* The attribute `selected="selected"` is generated by `value` which defines a
default value.
* The other three options are generated by `values` and they will always required
a translation key given by its value and the prefix `select_option_`.

## Buttons
Even though it's not required, a form can define a list of buttons to be displayed
at the bottom of it.
For example, let's suppose we want to show a button to submit a form, that would
mean something like this:
```json
{
	"form": {
		"buttons": {
			"send": "submit"
		}
	}
}
```
Or in it's complex way:
```json
{
	"form": {
		"buttons": {
			"send": {
				"type": "submit"
			}
		}
	}
}
```
Again we are going to use this last way and explain what you can do.

### Button types
Each button may have one of these types:

* `submit`: To send the form.
* `reset`: To restore default values.
* `button`: A simple button without any HTML action.

### Button label
By default, each button uses a translation key given by its name and the prefix
`btn_` to generate the text it shows, but this can be changed by setting the
property `label`.

### Button attributes
In the same way a field does, any button can define a property called `attrs`
setting a list of HTML attributes to be appended in the HTML tag `<button>`.

## Modes
As we explained in other pages, _modes_ is the mechanism used by __TooBasic__'s
_Forms Builder_ to show different aspects of the same form, but the difference
between those aspects depends on some _core behaviors_ and configuration.

Before we go into configuration we should remember that _core behavior_ means:

* Mode `view` shows all fields as read-only.
* Mode `delete` also shows all fields as read-only.

### Mode specifics
When a mode requires some specific configuration, it must be set inside a section
as it's done in this example:
```json
{
	"form": {
		"modes": {
			"mymode": {
				"action": "?service=mysevice&q=test",
				"method": "post"
			}
		}
	}
}
```
Here we set configure a specific _post_ URL for our form when it's being shown for
mode `mymode`.
There are no restriction about mode names, but these would be the standard list:

* `create`
* `edit`
* `remove`
* `view`

### Basic mode properties
Each mode can override some of the basic properties of a form and these will be:

* `action`
* `method`

The rest of form properties have their own policies.

### Mode attributes
A mode specification can provide a property called `attrs` defining attributes for
the HTML tag `<form>`.
All of these won't override the forms attribute, they'll get appended instead,
unless they share the same attribute name in which case the one defined by current
mode will survive.

### Mode buttons
Each mode can define a list of buttons following the same rules a form does, but
in this case you should have in mind that the list of buttons inside a mode,
completely overrides default buttons defined by the form.

## My own type
If for any reason one of __TooBasic__'s default types of builders does not builds
the form you would want, you can always write your own class starting with
something like this:
```php
<?php
class MyType extends \TooBasic\Forms\FormType {
	public function buildFor($item, $mode, $flags) {
		$out = '';

		// Some code to generate an HTML form.

		return $out;
	}
}

global $Defaults;
$Defaults[GC_DEFAULTS_FORMS_TYPES]['mytype'] = '\MyType';
```
By inheriting `\TooBasic\Forms\FormType` you make sure of having all the required
methods to provide a proper _Forms Builder_ type.
Of course, this means you need to implement the method `buildFor()` and do
something to build a _string_ with you HTML code.

Also, the last line tell __TooBasic__ to consider `mytype` as a valid type and how
to solve it when it's used.

## Suggestions
If you want or need it, you may visit these documentation pages:

* [Forms Basics](../forms.md)
* [Quick Forms](../qforms.md)
