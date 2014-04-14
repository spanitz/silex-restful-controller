Restful Controller for Silex
============================

The RestfulControllerProvider provides an alternative way of implementing class-based route controllers.

Getting started
---------------

Just register the `RestfulControllerProvider` for a specific route:

```php
<?php

use spanitz\Silex\Provider\RestfulControllerProvider;

$app = new Silex\Application();
$app->mount('/api', new RestfulControllerProvider());
```

Usage
-----

Let's take the well-known Todo example:

```php
<?php
namespace Api\Controller;

use spanitz\Silex\RestfulController;

class Todo extends RestfulController
{
    public function get ($id = null)
    {
        $data = array();
        // fetch multiple todos, or a single one by its $id...

        return $data;
    }

    public function put ($id = null)
    {
        // update todo...
    }

    public function post ()
    {
        // create todo...
    }

    public function delete ($id = null)
    {
        // delete todo...
    }
}
```

The implementation above exposes the following routes:

* `GET /api/todo/<id>`
* `PUT /api/todo/<id>`
* `POST /api/todo/`
* `DELETE /api/todo/<id>`

As you see, the implemented methods corresponds to HTTP methods. If your controller extends from RestfulController, you're able to access the Silex Application instance in your methods with `$this->app`; and the corresponding Request with `$this->request`.

Your method can either return an array, which is returned as `JsonResponse`; or any kind of `Symfony\Component\HttpFoundation\Response` instance.

Configuration
-------------

If the default namespace `Api\Controller` doesn't fit your needs, just overwrite the configuration in the constructor:

 ```php
 $app->mount('/api', new RestfulControllerProvider(array(
    'namespace' => 'My\Controller\Namespace'
 )));
 ```

Your derived class of RestfulController also takes care of the correct response codes. If you want to overwrite this behaviour, the following configuration can be set:

 ```php
 $app->mount('/api', new RestfulControllerProvider(array(
    'status-codes' => array(
        'post' => 200
        'put' => 200
    )
 )));
 ```

Installation
------------

Simply install via composer. Add the following lines to your composer.json:

```json
{
    "require": {
        "spanitz/silex-restful-controller": "1.0.*@dev"
    },

    "autoload": {
        "psr-4": {"Api": "src/"}
    }
}
```