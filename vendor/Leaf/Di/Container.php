<?php
namespace Leaf\Di;

/**
 * Description of Container
 *
 * @package    Di
 * @version    2.1
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Container implements \ArrayAccess
{
    /**
     *
     * @var type
     */
    protected $services;

    public function set($name, $definition, $shared = false)
    {
        return $this->services[$name] = new Service($name, $definition, $shared);
    }

    /**
     * Resolves the service based on its configuration
     */
    public function get($name, $params = null)
    {
        if (isset($this->services[$name])) {
            $instance = $this->services[$name]->resolve($params, $this);
        } else {
            if (!class_exists($name)) {
                throw new Exception('Service "'.$name.'" wasn\'t found in the dependency injection container');
            }
            $reflection = new \ReflectionClass($name);
            if (is_array($params)) {
                $instance = $reflection->newInstanceArgs($params);
            } else {
                $instance = $reflection->newInstance();
            }
        }

        if ($instance instanceof InjectionInterface) {
            $instance->setDi($this);
        }

        return $instance;
    }

    /**
     * Returns a Phalcon\Di\Service instance
     */
    public function getService($name = false)
    {
        if ($name) {
            if (isset($this->services[name])) {
                return $this->services[name];
            } else {
                throw new Exception("Service '".name."' wasn't found in the dependency injection container");
            }
        } else {
            return $this->services;
        }
    }

    /**
     * Removes a service in the services container
     */
    public function remove($name)
    {
        unset($this->services[$name]);
        return true;
    }

    /**
     * Check whether the DI contains a service by a name
     */
    public function has($name)
    {
        return isset($this->services[$name]);
    }

    /**
     * Check if a service is registered using the array syntax
     */
    public function offsetExists($name)
    {
        return $this->has($name);
    }

    /**
     * Allows to register a shared service using the array syntax
     *
     *<code>
     *    $di["request"] = new \Phalcon\Http\Request();
     *</code>
     *
     * @param string name
     * @param mixed  definition
     *
     * @return boolean
     */
    public function offsetSet($name, $definition)
    {
        return $this->set($name, $definition, true);
    }

    /**
     * Allows to obtain a shared service using the array syntax
     *
     */

    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * Removes a service from the services container using the array syntax
     */
    public function offsetUnset($name)
    {
        return $this->remove($name);
    }
}