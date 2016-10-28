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

| URL                             | Method   |  Name     |
|:--------------------------------|:--------:|:---------:|
| `resource/<resource-name>`      | `GET`    | `index`   |
| `resource/<resource-name>`      | `POST`   | `create`  |
| `resource/<resource-name>/<id>` | `GET`    | `show`    |
| `resource/<resource-name>/<id>` | `PUT`    | `update`  |
| `resource/<resource-name>/<id>` | `DELETE` | `destroy` |
| `search/<resource-name>`        | `GET`    | `search`  |
| `stats/<resource-name>`         | `GET`    | `stats`   |

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

Have in mind that this action also uses parameters `expand`, `limit` and `offset`
in the same way than action `index`.

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
Perhaps policies is the most complicated part of this feature, but a necessary one
to avoid some security issues.
To make it easier, we'll try to explain it starting from the simples
configuration.

### Policies Types
All policy configurations are separated in 3 types:

* `active`: Anyone can use it
* `blocked`: No one can use it.
* `auth`: Only those with the right hash-code can use it.

### Initial status
By default, all your representations use the policy `blocked`, which means that no
one can access them using RESTful connections, unless you provide some
configuration for them.

### Full access to a representation
If you want to activate RESTful access to certain representation, you can edit the
file saved at `ROOTDIR/site/configs/rest.json` (if it's not there you may create
it) and write something like this:
```json
{
	"people": "active",
	"cities": "active"
}
```
This configuration will activate full access to our representation from previous
examples called `people` and also to another called `cities`.

### Partial access
If you want to give access only to list and read items you may write something
like this:
```json
{
	"people": {
		"index": "active",
		"show": "active",
		"destroy": "blocked",
		"search": "active",
		"stats": "active"
	}
}
```
This will allow access to actions `index`, `show`, `search` and `stats` and block
the rest.
We're also specifying the action `destroy` as `blocked` and though it's not an
error, it's not necessary because everything that is not specified is blocked.

### Authorization
If you want to give access only to logged in user and reject the rest you may
follow these steps.

Let's assume you have the same representation called `people` and some model that
performs all the setting required for a logged in user.

First, we configure out policies:
```json
{
	"people": "auth"
}
```
And the we add some logic to our model:
```php
<?php
class UsersManagerModel extends TooBasic\Model {
	public function loginUser(UserRepresentation $user) {
		// Setting user id on session.
		$this->params->session->current_user = $user->id();
		// Activating RESTful accesses.
		TooBasic\RestManager::Instance()->authorize();
		// Returning the authorization key for RESTful connections.
		return TooBasic\RestManager::Instance()->authorizationKey();
	}
	. . .
}
```
Function `loginUser()` uses the RESTful manager and runs its method `authorize()`
creating an authorization key (unless it's already created) and then returns the
current key so it can be later forwarded to a user's interface.
As an example, let's suppose that returned key is
`IZA5EMgtLBrv1fW0iDoPpkxO7KmQcnGCe9J4y8hS`.

Now that we have this, we can call a URL like this:

>http://www.myhost.com/mysite/rest/resource/people?authorize=IZA5EMgtLBrv1fW0iDoPpkxO7KmQcnGCe9J4y8hS

If you don't use the right hash that has been assigned to your session, you'll get
an _unauthorized_ error response.

### Authorization levels
_What if you have different types of user?_
Let's say that a simple user can only read, but and administrator can do anything.
If that's the case, you can set your policies in this way:
```json
{
	"people": {
		"index": "auth:admin:user",
		"create": "auth:admin",
		"show": "auth:admin:user",
		"update": "auth:admin",
		"destroy": "auth:admin",
		"search": "auth:admin:user",
		"stats": "auth"
	}
}
```
Now we have to slightly change our model:
```php
<?php
class UsersManagerModel extends TooBasic\Model {
	public function loginUser(UserRepresentation $user) {
		// Setting user id on session.
		$this->params->session->current_user = $user->id();

		$restManager = TooBasic\RestManager::Instance();
		// Activating RESTful default accesses.
		$restManager->authorize();
		// Activating RESTful accesses based on user role.
		$restManager->authorize($user->role); // role is either 'user' or 'admin'.
		// Returning the authorization key for RESTful connections.
		return TooBasic\RestManager::Instance()->authorizationKey();
	}
	. . .
}
```
Looking at all this we deduce that users with role `user` can access to actions
`index`, `show` and `search`.
On the other hand, role `admin` can access those and also `create`, `update` and
`destroy`.

The action `stats` is configured in a way that neither `user` nor `admin` can
access it, but our model is still using the method `authorize()` without
parameters and that gives our users the default access level, that way all our
users can access it.

## Suggestions
If you want or need, you may visit these documentation pages:

* [Representations](representations.md)
* [Routes](routes.md)
* [Models](models.md)

<!--:GBSUMMARY:Services:2:RESTful:-->
