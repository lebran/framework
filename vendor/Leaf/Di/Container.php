<?php
namespace Leaf\Di;

/**
 * Leaf\Di it's a component that implements Dependency Injection/Service Location patterns.
 * Supports string, object, array and anonymous function definition. Allows using the array syntax.
 *
 *                              Examples
 *  <code>
 *      $di = \Leaf\Di\Container();
 *
 *      // Using string definition
 *      $di->set('test', '\Leaf\App\TestController');
 *
 *      // Using object definition (singleton)
 *      $di->set('test',  new \Leaf\App\TestController('param1'));
 *
 *      // Using anonymous function definition
 *      $di->set('test',  function ($param1, $param2) {
 *          return new \Leaf\App\TestController($param1, $param2)
 *      });
 *
 *      // Array definition watching in \Leaf\Di\Service class
 *  </code>
 *
 * @package    Di
 * @version    2.1
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Container implements \ArrayAccess
{
    /**
     * Store services.
     *
     * @var array
     */
    protected $services = array();

    /**
     * Registers a service in the services container.
     *
     * @param string $name       Service name.
     * @param mixed  $definition Service definition.
     * @param bool   $shared     Shared or not.
     *
     * @return object Service object.
     */
    public function set($name, $definition, $shared = false)
    {
        return $this->services[$name] = new Service($name, $definition, $shared);
    }

    /**
     * Resolves the service based on its configuration.
     *
     * @param string $name   Service name.
     * @param array  $params Parameters for service constructor.
     *
     * @return object Resolving service instance object.
     */
    public function get($name, array $params = array())
    {
        if (isset($this->services[$name])) {
            $instance = $this->services[$name]->resolve($params, $this);
        } else {
            if (!class_exists($name)) {
                throw new Exception('Service "'.$name.'" wasn\'t found in the dependency injection container');
            }
            $reflection = new \ReflectionClass($name);
            $instance = $reflection->newInstanceArgs($params);
        }

        if ($instance instanceof InjectableInterface) {
            $instance->setDi($this);
        }

        return $instance;
    }

    /**
     * Returns a service instance
     *
     * @param string $name Service name.
     *
     * @return object Service object.
     */
    public function getService($name)
    {
        if (isset($this->services[$name])) {
            return $this->services[$name];
        } else {
            throw new Exception("Service '".$name."' wasn't found in the dependency injection container");
        }
    }

    /**
     * Removes a service in the services container.
     *
     * @param string $name Service name.
     *
     * @return void
     */
    public function remove($name)
    {
        unset($this->services[$name]);
    }

    /**
     * Check whether the container contains a service by a name.
     *
     * @param string $name Service name.
     *
     * @return bool True if exists, false - not.
     */
    public function has($name)
    {
        return isset($this->services[$name]);
    }

    /**
     * Allows to register a shared service using the array syntax.
     *
     * @param string $name       Service name.
     * @param mixed  $definition Service definition.
     *
     * @return object Service object.
     */
    public function offsetSet($name, $definition)
    {
        return $this->set($name, $definition, true);
    }

    /**
     * Allows to obtain a shared service using the array syntax
     *
     * @param string $name Service name.
     *
     * @return object Resolving service instance object.
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * Removes a service from the services container using the array syntax.
     *
     * @param string $name Service name.
     *
     * @return void
     */
    public function offsetUnset($name)
    {
        $this->remove($name);
    }

    /**
     * Check if a service is registered using the array syntax.
     *
     * @param string $name Service name.
     *
     * @return bool True if exists, false - not.
     */
    public function offsetExists($name)
    {
        return $this->has($name);
    }
}