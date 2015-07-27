<?php
namespace Lebran\Mvc\Router;

/**
 * Маршрутизатор. Обрабатывает переданные правила. Отправляет сегменты uri.
 *
 *                      ПРИМЕР
 *      'admin' => array(
 *              'rout' => 'admin/(<controller>(/<action>(/<id>)))',
 *              'default' => array(
 *                      'directory' => 'admin',
 *                      'controller' => 'register',
 *                      'action' => 'logout'
 *              ),
 *              'regex' => array(
 *                      'action' = 'login|logout|register',
 *                      'id' => '\d+'
 *              )
 *      )
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
     * What could be a part (segment).
     *
     * @var string
     */
    const REGEX_SEGMENT = '[^/.,;?\n]++';

    /**
     * What should be escaped.
     *
     * @var string
     */
    const REGEX_ESCAPE = '[.\\+*?[^\\]${}=!|]';

    /**
     * The compiled rule.
     *
     * @var string
     */
    public $pattern;

    /**
     * The parameters of defaults.
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * Regular expression for parameters.
     *
     * @var array
     */
    protected $regex;

    /**
     * An array of satisfying methods.
     *
     * @var array
     */
    protected $methods =  null;

    /**
     * Storage for middlewares.
     *
     * @var array
     */
    protected $middlewares = null;

    /**
     * Storage for callbacks.
     *
     * @var array
     */
    protected $callbacks = [];

    /**
     * Initialisation.
     *
     * @param mixed $pattern  Routing rule or array of route.
     * @param array $defaults Default parameters.
     * @param array $regex    Regular expression for parameters.
     *
     * @throws \Lebran\Mvc\Router\Exception
     */
    public function __construct($pattern, array $defaults = [], array $regex = [])
    {
        if (is_array($pattern)) {
            if (empty($pattern['pattern'])) {
                throw new Exception('Route must contain pattern.');
            }
            $this->pattern = $this->compile($pattern['pattern']);
            unset($pattern['pattern']);
            foreach ($pattern as $key => $value) {
                $this->{$key}($value);
            }
        } else {
            $this->pattern = $this->compile($pattern);
        }
        $this->defaults = $defaults;
        $this->regex    = $regex;
    }

    /**
     * Adds satisfying methods.
     *
     * @param array $methods An array of satisfying methods.
     *
     * @return object Route object.
     */
    public function methods(array $methods)
    {
        $this->methods = $methods;
        return $this;
    }

    /**
     * Adds middlewares.
     *
     * @param array $middlewares An array of middlewares.
     *
     * @return object Route object.
     */
    public function middlewares(array $middlewares)
    {
        $this->middlewares = $middlewares;
        return $this;
    }

    /**
     * Adds callback for parameter.
     *
     * @param string   $part     Parameter name.
     * @param \Closure $callback Anonymous function for parameter.
     *
     * @return object Route object.
     */
    public function callback($part, \Closure $callback)
    {
        $this->callbacks[$part] = $callback;
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
        $expression = preg_replace('#'.self::REGEX_ESCAPE.'#', '\\\\$0', $pattern);

        if (strpos($expression, '(') !== false) {
            $expression = str_replace(array('(', ')'), array('(?:', ')?'), $expression);
        }

        $expression = str_replace(array('<', '>'), array('(?P<', '>'.self::REGEX_SEGMENT.')'), $expression);

        if (!empty($this->regex)) {
            $search = $replace = array();
            foreach ($this->regex as $key => $value) {
                $search[]  = "<$key>".self::REGEX_SEGMENT;
                $replace[] = "<$key>$value";
            }

            $expression = str_replace($search, $replace, $expression);
        }

        return '#^'.$expression.'$#uD';
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
     * Gets compiled pattern.
     *
     * @return string Compiled pattern.
     */
    public function getCompiledPattern()
    {
        return $this->pattern;
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
}
