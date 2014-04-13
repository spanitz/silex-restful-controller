<?php
namespace spanitz\Silex;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
     * Dispatches supplied HTTP method from Request instance
     *
     * @param string      $controller Name of controller to be instatiated
     * @param mixed       $id         Optional model id
     * @param Request     $request    Request instance
     * @param Application $app        Application instance
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function dispatch ($controller, $id = null, Request $request, Application $app)
    {
        $method = strtolower($request->getMethod());
        $className = $this->fromUrlSegment($controller, $app['restful.config']['namespace']);

        if (class_exists($className)) {
            $class = new $className($request, $app);
            if (method_exists($class, $method)) {
                try {
                    $data = $class->{$method}($id);
                } catch (\HttpException $exception) {
                    return $this->error($exception);
                }
            }
        }

        if (isset($data)) {
            return $this->success($data, $method, $app['restful.config']['status-codes']);
        }

        return $this->error();
    }

    /**
     * Returns an instance of Symfony\Component\HttpFoundation\Response which indicates a successful request
     * 
     * @param  mixed  $data        The payload to be returned
     * @param  string $method      HTTP method
     * @param  array  $statusCodes Associative array of 2xx status codes as values, and HTTP methods as keys
     * 
     * @return Symfony\Component\HttpFoundation\Response
     */
    protected function success ($data, $method, array $statusCodes)
    {
        if (array_key_exists($method, $statusCodes)) {
            $statusCode = $statusCodes[$method];
        } else {
            $statusCode = 200;
        }

        if (is_array($data)) {
            return new JsonResponse($data, $statusCode);
        }

        return new Response($data, $statusCode, array('Content-type' => 'application/json'));
    }

    /**
     * Returns an instance of Symfony\Component\HttpFoundation\Response which indicates an invalid request
     * 
     * @param  mixed $data Optional instance of HttpException
     * 
     * @return Symfony\Component\HttpFoundation\Response
     */
    protected function error (\HttpException $data = null)
    {
        if (is_null($data)) {
            return new JsonResponse(array('error' => 'Resource not found.'), 404);
        }
        
        return new JsonResponse(array('error' => $data->getMessage()), $data->getStatusCode());
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
     * @param string $str       URL segment
     * @param string $namespace Namespace
     *
     * @return string
     */
    public function fromUrlSegment ($str, $namespace)
    {
        $str = implode('', array_map('ucfirst', explode('-', strtolower($str))));

        return $namespace
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