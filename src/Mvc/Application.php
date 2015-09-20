<?php
namespace Lebran\Mvc;

use Lebran\Utils\Queue;
use Lebran\Di\Injectable;
use Lebran\Event\Eventable;
use Lebran\Mvc\Application\Exception;

class Application
{
    use Injectable, Eventable;

    const CONTROLLER_POSTFIX = 'Controller';

    const ACTION_POSTFIX = 'Action';

    const MODULE_CLASS_NAME = 'Module';

    protected $modules;

    protected $handler;

    protected $middlewares;

    public function __construct($di)
    {
        $this->di          = $di;
        $this->middlewares = new Queue();
    }

    public function registerModules(array $modules)
    {
        $this->modules = $modules;
    }

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

        $current = $router->getMatchedRoute();
        foreach ($current->getMiddlewares() as $priority => $value) {
            $this->middlewares->insert($value, $priority);
        }

        if ($current->getHandler()) {
            $this->handler = $current->getHandler()->bindTo($this->di, $this);
        } else {
            if (is_array($this->modules)) {
                $module  = $router->getModule();
                $segment = ucfirst($router->getController()).self::CONTROLLER_POSTFIX;
                if (array_key_exists($module, $this->modules)) {
                    if (class_exists($this->modules[$module].$segment)) {
                        $module     = $this->modules[$module];
                        $controller = $module.$segment;
                    }
                } else {
                    foreach ($this->modules as $value) {
                        if (class_exists($value.$segment)) {
                            $controller = $value.$segment;
                            $module     = $value;
                            break;
                        }
                    }
                }

                if (empty($controller)) {
                    throw new Exception('');
                }

                $module .= self::MODULE_CLASS_NAME;
                if (class_exists($module)) {
                    new $module($this->di);
                }

                $controller = new $controller($this->di);
                $action     = $router->getAction().self::ACTION_POSTFIX;
                if (method_exists($controller, $action)) {
                    $this->handler = [$controller, $action];
                } else {
                    throw new Exception('');
                }
            } else {
                throw new Exception('');
            }
        }

        return $this->handleMiddlewares();
    }

    protected function handleMiddlewares()
    {
        $this->middlewares->insert($this, -PHP_INT_MAX);
        $middlewares = $this->middlewares->toArray();

        for ($i = 1, $count = count($middlewares);$i < $count;$i++) {
            $middlewares[$i - 1]->setNext($middlewares[$i]);
        }

        return $middlewares[0]->call();
    }

    public function call()
    {
        if ($this->handler instanceof \Closure) {
            $reflection = new \ReflectionFunction($this->handler);
        } else {
            $reflection = new \ReflectionMethod(...$this->handler);
        }

        $parameters = $this->di->get('router')->getParams();
        $pass       = [];
        foreach ($reflection->getParameters() as $param) {
            if (array_key_exists($param->getName(), $parameters)) {
                $pass[] = $parameters[$param->getName()];
            } else {
                if ($param->isOptional()) {
                    $pass[] = $param->getDefaultValue();
                } else {
                    throw new Exception('Page not found', 404);
                }
            }
        }

        return $this->prepareResponse(call_user_func_array($this->handler, $pass));
    }

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
                throw new Exception('');
        }
    }

}