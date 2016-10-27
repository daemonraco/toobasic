# TooBasic: RESTful
## What it this?
At this point you probably know what RESTful is but if you are wondering you may
visit [this link](https://en.wikipedia.org/wiki/Restful).

Basically is a way to access you data in your site through some standard API
endpoints.
__TooBasic__ provides a internal mechanism for that purpose I we're going to
explain how to use it in this page.

## Introduction
__TooBasic__ provides three ways to access database
[representations](representations.md) in your site:

* __CRUD__: A way to access and modify entries.
* __Search__: A way to search for specific items.
* __Statistics__: Status information about a table's contents.

From different perspective, __TooBasic__ provides these actions to access
representations:

| URL                                       | Method | Name    |
|:------------------------------------------|:------:|:-------:|
| resource/&lt;resource-name&gt;            | GET    | index   |
| resource/&lt;resource-name&gt;            | POST   | create  |
| resource/&lt;resource-name&gt;/&lt;id&gt; | GET    | show    |
| resource/&lt;resource-name&gt;/&lt;id&gt; | PUT    | update  |
| resource/&lt;resource-name&gt;/&lt;id&gt; | DELETE | destroy |
| search/&lt;resource-name&gt;              | GET    | search  |
| stats/&lt;resource-name&gt;               | GET    | stats   |

## How to use it
Maybe the first question you would have would be _How do I access any of this
routes?_ so let's explain that.

Suppose you have a table in your site called `people` and it has its pair of
_factory_ and _representation_, and also its _RESTful_ policies are active (we'll
talk about this later). you can simple access to:

>http://www.myhost.com/mysite/rest/resource/people

If for any reason, your system does not support friendly URLs, you can still
access this endpoint like this:

>http://www.myhost.com/mysite/?rest=resource/people

For more information about friendly URLs you may visit our documentation page on
[Routes](routes.md).

## Listing items
Following our previous example, if we want to access all items in table `people`,
we may the action `index` and just call the URL mentioned before.
In that way we'll get a JSON response containing a list of objects where each
object is a table's entry.

Of course, you won't actually get all elements but only the first 10 (ten) of them
to avoid an abusive use of resources in your server.

### Limit and offset
If you want to get a different amount of entries on a `index` call, you may use
the parameter `limit` in this way:

>http://www.myhost.com/mysite/rest/resource/people?limit=15

For safety reasons, the parameter `limit` has and internal limit of one hundred,
so it doesn't matter if you use a bigger number, it won't return more than a
hundred.

If you want to access items beyond your limit, you may use the parameter `offset`,
for example:

>http://www.myhost.com/mysite/rest/resource/people?limit=15&offset=30

This URL will retrieve 15 items starting from the 31st item in the table. And yes,
the first item of a table has the offset zero.

### Expand
If your representation makes use of the expanded columns feature, you may add the
parameter `expand` and will also load sub-representations.
Note that this expansion won't be a _deep_ one to avoid performace issues.

## Read an item
If you already have a table's entry id, you may use a URL like this to access its
complete information:

>http://www.myhost.com/mysite/rest/resource/people/32

This will return a JSON response containing a single object with all entry's
information.
If your representation is using _field filters_, the information you get will be
already applied.
Also, if you add the parameter `expand`, you'll get sub-representations expanded
(this is a deep expansion).

## Update
If you need to change an item's information, you may use the action `update`
sending `PUT` request with something like this (jQuery example):
```javascript
$.ajax({
	url: '/mysite/rest/resource/people/32',
	type: 'PUT',
	dataType: 'json',
	contentType: 'application/json',
	data: JSON.stringify({
		name: 'Jane Doe',
		age: 27
	}),
	success: function (response) {
		console.log('Successfully saved', response);
	}
});
```
This call will attempt to update the information of a row with ID `32` and change
its field `name` and also field `age`.

Note that request body is sent as a JSON string and not as a form's set of values.

The response you'll get is the item as it was left on database.

## Create
Action `create` is similar to the previous one in the way it's use, but it doesn't
need an ID and it's run using `POST` as request method.
For example (jQuery example):
```javascript
$.ajax({
	url: '/mysite/rest/resource/people',
	type: 'POST',
	dataType: 'json',
	contentType: 'application/json',
	data: JSON.stringify({
		name: 'John Doe',
		married: true,
		age: 27
	}),
	success: function (response) {
		console.log('Successfully saved', response);
	}
});
```

The response you'll get is the newly created item.

## Delete
If you want to get rid of certain item, you may use its ID and call the `destroy`
action in this way (jQuery example):
```javascript
$.ajax({
	url: '/mysite/rest/resource/people/32',
	type: 'DELETE',
	dataType: 'json',
	success: function (response) {
		console.log('Successfully saved', response);
	}
});
```

The response you'll get will be a simple object like this one:
```json
{
	"status": true
}
```

Where `status` is the result of the _delete_ operation.

## Search
If you want to search every _accountant_ of age _27_ and in your table, you may
use this URL:

>http://www.myhost.com/mysite/rest/search/people/job/accountant/age/27

This call to our action `search`, will return a list of items that match those two
conditions.

## Statistics
At the moment, the action `stats` is rather simple and it only returns the amount
of items inside a table.
So, if you call:

>http://www.myhost.com/mysite/rest/stats/people

you may get:
```json
{
	"count": 127
}
```

## Error messages
Whenever an error appears, you'll get an error with an structure like this:
```json
{
	"lasterror": {
		"code": "500",
		"message": "Action 'index' is not allow on resource 'bananas'."
	},
	"errors": [
		{
			"code": "500",
			"message": "Action 'index' is not allow on resource 'bananas'."
		}
	]
}
```

## Policies
@TODO

## Suggestions
If you want or need, you may visit these documentation pages:

* [Representations](representations.md)
* [Routes](routes.md)

<!--:GBSUMMARY:Services:2:RESTful:-->
