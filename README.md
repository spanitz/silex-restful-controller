Restful Controller for Silex
============================

Usage
-----

If you need a restful Api, just register the `RestfulControllerProvider` for a specific route:

```php
<?php

use spanitz\Silex\Provider\RestfulControllerProvider;

$app = new Silex\Application();
$app->mount('/api', new RestfulControllerProvider());
```

Then you're able to implement your Controllers:

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

    public function put ()
    {
        // create todo...
    }

    public function post ($id = null)
    {
        // update todo...
    }

    public function delete ($id = null)
    {
        // delete todo...
    }
}
```

The example implementation above exposes the following routes:

* `GET /api/todo`
* `GET /api/todo/<id>`
* `PUT /api/todo`
* `POST /api/todo/<id>`
* `DELETE /api/todo/<id>`

As you see, the implemented methods corresponds to HTTP methods. By extending RestfulController you're able to access the Silex Application instance in your methods by simply call `$this->app`; and the corresponding Request by `$this->request`.

Your method can either return an array, which is returned as `JsonResponse`; or any kind of `Symfony\Component\HttpFoundation\Response` instance.

If the default namespace `Api\Controller` doesn't fit your needs for your own Controller classes. Just overwrite the configuration in the constructor:

 ```php
 $app->mount('/api', new RestfulControllerProvider(array(
    'namespace' => 'My\Controller\Namespace'
 )));
 ```
