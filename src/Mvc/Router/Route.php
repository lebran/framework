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
 * @package    Core
 * @subpackage Http
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Route
{

    /**
     * Что может быть частью (сегмента).
     *
     * @var string
     */
    const REGEX_SEGMENT = '[^/.,;?\n]++';

    /**
     * Что должно быть экранировано.
     *
     * @var string
     */
    const REGEX_ESCAPE = '[.\\+*?[^\\]${}=!|]';

    /**
     * Хранилище правил маршрутизации.
     *
     * @var array
     */
    public $pattern;

    /**
     * Хранилище правил маршрутизации.
     *
     * @var array
     */
    protected $defaults;

    /**
     * Хранилище правил маршрутизации.
     *
     * @var array
     */
    protected $regex = null;

    protected $methods = [];

    /**
     * Хранилище правил маршрутизации.
     *
     * @var array
     */
    protected $middlewares = null;

    /**
     * Хранилище правил маршрутизации.
     *
     * @var array
     */
    protected $callbacks = [];

    /**
     * Компилирует и сохраняет правила маршрутизации, если таких еще нет.
     *
     * @param array $routes Массив правил маршрутизации.
     */
    public function __construct($pattern, array $defaults = [], array $regex = [])
    {
        if (is_array($pattern)) {
            if(empty($pattern['pattern'])){
                throw new Exception('Route must contain pattern.');
            }
            $this->pattern = $pattern['pattern'];
            unset($pattern['pattern']);
            foreach($pattern as $key =>$value){
                $this->{$key}($value);
            }
        } else {
            $this->pattern = $pattern;
        }
        $this->defaults = $defaults;
        $this->regex = $regex;
        $this->compiled = $this->compile();
    }

    public function methods($method)
    {
        if(is_array($method)){
            $this->methods = array_merge($this->methods, $method);
        } else {
            $this->methods[] = $method;
        }
        return $this;
    }

    public function middlewares(array $middlewares)
    {
        $this->middlewares += $middlewares;
        return $this;
    }

    public function callback($part, \Closure $callback)
    {
        $this->callbacks[$part] = $callback;
        return $this;
    }


    /**
     * Компилирует правило маршрутизации(превращает в регулярное выражения).
     *
     * @param string $rout  Правило маршрутизации.
     * @param array  $regex Регулярные выражения.
     *
     * @return string Скомпилированное правило.
     */
    public function compile()
    {
        $expression = preg_replace('#'.self::REGEX_ESCAPE.'#', '\\\\$0', $this->pattern);

        if (strpos($expression, '(') !== false) {
            $expression = str_replace(array('(', ')'), array('(?:', ')?'), $expression);
        }

        $expression = str_replace(array('<', '>'), array('(?P<', '>'.self::REGEX_SEGMENT.')'), $expression);

        if (is_array($this->regex)) {
            $search = $replace = array();
            foreach ($this->regex as $key => $value) {
                $search[]  = "<$key>".self::REGEX_SEGMENT;
                $replace[] = "<$key>$value";
            }

            $expression = str_replace($search, $replace, $expression);
        }

        return '#^'.$expression.'$#uD';
    }

    public function getDefaults()
    {
        return $this->defaults;
    }

    public function getCompiledPattern()
    {
        return $this->compiled;
    }

    public function getMethods()
    {
        return $this->methods;
    }

    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    /**
     * @return array
     */
    public function getCallbacks()
    {
        return $this->callbacks;
    }
}
