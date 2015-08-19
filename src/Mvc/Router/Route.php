<?php
namespace Lebran\Mvc\Router;

/**
 * This class represents every route added to the router or collection.
 *
 *                      Example
 * <code>
 *      $route = new Route(
 *          '<lang \w{2}>[-<sublang \w{2}>]/<controller>[/<action>]',
 *          [
 *              'defaults' => [
 *                  'lang' => 'en',
 *                  'controller' => 'news'
 *              ],
 *              'middlewares' => [
 *                  'auth',
 *                  'cache'
 *              ],
 *              'callbacks' => [
 *                  'controller' => function($controller){
 *                      return $controller.'Controller';
 *                   },
 *                  'action' => function($action){
 *                      return $action.'Action';
 *                   }
 *              ]
 *          ],
 *          [
 *              'post',
 *              'get'
 *          ]
 *      );
 *
 *      $router = new Router($route);
 * </code>
 *
 *      Примеры подходящих ссылок:
 *          - admin/register/login/2005
 *          - admin/other/register
 *          - admin/yoyoy
 *          - admin
 *
 * @package    Mvc
 * @subpackage Router
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Route
{
    /**
     * What could not be a part of segment.
     *
     * @var string
     */
    const REGEX_SEGMENT = '[^/.,;?\n]++';

    /**
     * What should be escaped.
     *
     * @var string
     */
    const REGEX_ESCAPE = '[.\\+*?(^\\)${}=!|]';

    /**
     * The compiled rule.
     *
     * @var string
     */
    protected $pattern;

    /**
     * The name of route.
     *
     * @var string
     */
    protected $name;

    /**
     * The parameters of defaults.
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * An array of satisfying methods.
     *
     * @var array
     */
    protected $methods = [];

    /**
     * Storage for middlewares.
     *
     * @var array
     */
    protected $middlewares = [];

    /**
     * Storage for callbacks.
     *
     * @var array
     */
    protected $callbacks = [];

    /**
     * Initialisation.
     *
     * @param string $pattern    The rule of routing.
     * @param mixed  $definition The route definition(string or array).
     * @param mixed  $methods    Satisfying method(s).
     *
     * @throws \Lebran\Mvc\Router\Exception
     */
    public function __construct($pattern, $definition = null, $methods = null)
    {
        if (is_string($definition)) {
            if (strpos($definition, '::') !== false) {
                $defaults = explode('::', $definition);
                $this->setDefaults(['controller' => $defaults[0], 'action' => $defaults[1]]);
            } else {
                throw new Exception('A string definition of route should be type of "controller::action"');
            }
        } else if (is_array($definition)) {
            foreach ($definition as $key => $value) {
                $this->{'set'.strtoupper($key)}($value);
            }
        } else if (null !== $definition) {
            throw new Exception('The route definition should be string or array.');
        }

        if (null !== $methods) {
            $this->setMethods($methods);
        }
        $this->pattern = $this->compile($pattern);
    }

    /**
     * Sets name of route for generation urls.
     *
     * @param string $name The name of route.
     *
     * @return object Route object.
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Adds satisfying methods.
     *
     * @param array $defaults An array of satisfying methods.
     *
     * @return object Route object.
     */
    public function setDefaults(array $defaults)
    {
        $this->defaults = array_merge($this->defaults, $defaults);
        return $this;
    }

    /**
     * Adds satisfying methods.
     *
     * @param array $methods An array of satisfying methods.
     *
     * @return object Route object.
     */
    public function setMethods($methods)
    {
        if (is_array($methods)) {
            $this->methods = array_merge($this->methods, array_map('strtoupper', $methods));
        } else {
            $this->methods[] = strtoupper($methods);
        }
        return $this;
    }

    /**
     * Adds middlewares.
     *
     * @param array $middlewares An array of middlewares.
     *
     * @return object Route object.
     */
    public function setMiddlewares(array $middlewares)
    {
        $this->middlewares = $middlewares;
        return $this;
    }

    /**
     * Adds callbacks for parameters.
     *
     * @param array $callbacks An array of anonymous function for parameter.
     *
     * @return object Route object.
     * @throws \Lebran\Mvc\Router\Exception
     */
    public function setCallbacks(array $callbacks)
    {
        foreach ($callbacks as $section => $callback) {
            if (is_callable($callback)) {
                $this->callbacks[$section] = $callback;
            } else {
                throw new Exception('The value of array cell should be callable');
            }
        }
        return $this;
    }

    /**
     * Compiles routing rule (turns into a regular expression).
     *
     * @param string $pattern The rule of routing.
     *
     * @return string The compiled rule.
     */
    protected function compile($pattern)
    {
        $regex      = [];
        $expression = preg_replace_callback(
            '#<(\S[^<>]+) (\S[^<>]+)>#',
            function ($matches) use (&$regex) {
                $regex[$matches[1]] = $matches[2];
                return '<'.$matches[1].'>';
            },
            $pattern
        );

        $expression = preg_replace('#'.self::REGEX_ESCAPE.'#', '\\\\$0', $expression);

        if (strpos($expression, '[') !== false) {
            $expression = str_replace(array('[', ']'), array('(?:', ')?'), $expression);
        }
        $expression = str_replace(array('<', '>'), array('(?P<', '>'.self::REGEX_SEGMENT.')'), $expression);

        if (0 !== count($regex)) {
            $search = $replace = array();
            foreach ($regex as $key => $value) {
                $search[]  = '<'.$key.'>'.self::REGEX_SEGMENT;
                $replace[] = '<'.$key.'>'.$value;
            }

            $expression = str_replace($search, $replace, $expression);
        }

        return '#^'.$expression.'$#uD';
    }

    /**
     * Gets compiled pattern.
     *
     * @return string Compiled pattern.
     */
    public function getCompiledPattern()
    {
        return $this->pattern;
    }

    /**
     * Gets default parameters.
     *
     * @return array An array of defaults.
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * Gets satisfying methods.
     *
     * @return array An array of methods.
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Gets middlewares.
     *
     * @return array An array of middlewares.
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    /**
     * Gets callbacks.
     *
     * @return array An array of callbacks.
     */
    public function getCallbacks()
    {
        return $this->callbacks;
    }

    /**
     * Gets name of route.
     *
     * @return string The name of route.
     */
    public function getName()
    {
        return $this->name;
    }
}
