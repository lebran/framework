<?php
namespace Lebran\Mvc;

use Lebran\Mvc\Router\Collection;
use Lebran\Di\InjectableInterface;
use Lebran\Event\EventableInterface;

/**
 * Lebran\Mvc\Router is the standard framework router. Routing is the process of
 * taking a URI endpoint (that part of the URI which comes after the base URL) and
 * decomposing it into parameters to determine which module, controller, and
 * action of that controller should receive the request.
 *
 *                                  Example
 * <code>
 *      $router = new Router();
 *
 *      $router->add("[<controller>[/<action>[/<id \d+>]]]", 'Test::index');
 *
 *      $router->handle();
 *
 *      echo $router->getController();
 * </code>
 *
 * @package    Mvc
 * @subpackage Router
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Router extends Collection implements InjectableInterface, EventableInterface
{
    /**
     * Store for di container.
     *
     * @var
     */
    protected $di;

    /**
     * Storage for internal event manager.
     *
     * @var object
     */
    protected $em;

    /**
     * Current uri.
     *
     * @var
     */
    protected $uri;

    /**
     * Current matched route.
     *
     * @var object
     */
    protected $matched = null;

    /**
     * Current module if given.
     *
     * @var string
     */
    protected $module = null;

    /**
     * Current controller.
     *
     * @var string
     */
    protected $controller;

    /**
     * Current action.
     *
     * @var string
     */
    protected $action;

    /**
     * Current parameters.
     *
     * @var array
     */
    protected $params;

    /**
     * Handles routing information received from the rewrite engine.
     *
     * @param string $uri Uniform resource identifier.
     *
     * @return boolean True if matched, then - false.
     */
    public function handle($uri = null)
    {
        $this->uri = ($uri)?$uri:$this->di['request']->getUri();

        if (is_object($this->em)) {
            $this->em->fire('router.before.checkRoutes', $this, ['uri' => $this->uri]);
        }

        $params = [];
        foreach ($this->routes as $route) {
            if (is_object($this->em)) {
                $this->em->fire('router.checkRoute', $this, ['route' => $route]);
            }

            if ($route->getMethods() && !in_array(
                    $this->di['request']->getMethod(),
                    $route->getMethods()
                )
            ) {
                continue;
            }

            if (!preg_match($route->getCompiledPattern(), $this->uri, $params)) {
                continue;
            }

            foreach ($params as $key => $value) {
                if (is_int($key)) {
                    unset($params[$key]);
                }
            }
            $params += $route->getDefaults();

            if (empty($params['controller']) or empty($params['action'])) {
                continue;
            }

            foreach ($route->getCallbacks() as $part => $callback) {
                $params[$part] = call_user_func_array($callback, [$params[$part]]);
            }

            $this->matched = $route;
            break;
        }

        if (is_object($this->em)) {
            $this->em->fire('router.after.checkRoutes', $this, ['matched' => $this->matched]);
        }

        if (!$this->matched) {
            return false;
        }

        $this->controller = $params['controller'];
        unset($params['controller']);
        $this->action = $params['action'];
        unset($params['action']);

        if (!empty($params['module'])) {
            $this->module = $params['module'];
            unset($params['module']);
        }

        $this->params = $params;
        return true;
    }

    /**
     * Gets the name of module if given.
     *
     * @return string Module name.
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Gets the name of controller.
     *
     * @return string Controller name.
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Gets the name of action.
     *
     * @return string Action name.
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Gets parameter or parameters.
     *
     * @param string $name Parameter name.
     *
     * @return mixed An array of params or param.
     */
    public function getParams($name = null)
    {
        return $name?$this->params[$name]:$this->params;
    }

    /**
     * Gets matched route.
     *
     * @return object Route object.
     */
    public function getMatchedRoute()
    {
        return $this->matched;
    }

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

    /**
     * Sets the events manager.
     *
     * @param object $em Event manager object.
     *
     * @return void
     */
    public function setEventManager($em)
    {
        $this->em = $em;
    }

    /**
     * Gets the internal event manager
     *
     * @return object Manager object.
     */
    public function getEventManager()
    {
        return $this->em;
    }
}


