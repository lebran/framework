<?php
namespace Leaf\Di;

/**
 * Represents individually a service in the services container.
 *
 *                           Example
 * <code>
 *      class TestController{
 *          public $test = 'string';
 *          public function __construct($param1 = '', $param2 = ''){}
 *          public function test($param){}
 *          public function __toString(){}
 *      }
 *
 *      $service = new \Leaf\Di\Service(
 *          'test',
 *          array(
 *              'class'      => '\Leaf\App\TestController',  // class1
 *              'arguments'  => array(
 *                  array(  // first argument for class1 construct
 *                      'type'      => 'class',
 *                      'name'      => '\Leaf\App\TestController',  // class2
 *                      'arguments' => array(
 *                          array('type' => 'parameter', 'value' => 'One'), // first argument for class2 construct
 *                          array('type' => 'parameter', 'value' => 'Two')  // second argument for class2 construct
 *                      )
 *                  ),
 *                  array('type' => 'parameter', 'value' => 'Three') // second argument for class1 construct
 *              ),
 *              'calls'      => array(
 *                  array(  // call method 'test' in class1
 *                      'method'    => 'test',
 *                      'arguments' => array(
 *                          array('type' => 'parameter', 'value' => 'Four') // first argument for method
 *                      )
 *                  )
 *              ),
 *              'properties' => array(
 *                  array(  // set properties 'test' in class1
 *                      'name'  => 'test',
 *                      'value' => array(   // value for properties
 *                          'value' => 'Five',
 *                          'type'  => 'parameter'
 *                      )
 *                  )
 *              )
 *          )
 *      );
 *
 *      $test = $service->resolve($params, $di);
 *  </code>
 *
 * @package    Di
 * @version    2.1
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Service
{
    /**
     * The name of service.
     *
     * @var string
     */
    protected $name;

    /**
     * The definition of the service.
     *
     * @var mixed
     */
    protected $definition;

    /**
     * Shared service or not.
     *
     * @var bool
     */
    protected $shared = false;

    /**
     * Last shared instance.
     *
     * @var object
     */
    protected $shared_instance;

    /**
     * Initialisation.
     *
     * @param string $name       Service name.
     * @param mixed  $definition Service definition.
     * @param bool   $shared     Shared or not.
     *
     * @return void
     */
    final public function __construct($name, $definition, $shared = false)
    {
        $this->name       = $name;
        $this->definition = $definition;
        $this->shared     = $shared;
    }

    /**
     * Resolves the service.
     *
     * @param array  $params Parameters for service constructor.
     * @param object $di     Container object.
     *
     * @return object Service instance object.
     */
    public function resolve(array $params, Container $di)
    {
        if ($this->shared and $this->shared_instance !== null) {
            return $this->shared_instance;
        }

        $found    = true;
        $instance = null;

        if (is_string($this->definition)) {
            if (class_exists($this->definition)) {
                $reflection = new \ReflectionClass($this->definition);
                $instance = $reflection->newInstanceArgs($params);
            } else {
                $found = false;
            }
        } else if (is_object($this->definition)) {
            if ($this->definition instanceof \Closure) {
                $instance = call_user_func_array($this->definition, $params);
            } else {
                $instance = $this->definition;
            }
        } else {
            if (is_array($this->definition)) {
                $instance = $this->build($di, $this->definition, $params);
            } else {
                $found = false;
            }
        }

        if ($found === false) {
            throw new Exception('Service "'.$this->name.'" cannot be resolved');
        }

        if ($this->shared) {
            $this->shared_instance = $instance;
        }

        return $instance;
    }

    /**
     * Check whether the service is shared or not.
     *
     * @return bool True if shared, false - not.
     */
    public function isShared()
    {
        return $this->shared;
    }

    /**
     * Sets if the service is shared or not.
     *
     * @param bool $shared Shared or not.
     *
     * @return object Service object.
     */
    public function setShared($shared = true)
    {
        $this->shared = $shared;
        return $this;
    }

    /**
     * Sets the service definition.
     *
     * @param mixed $definition New definition.
     *
     * @return object Service object.
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;
        return $this;
    }

    /**
     * Sets the service parameter, only for array definition.
     *
     * @param int   $position  Parameter position.
     * @param array $parameter New parameter.
     *
     * @return object Service object.
     */
    public function setParameter($position, array $parameter)
    {
        if (is_array($this->definition)) {
            throw new Exception('Definition must be an array to update its parameters');
        }

        if (empty($this->definition['arguments'])) {
            $this->definition['arguments'] = array($position => $parameter);
        } else {
            $this->definition['arguments'][$position] = $parameter;
        }

        return $this;
    }

    /**
     * Builds a service using a complex service definition.
     *
     * @param object $di         Container object.
     * @param array  $definition Service definition.
     * @param array  $params     Parameters for constructor.
     *
     * @return object Service object.
     */
    final protected function build(Container $di, array $definition, array $params = array())
    {
        if (empty($definition['class'])) {
            throw new Exception('Invalid service definition. Missing "class" parameter');
        }

        $reflection = new \ReflectionClass($definition['class']);
        if (!empty($params)) {
            $instance = $reflection->newInstanceArgs($params);
        } else {
            if (empty($definition['arguments'])) {
                $instance = $reflection->newInstance();
            } else {
                $instance = $reflection->newInstanceArgs($this->buildParams($di, $definition['arguments']));
            }
        }

        if (!is_object($instance)) {
            throw new Exception(
                'The definition has setter injection parameters but the constructor didn\'t return an instance'
            );
        }

        if (!empty($definition['calls'])) {
            if (!is_array($definition['calls'])) {
                throw new Exception('Setter injection parameters must be an array');
            }

            foreach ($definition['calls'] as $position => $method) {
                if (!is_array($definition['calls'])) {
                    throw new Exception('Method call must be an array on position '.$position);
                }

                if (empty($method['method'])) {
                    throw new Exception('The method name is required on position '.$position);
                }

                $method_call = array($instance, $method['method']);

                if (empty($method['arguments']) and !is_array($method['arguments'])) {
                    call_user_func($method_call);
                } else {
                    call_user_func_array($method_call, $this->buildParams($di, $method['arguments']));
                }
            }
        }

        if (!empty($definition['properties'])) {
            if (!is_array($definition['properties'])) {
                throw new Exception('Setter injection parameters must be an array');
            }

            foreach ($definition['properties'] as $position => $property) {
                if (!is_array($property)) {
                    throw new Exception('Property must be an array on position '.$position);
                }

                if (empty($property['name'])) {
                    throw new Exception('The property name is required on position '.$position);
                }

                if (empty($property['value'])) {
                    throw new Exception('The property value is required on position '.$position);
                }

                $instance->{$property['name']} = $this->buildParam($di, $position, $property['value']);
            }
        }

        return $instance;
    }


    /**
     * Resolves a constructor/call parameter.
     *
     * @param object $di       Container object.
     * @param int    $position Parameter position.
     * @param array  $argument Parameter argument (parameter, class).
     *
     * @return mixed
     */
    final protected function buildParam(Container $di, $position, array $argument)
    {
        if (empty($argument['type'])) {
            throw new Exception('Argument at position '.$position.' must have a type');
        }

        switch ($argument['type']) {
            case 'parameter':
                if (empty($argument['value'])) {
                    throw new Exception('Service "value" is required in parameter on position '.$position);
                }
                return $argument['value'];

            case 'class':
                if (empty($argument['name'])) {
                    throw new Exception('Service "name" is required in parameter on position '.$position);
                }

                if (!empty($argument['arguments'])) {
                    return $di->get($argument['name'], $this->buildParams($di, $argument['arguments']));
                }
                return $di->get($argument['name']);

            default:
                throw new Exception('Unknown service type in parameter on position '.$position);
        }
    }

    /**
     * Resolves an array of parameters.
     *
     * @param object $di        Container object.
     * @param array  $arguments Parameter arguments (parameter, class).
     *
     * @return array
     */
    final protected function buildParams(Container $di, array $arguments)
    {
        $build_arguments = array();
        foreach ($arguments as $position => $argument) {
            $build_arguments[] = $this->buildParam($di, $position, $argument);
        }
        return $build_arguments;
    }
}