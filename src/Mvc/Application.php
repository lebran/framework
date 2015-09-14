<?php
namespace Lebran\Mvc;

use Lebran\Di\Injectable;
use Lebran\Event\Eventable;
use Lebran\Mvc\Application\Exception;

class Application
{
    use Injectable, Eventable;

    protected $modules;

    protected $handler;

    protected $middlewares = [];

    public function __construct($di)
    {
        $this->di = $di;
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
            throw new Exception('Not found', 404);
        }

        $current     = $router->getMatchedRoute();
        $middlewares = array_merge($this->middlewares, $current->getMiddlewares());

        if ($current->getHandler()) {
            $this->handler = $current->getHandler()->bindTo($this->di, $this);

        } else {
            if (is_array($this->modules)) {

                $module = $router->getModule();
                if ($module && array_key_exists($module, $this->modules)) {
                    if (class_exists($this->modules[$module].'\Module')) {
                    }
                    //  $module =
                }
            }
        }

        $middlewares[] = $this;

        for ($i = 1, $count = count($middlewares);$i < $count;$i++) {
            $middlewares[$i - 1]->setNext($middlewares[$i]);
        }

        return $middlewares[0]->call();
    }

    public function call()
    {
        //$response = $this->dispatch();
        $response = call_user_func($this->handler);

        if (null === $response) {
            return $this->di->get('response');
        } else if (is_object($response)) {
            return $response;
        } else if (is_string($response)) {
            return $this->di->get('response')->addBody($response);
        } else if (is_array($response)) {
            return $this->di->get('response')->setJsonBody($response);
        } else {
            throw new Exception('');
        }
    }
}