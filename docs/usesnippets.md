# TooBasic: Using Snippets
## Snippets?
You may already know this, but if you don't a snippet is ["a small region of re-usable source code"](http://en.wikipedia.org/wiki/Snippet_%28programming%29).

In other words, it's a piece of code you write in many of your actions and you want to write once and invoke it from your actions, instead of writing it every time, which is a pain in the... a headache to maintain.

For example, if some of your actions show a section to jump between pages (let's call it pager), you may create a nice piece of HTML and paste every time you need it. Well. that is a snippet and out examples will be based on that idea.

## Pager Snippet
For our examples we're are going to create a snippet called 'pager' to show page buttons inside an action with pagination.

* Let's create a file with this content:
```html
{if $show}
<div class="Pager">
	<a href="?{$first.query}" title="{$first.id}"><span title="First" class="glyphicon glyphicon-fast-backward"></span></a>
	<a href="?{$previous.query}" title="{$previous.id}"><span title="Previous" class="glyphicon glyphicon-backward"></span></a>

	{foreach from=$pages item=page}
	{if $page.current}
	<a class="Current" href="#" title="{$page.id}" onclick="return false;">{$page.id}</a>
	{else}
	<a href="?{$page.query}" title="{$page.id}">{$page.id}</a>
	{/if}
	{/foreach}

	<a href="?{$next.query}" title="{$next.id}"><span title="Next" class="glyphicon glyphicon-forward"></span></a>
	<a href="?{$last.query}" title="{$last.id}"><span title="Last" class="glyphicon glyphicon-fast-forward"></span></a>
</div>
{/if}
```
* And save it in __ROOTDIR/site/snippets/pager.html__.

## Pager Snippet Mananger
To make things a little more interesting, let's create a model to manage this new snippet. Something like the next code and store it in __ROOTDIR/site/models/Pager.php__:
```php
<?php
class PagerModel extends \TooBasic\Model {
	const SurroundingLength = 10;
	public function snippetSets($maxPage, $currentPage) {
		$out = array("show" => true);
		if($maxPage > 1) {
			$pattern = "/^((.*)&|)page=([0-9]*)(.*)$/";
			$currentPage = $currentPage > $maxPage ? $maxPage : $currentPage;
			$query = $_SERVER["QUERY_STRING"];
			if(!$query) {
				$query = "page=1";
			} else {
				if(!preg_match($pattern, $query)) {
					$query.="&page=1";
				}
			}
			$pages = array();
			for($i = 1; $i <= $maxPage; $i++) {
				$pages[$i] = array(
					"current" => $currentPage == $i,
					"id" => $i,
					"query" => preg_replace($pattern, "\$2&page={$i}\$4", $query)
				);
			}
			$out["first"] = $pages[1];
			$out["last"] = $pages[$maxPage];
			$out["previous"] = $pages[$currentPage == 1 ? 1 : $currentPage - 1];
			$out["next"] = $pages[$currentPage == $maxPage ? $maxPage : $currentPage + 1];
			$minIndex = $currentPage - self::SurroundingLength;
			$minIndex = $minIndex > ($maxPage - (self::SurroundingLength * 2) ) ? ($maxPage - (self::SurroundingLength * 2) ) : $minIndex;
			$minIndex = $minIndex < 1 ? 1 : $minIndex;
			$out["pages"] = array_slice($pages, $minIndex - 1, (self::SurroundingLength * 2) + 1, true);
		} else {
			$out["show"] = false;
		}
		return $out;
	}
	protected function init() {}
}
```
Why a model? well this will help you avoid some settings and make your code simpler.

## Current Problem
Let's suppose your are working with an action that lists a long list of thing and you ended up with something like this:

* Controller __ROOTDIR/site/controllers/things.php__:
```php
<?php
class ThingsController extends \TooBasic\Controller {
	protected function basicRun() {
		$out = true;
		$thingsToShow = $this->model->stuff->things();
		$this->assign("things", $thingsToShow);
		return $out;
	}
}
```
* Template __ROOTDIR/site/templates/action/things.html__:
```html
<div class="Things">
	{foreach from=$things item=thing}
	<div class="Thing">
		<span class="Id">{$thing.id}</span>
		<span class="Name">{$thing.name}</span>
	</div>
	{/foreach}
</div>
```

Of course you want to add a pagination system.

... and yes, we've assumed your are using [smarty](http://www.smarty.net/).

### Note Here
The model 'stuff' doesn't really exist, it's just and example... duh :)

## Adding Pages
Let's add some code using our snippet:

* Modify your controller into something like this:
```php
class ThingsController extends \TooBasic\Controller {
	const MaxPerPage = 100;
	protected function basicRun() {
		$out = true;
		$thingsToShow = $this->model->stuff->things();
		$currentPage = isset($this->params->get->page) ? $this->params->get->page : 1;
		$currentPage = $currentPage > 0 ? $currentPage : 1;
		$totalCount = count($thingsToShow);
		$thingsToShow = array_slice($thingsToShow, ($currentPage - 1) * self::MaxPerPage, self::MaxPerPage, true);
		$this->setSnippetDataSet("pagerdata", $this->model->pager->snippetSets(ceil($totalCount / self::MaxPerPage), $currentPage));
		$this->assign("things", $thingsToShow);
		return $out;
	}
}
```
* And your template into this:
```html
<div class="Things">
	{foreach from=$things item=thing}
	<div class="Thing">
		<span class="Id">{$thing.id}</span>
		<span class="Name">{$thing.name}</span>
	</div>
	{/foreach}
</div>
{$ctrl->snippet("pager","pagerdata")}
```

All done! Now your controller sends just a chunk to its template and also set the information for the snippet 'pager'.

## Explain It!
The example may be a little vague, so let's explain a few things.

### How to Invoke a Snippet.
The simplest way to invoke a snippet is using a code like this:
```html
{$ctrl->snippet("snippetname")}
```
This will load a piece of code stored in __ROOTDIR/site/snippets/snippetname.html__ and insert it inside your template when it's rendered.
If the snippet uses variables, these will be taken from the assignments made by your controller.

### Separated Assignments
Because a snippet is a piece of code you may want to insert more than once in the same template, you'll probably get some troubles when trying to obtain different results on each insertion. Well, there is a way to point a different set of assignments on each invocation:
```html
{$ctrl->snippet("snippetname","set1")}
{$ctrl->snippet("snippetname","set2")}
{$ctrl->snippet("snippetname","set3")}
```

By default this will have no effect unless some changes are made in the controller. Here's when you need to use the method __setSnippetDataSet()__ in your controller:
```php
class ExampleController extends \TooBasic\Controller {
	protected function basicRun() {
		$this->setSnippetDataSet("set1", array("key"=>"A","value"=>1));
		$this->setSnippetDataSet("set2", array("key"=>"B","value"=>2));
		$this->setSnippetDataSet("set3", array("key"=>"C","value"=>3));
		return true;
	}
}
```
This will cause your snippet to act in a different way on each invocation. And yes, this is the technique used in our example.

### Something Good
Using the method __setSnippetDataSet()__ will help you keep your controller and template clean because non of its assignments are exported towards you template, their are used only inside the appropriate snippets.

