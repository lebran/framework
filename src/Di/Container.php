<?php
namespace Lebran\Di;

use Lebran\Event\EventableInterface;

/**
 * Lebran\Di it's a component that implements Dependency Injection/Service Location patterns.
 * Supports string, object, array and anonymous function definition. Allows using the array syntax.
 *
 *                              Examples
 *  <code>
 *      $di = \Lebran\Di\Container();
 *
 *      // Using string definition
 *      $di->set('test', '\Lebran\App\TestController');
 *
 *      // Using object definition (singleton)
 *      $di->set('test',  new \Lebran\App\TestController('param1'));
 *
 *      // Using anonymous function definition
 *      $di->set('test',  function ($param1, $param2) {
 *          return new \Lebran\App\TestController($param1, $param2)
 *      });
 *
 *      // Array definition watching in \Lebran\Di\Service class
 *  </code>
 *
 * @package    Di
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Container implements \ArrayAccess, EventableInterface
{
    /**
     * Store services.
     *
     * @var array
     */
    protected $services = [];

    /**
     * Storage for internal event manager.
     *
     * @var object
     */
    protected $em;

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
     * @throws \Lebran\Di\Exception
     */
    public function get($name, array $params = [])
    {
        if (is_object($this->em)) {
            $this->em->fire('di.before.serviceResolve', $this, ['name' => $name, 'params' => $params]);
        }

        if (array_key_exists($name, $this->services)) {
            $instance = $this->services[$name]->resolve($params, $this);
        } else {
            if (!class_exists($name)) {
                throw new Exception('Service "'.$name.'" wasn\'t found in the dependency injection container');
            }
            $reflection = new \ReflectionClass($name);
            $instance   = $reflection->newInstanceArgs($params);
        }

        if ($instance instanceof InjectableInterface) {
            $instance->setDi($this);
        }

        if (is_object($this->em)) {
            $this->em->fire(
                'di.after.serviceResolve',
                $this,
                ['name' => $name, 'params' => $params, 'instance' => $instance]
            );
        }

        return $instance;
    }

    /**
     * Returns a service instance
     *
     * @param string $name Service name.
     *
     * @return object Service object.
     * @throws \Lebran\Di\Exception
     */
    public function getService($name)
    {
        if (array_key_exists($name, $this->services)) {
            return $this->services[$name];
        } else {
            throw new Exception('Service "'.$name.'" wasn\'t found in the dependency injection container');
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
        return array_key_exists($name, $this->services);
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
     * @throws \Lebran\Di\Exception
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