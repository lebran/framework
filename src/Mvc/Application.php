<?php
namespace Lebran\Mvc;

use Lebran\Utils\Queue;
use Lebran\Di\Injectable;
use Lebran\Event\Eventable;
use Lebran\Mvc\Application\Exception;
use Lebran\Mvc\Application\Middleware;

/**
 * This component encapsulates all the complex operations behind instantiating every component
 * needed and integrating it with the rest to allow the MVC pattern to operate as desired.
 *
 * @package    Mvc
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Application
{
    use Injectable, Eventable;

    /**
     * @var string Controller postfix.
     */
    const CONTROLLER_POSTFIX = 'Controller';

    /**
     * @var string Action postfix.
     */
    const ACTION_POSTFIX = 'Action';

    /**
     * @var array Storage for module handlers.
     */
    protected $modules = [];

    /**
     * @var array Storage for module namespaces.
     */
    protected $namespaces;

    /**
     * @var callable Request handler.
     */
    protected $handler;

    /**
     * @var Queue Storage for middlewares.
     */
    protected $middlewares;


    /**
     * Initialisation.
     *
     * @param object $di Dependency injection container.
     */
    public function __construct($di)
    {
        $this->di          = $di;
        $this->middlewares = new Queue();
    }

    /**
     * Register application module.
     *
     * @param string $name      The name of module.
     * @param string $namespace Module namespace.
     * @param mixed  $handler   Module handler.
     *
     * @return object Application object.
     * @throws \Lebran\Mvc\Application\Exception
     */
    public function addModule($name, $namespace, $handler = null)
    {
        $namespace = rtrim(trim($namespace), '\\').'\\';
        if ($handler) {
            if (is_string($handler) || $handler instanceof \Closure) {
                $this->modules[$name] = $handler;
            } else {
                throw new Exception('');
            }
        }
        $this->namespaces[$name] = $namespace;
        return $this;
    }

    /**
     * Adds global middlewares for all requests.
     *
     * @param Middleware $middleware An array of middlewares.
     * @param mixed      $priority   Middleware priority.
     *
     * @return object Application object.
     */
    public function addMiddleware(Middleware $middleware, $priority = 0)
    {
        $this->middlewares->insert($middleware, $priority);
    }

    /**
     * Handles a MVC request.
     *
     * @param string $uri Manually uri.
     *
     * @return object Response object.
     * @throws \Lebran\Mvc\Application\Exception
     */
    public function handle($uri = null)
    {
        if (!$this->di->has('router')) {
            throw new Exception('A dependency injection object is required to access the "router" service.');
        }

        if (is_object($this->em)) {
            $this->em->fire('application.boot', $this);
        }

        $router = $this->di->get('router');
        if (!$router->handle($uri)) {
            throw new Exception('Page not found', 404);
        }
        $params      = $router->getParams();
        $current     = $router->getMatchedRoute();
        $middlewares = $current->getMiddlewares();

        if ($current->getHandler()) {
            $this->handler = [$current->getHandler()->bindTo($this->di, $this), $params];
        } else {
            if (is_array($this->namespaces)) {
                $module  = $router->getModule();
                $segment = ucfirst($router->getController()).self::CONTROLLER_POSTFIX;
                if (array_key_exists($module, $this->namespaces)) {
                    if (class_exists($this->namespaces[$module].$segment)) {
                        $controller = $this->namespaces.$segment;
                    }
                } else {
                    foreach ($this->namespaces as $name => $value) {
                        if (class_exists($value.$segment)) {
                            $controller = $value.$segment;
                            $module     = $name;
                            break;
                        }
                    }
                }

                if (!isset($controller)) {
                    throw new Exception('Controller "'.$segment.'" not found', 404);
                }

                $action = $router->getAction().self::ACTION_POSTFIX;
                if (array_key_exists($module, $this->modules)) {
                    if (is_string($this->modules[$module])) {
                        new $this->modules[$module]($this->di);
                    } else {
                        call_user_func($this->modules[$module]->bindTo($this->di, $this));
                    }
                }

                $controller = new $controller($this->di);
                if (method_exists($controller, $action)) {
                    $reflection    = new \ReflectionMethod($controller, $action);
                    $this->handler = [$reflection->getClosure($controller), $params];
                } else {
                    throw new Exception('Method "'.$action.'" not exists', 404);
                }
            } else {
                throw new Exception('It must be at least one module: [name => namespace]');
            }
        }

        return $this->handleMiddlewares($middlewares);
    }

    /**
     * Call handler. Last handler in middlewares queue.
     *
     * @return object Response object.
     * @throws \Lebran\Mvc\Application\Exception
     */
    final public function call()
    {
        $resolved = $this->resolveParameters(...$this->handler);
        $response = call_user_func_array(array_shift($this->handler), $resolved);
        return $this->prepareResponse($response);
    }

    /**
     * Build middlewares queue and handle it.
     *
     * @param array $middlewares An array of middlewares.
     *
     * @return mixed Controller response.
     */
    protected function handleMiddlewares(array $middlewares)
    {
        $queue = clone $this->middlewares;
        foreach ($middlewares as $priority => $value) {
            $queue->insert($value, $priority);
        }

        $queue->insert($this, -PHP_INT_MAX);
        $middlewares = $queue->toArray();

        if (is_object($this->em)) {
            $this->em->fire('application.runMiddlewares', $this, ['middlewares' => $middlewares]);
        }

        for ($i = 1, $count = count($middlewares);$i < $count;$i++) {
            $middlewares[$i - 1]->setNext($middlewares[$i]);
        }

        return $middlewares[0]->call();
    }

    /**
     * Sort parameters in the correct sequence.
     *
     * @param \Closure $handler    Request handler.
     * @param array    $parameters Provided parameters.
     *
     * @return array Resolved parameters.
     * @throws \Lebran\Mvc\Application\Exception
     */
    public function resolveParameters(\Closure $handler, array $parameters)
    {
        $resolved   = [];
        $reflection = new \ReflectionFunction($handler);
        foreach ($reflection->getParameters() as $param) {
            if (array_key_exists($param->getName(), $parameters)) {
                $resolved[] = $parameters[$param->getName()];
            } else {
                if ($param->isOptional()) {
                    $resolved[] = $param->getDefaultValue();
                } else {
                    throw new Exception('Page not found', 404);
                }
            }
        }
        return $resolved;
    }

    /**
     * Prepares response for returns depending on the type.
     *
     * @param mixed $response Controller response.
     *
     * @return object Response object.
     * @throws \Lebran\Mvc\Application\Exception
     */
    protected function prepareResponse($response)
    {
        switch (strtolower(gettype($response))) {
            case 'null':
                return $this->di->get('response');
                break;
            case 'object':
                return $response;
                break;
            case 'array':
                return $this->di->get('response')->setJsonBody($response);
                break;
            case 'string':
            case 'integer':
            case 'double':
                return $this->di->get('response')->addBody($response);
                break;
            default:
                throw new Exception('Type of response not support.');
        }
    }
}