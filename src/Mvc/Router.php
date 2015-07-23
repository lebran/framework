<?php
namespace Lebran\Mvc;

use Lebran\Mvc\Router\Route;
use Lebran\Di\InjectableInterface;

/**
 * Description of Router
 *
 * @author Roma
 */
class Router implements InjectableInterface
{
    protected $routes = [];

    protected $di;

    protected $uri;

    protected $matched = null;

    protected $not_found = null;

    protected $controller;

    protected $action;

    protected $directory = null;

    protected $params;

    /**
     * Sets the dependency injection container.
     *
     * @param object $di Container object.
     *
     * @return void
     */
    public function setDi($di)
    {
        $this->di = $di;
    }

    /**
     * Returns the dependency injection container.
     *
     * @return object Container object.
     */
    public function getDi()
    {
        return $this->di;
    }

    public function add($pattern, array $default = [], array $regex = [])
    {
        $route = new Route($pattern, $default, $regex);
        array_unshift($this->routes, $route);
        return $route;
    }


    /**
     * Проверяет соответствует ли uri правилам маршрутизации.
     *
     * @param string $uri Адрес запроса.
     *
     * @return boolean|array Если uri соответствует правилу - сегменты uri, нет - false.
     */
    public function handle($uri = null)
    {

        $this->uri = ($uri)?$uri:$this->di['request']->getUri();

        $params = [];

        foreach ($this->routes as $route) {
            if ($route->getMethods() && !in_array($this->di['request']->getMethod(), $route->getMethods())) {
                continue;
            }

            if (!preg_match($route->getCompiledPattern(), $this->uri, $matches)) {
                continue;
            }


            foreach ($matches as $key => $value) {
                if (is_int($key)) {
                    continue;
                }
                $params[$key] = $value;
            }
            $params += $route->getDefaults();

            if (empty($params['controller']) or empty($params['action'])) {
                continue;
            }

            foreach ($route->getCallbacks() as $part => $callback) {
                $params[$part] = call_user_func($callback, $params[$part]);
            }

            $this->matched = $route;
            break;
        }

        if (!$this->matched and $this->not_found) {
            $params = $this->not_found;
        }

        if ($this->matched or $this->not_found) {
            $this->controller = $params['controller'];
            unset($params['controller']);
            $this->action = $params['action'];
            unset($params['action']);

            if (!empty($params['directory'])) {
                $this->directory = $params['directory'];
                unset($params['directory']);
            }

            $this->params = $params;

            return true;
        }

        return false;
    }

}


