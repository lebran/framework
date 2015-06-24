<?php
namespace Leaf\Di;

/**
 * Description of Service
 *
 * @package    Di
 * @version    2.1
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Service
{
    protected $name;

    protected $class;

    protected $shared = false;

    protected $resolved = false;

    protected $shared_instance;

    /**
     * Phalcon\Di\Service
     *
     * @param string name
     * @param mixed definition
     * @param boolean shared
     */
    public final function __construct($name, $class, $shared = false)
    {
        $this->name = $name;
        $this->class = $class;
        $this->shared = $shared;
    }

    /**
     * Resolves the service
     *
     * @param array parameters
     * @param Phalcon\DiInterface dependencyInjector
     * @return mixed
     */
    public function resolve($parameters = null/*, $di = null*/)
    {
        if ($this->shared and $this->shared_instance !== null) {
            return $this->shared_instance;
	}

	$found = true;
	$instance = null;

	if (is_string($this->class)) {
            if (class_exists($this->class)) {
		$reflection = new \ReflectionClass($this->class);
                if (is_array($parameters)) {
                    $instance = $reflection->newInstanceArgs($parameters);
                } else {
                    $instance = $reflection->newInstance();
                }
            } else {
                $found = false;
            }
        } else if(is_object($this->class)) {
            if ($this->class instanceof \Closure) {
                if (is_array($parameters)) {
                    $instance = call_user_func_array($this->class, $parameters);
                } else {
                    $instance = call_user_func($this->class);
                }
            } else {
                $instance = $this->class;
            }
        } else {
            if(is_array($this->class)) {
                //builder = new Builder(),
		//instance = builder->build(dependencyInjector, definition, parameters);
            } else {
                $found = false;
            }
	}
	
	if ($found === false)  {
            throw new Exception('Service "'.$this->name.'" cannot be resolved');
	}

        if ($this->shared) {
            $this->shared_instance = $instance;
        }

        $this->resolved = true;

	return $instance;
    }

}