<?php
namespace Leaf\Mvc;

use Leaf\Mvc\Router\Route;

/**
 * Description of Router
 *
 * @author Roma
 */
class Router
{
    protected $routes = array();

    public function add($name, $pattern)
    {
        $route = new Route($name, $pattern);
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
    public function handle($uri = false)
    {
        $this->uri = ($uri)? $uri : $_SERVER['REQUEST_URI'];

        foreach ($this->routes as $route) {
            $matches = array();
            $params = array();

            if (!preg_match($route->compile(), $this->uri, $matches)) {
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

            foreach ($route->getCallbacks() as $part => $callback){
                $params[$part] = call_user_func($callback, $part);
            }
     
            $this->was_matched = true;
            break;
        }

        if (!$this->was_matched and $this->not_found) {
            $params = $this->not_found;
            $this->was_matched = true;
        }

        if($this->was_matched){
            $this->controller = $params['controller'];
            unset($params['controller']);
            $this->action = $params['action'];
            unset($params['action']);

            if(!empty($params['directory'])){
                $this->directory = $params['directory'];
                unset($params['directory']);
            }

            $this->params = $params;

            $this->matched_route = $route;
        }     
    }

}


