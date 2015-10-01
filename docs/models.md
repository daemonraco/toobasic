# TooBasic: Models
## What is a Model in __TooBasic__?
A good practice is NEVER to put real logic inside a controller, that must be
inside some class in a common place where many controllers can access it.
That's a __TooBasic Model__, a class where you can store logics and use it from
any controller in your site.

## Example
For this page we are going to suppose an example where a service return the
information of a user (based on an ID) and a controller that shows information of
two users (based on their IDs).

We're going to suppose that getting a user's information is a complex process that
requires more than one line.
## Model
Following our example we are going to create a model to store logic related with
users' information and save it at __ROOTDIR/site/models/User.php__:
```php
<?php
class UserModel extends \TooBasic\Model {
    public function info($id) {
        $out = [];
        $user = $this->representation->users->item($id);
        if($user) {
            $out = $user->toArray();
            $out["city"] = $this->city($out["city"]);
            $out["school"] = $this->school($out["school"]);
        }
        return $out;
    }
    protected function city($id) {
        $city = $this->representation->cities->item($id);
        return $city ? $city->toArray() : [];
    }
    protected function init() {}
    protected function school($id) {
        $school = $this->representation->schools->item($id);
        return $school ? $school->toArray() : [];
    }
}
```
## Using a Model
Now that we have a model for our example, we need to create a service and a
controller that uses it.

* Create a controller and save it at __ROOTDIR/site/controllers/usercmp.php__:
```php
<?php
class UsercmpController extends \TooBasic\Controller {
    protected function basicRun() {
        $this->assign("user1", $this->model->user->info($this->params->get->uid1));
        $this->assign("user2", $this->model->user->info($this->params->get->uid2));
        return true;
    }
    protected function init() {
        parent::init();
        $this->_requiredParams["GET"][] = "uid1";
        $this->_requiredParams["GET"][] = "uid2";
    }
}
```
* Create a service and save it at __ROOTDIR/site/services/userinfo.php__:
```php
<?php
class UserinfoService extends \TooBasic\Service {
    protected function basicRun() {
        $this->assign("info", $this->model->user->info($this->params->get->userid));
        return true;
    }
    protected function init() {
        parent::init();
        $this->_requiredParams["GET"][] = "userid";
    }
}
```

In both cases you don't need to care about _how_ the user information is build,
you just worry about forwarding the information letting the model do its job.

## Suggestions
Here you have a few links you may want to visit:

* [Services](services.md)
* [Representations](representations.md)
* [Magic Properties](magicprop.md)
