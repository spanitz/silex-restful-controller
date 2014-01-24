<?php
namespace spanitz\Silex;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RestfulController
 *
 * Simple controller class used to dispatch HTTP Methods
 *
 * @package spanitz\Silex
 * @author  Stefan Panitz <info@stefanpanitz.de>
 */
class RestfulController
{
    /**
     * @var Silex\Application
     */
    protected $app;
    /**
     * @var Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * Instantiate Controller
     */
    public function __construct (/* Request $request, Application $app */)
    {
        $args = func_get_args();

        if (count($args) === 2 && $args[0] instanceof Request && $args[1] instanceof Application) {
            $this->request = $args[0];
            $this->app = $args[1];
        }
    }

    /**
     * Dispatches supplied HTTP method from Request class
     *
     * @param string      $controller Name of controller to be instatiated
     * @param mixed       $id         Optional model id
     * @param Request     $request    Request instance
     * @param Application $app        Application instance
     *
     * @return Symfony\Component\HttpFoundation\JsonResponse
     */
    public function dispatch ($controller, $id = null, Request $request, Application $app)
    {
        $method = strtolower($request->getMethod());
        $className = self::fromUrlSegment($controller, $app);

        if (class_exists($className)) {
            $class = new $className($request, $app);
            if (method_exists($class, $method)) {
                try {
                    $response = $class->{$method}($id);
                } catch (\Exception $e) {
                    $response = $app->json(array('error' => $e->getMessage()), 500);
                }
            }
        }

        if (isset($response)) {
            return is_array($response) ? $app->json($response) : $response;
        }

        return $app->json(array('error' => 'Resource not found.'), 404);
    }

    /**
     * Returns the class name of the current object without namespace
     *
     * @return string
     */
    public function getClassName ()
    {
        $className = explode('\\', get_class($this));

        return array_pop($className);
    }

    /**
     * Converts the submitted URL segment to a valid class name
     * including namespace
     *
     * @param string      $str URL segment
     * @param Application $app Application instance
     *
     * @return string
     */
    static public function fromUrlSegment ($str, Application $app)
    {
        $str = implode('', array_map('ucfirst', explode('-', strtolower($str))));

        return $app['restful.config']['namespace']
            . '\\'
            . $str;
    }

    /**
     * Returns the current class name as an URL segment
     *
     * @return string
     */
    public function getUrlSegment ()
    {
        $parts = preg_split(
            '/([A-Z]{1}+[a-z]+)|([A-Z])/',
            $this->getClassName(),
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );

        return strtolower(implode('-', $parts));
    }

} 