<?php
namespace spanitz\Silex\Provider;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class RestfulControllerProvider implements ControllerProviderInterface
{
    protected $config = array(
        'namespace' => 'Api\Controller'
    );

    public function __construct(array $config = array())
    {
        $this->config = array_merge($this->config, $config);
    }

    public function connect(Application $app)
    {
        $app['restful.config'] = $this->config;

        $controllers = $app['controllers_factory'];

        $middleware = function (Request $request) {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                $request->request->replace(is_array($data) ? $data : array());
            }
        };

        $controllers
            ->match('/{controller}/{id}', 'spanitz\Silex\RestfulController::dispatch')
            ->before($middleware)
            ->bind("model");

        $controllers
            ->match('/{controller}', 'spanitz\Silex\RestfulController::dispatch')
            ->before($middleware)
            ->bind("collection");

        return $controllers;
    }
}