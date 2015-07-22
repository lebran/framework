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
    public $name;

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
    protected $defaults = array();

    /**
     * Хранилище правил маршрутизации.
     *
     * @var array
     */
    protected $regex = false;

    /**
     * Хранилище правил маршрутизации.
     *
     * @var array
     */
    protected $middlewares = false;

    /**
     * Хранилище правил маршрутизации.
     *
     * @var array
     */
    protected $callbacks = array();

    /**
     * Компилирует и сохраняет правила маршрутизации, если таких еще нет.
     *
     * @param array $routes Массив правил маршрутизации.
     *
     * @return void
     */
    public function __construct($name ,$pattern)
    {
        $this->name = $name;
        $this->pattern = $pattern;
        /*foreach ($routes as $name => $value) {
            if (is_array($value) and array_key_exists('rout', $value) and empty(self::$routes[$name])) {
                $value['regex'] = empty($value['regex'])?null:$value['regex'];
                $rout           = $this->compile($value['rout'], $value['regex']);

                $default             = array_key_exists('default', $value)?$value['default']:null;
                self::$routes[$name] = array(
                    'rout'    => $rout,
                    'default' => $default
                );
            }
        }*/
    }

    public function defaults(array $defaults)
    {
        $this->defaults = $defaults;
        return $this;
    }

    public function regex(array $regex)
    {
        $this->regex = $regex;
        return $this;
    }

    public function middlewares(array $middlewares)
    {
        $this->middlewares = $middlewares;
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

    public function getRegex()
    {
        return $this->regex;
    }

    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    public function getCallbacks()
    {
        return $this->callbacks;
    }
}
