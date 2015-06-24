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
    protected $classes;

    public function set($name, $class, $shared = false)
    {
        return $this->classes[$name] = new Service($name, $class, $shared);
    }

    /**
     * Resolves the service based on its configuration
     */
    public function get($name, $parameters = null)
    {
        if (isset($this->classes[$name])) {
            $instance = $this->classes[$name]->resolve($parameters, $this);
	} else {
            if (!class_exists($name)) {
		throw new Exception('Service "'.$name.' wasn\'t found in the dependency injection container');
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
        if((bool)$name){
            if(isset($this->classes[name])){
                return $this->classes[name];
            } else {
                throw new Exception("Service '" . name . "' wasn't found in the dependency injection container");
            }
        } else {
            return $this->classes;
        }
    }

    /**
     * Removes a service in the services container
     */
    public function remove($name)
    {
    	unset($this->classes[$name]);
        return true;
    }

    /**
     * Check whether the DI contains a service by a name
     */
    public function has($name)
    {
    	return isset($this->classes[$name]);
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
     *	$di["request"] = new \Phalcon\Http\Request();
     *</code>
     *
     * @param string name
     * @param mixed definition
     * @return boolean
     */
    public function offsetSet($name, $class)
    {
    	$this->set($name, $class, true);
    	return true;
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