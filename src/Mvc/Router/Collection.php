<?php
namespace Lebran\Mvc\Router;

/**
 * Helper class to create a collection of routes with common attributes.
 *
 *                                 Example
 * <code>
 *      $router = new Router();
 *
 *      $router->collection([
 *          'middlewares' => [
 *              'auth'
 *          ],
 *          'defaults' => [
 *              'module' => 'admin'
 *          ]
 *      ], function($collection){
 *          $collection->add('news/add', 'News::add');
 *          $collection->add('news/update', 'News::update');
 *
 *          $collection->collection([
 *              'methods' => 'post'
 *          ], function($collection){
 *              $collection->add('news/add', 'NewsPost::add');
 *              $collection->add('news/update', 'NewsPost::update');
 *          });
 *      });
 * </code>
 *
 * @package    Mvc
 * @subpackage Router
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Collection
{
    /**
     * Storage for routes.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * Adds new route.
     *
     * @param string $pattern    The rule of routing
     * @param mixed  $definition The route definition(string or array).
     * @param mixed  $methods    Satisfying method(s).
     *
     * @return object Route object.
     */
    public function add($pattern, $definition = null, $methods = null)
    {
        $route = is_object($pattern)?$pattern:new Route($pattern, $definition, $methods);
        array_unshift($this->routes, $route);
        return $route;
    }

    /**
     * Adds collection of routes.
     *
     * @param array $definition
     * @param mixed $collection
     *
     * @return object Collection object
     * @throws \Lebran\Mvc\Router\Exception
     */
    public function collection($definition, $collection)
    {
        if (is_callable($collection)) {
            $group = new Collection();
            call_user_func_array($collection, [$group]);
        } else if (is_object($collection)) {
            $group = $collection;
        } else {
            throw new Exception('Collection should be object or callable.');
        }

        foreach ($group->getRoutes() as $route) {
            foreach ($definition as $key => $value) {
                $route->{'set'.strtoupper($key)}($value);
            }
            $this->add($route);
        }

        return $group;
    }

    /**
     * Returns a route object by its name.
     *
     * @param string $name The name of route.
     *
     * @return bool Route if exists, then - false.
     */
    public function getRouteByName($name)
    {
        foreach ($this->routes as $key => $route) {
            if ($route->getName() === $name) {
                return $route;
            }
        }
        return false;
    }


    /**
     * Gets array of routes.
     *
     * @return array An array of routes.
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Removes all the pre-defined routes.
     *
     * @return object Collection object.
     */
    public function clearRoutes()
    {
        $this->routes = [];
        return $this;
    }
}