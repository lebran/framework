<?php
namespace Easy\Core\Http;

use Easy\Core\Config;

/**
 *                      РОУТИНГ
 * Правила записываются в файл routes в папке core/config.
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
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Route {
    
    /**
     * Что может быть частью (сегмента).
     */
    const REGEX_SEGMENT = '[^/.,;?\n]++';
    
    /**
     * Что должно быть экранировано. 
     */
    const REGEX_ESCAPE  = '[.\\+*?[^\\]${}=!|]';
    
    /**
     * Хранилище правил роутинга.
     *
     * @var array
     */
    protected static $routes = false;
    
    /**
     * Проверяет соответствует ли uri правилам маршрутизации.
     * 
     * @param string $uri Адрес запроса.
     * @return boolean|array Если соответствует - сегменты uri, нет - false.
     */    
    public static function check($uri)
    {
        if(!self::$routes){
            self::init();
        }
        $matches = array();
        $params = array();

        foreach (self::$routes as $rout) {
            if(preg_match($rout['rout'], $uri, $matches)){
                $default = $rout['default'];
                break;
            }
        }

        foreach ($matches as $key => $value) {
            if (is_int($key)) {
                continue;
            }
            $params[$key] = $value;
        }
        $params += $default;
        
        return empty($params)? false : $params;
    }
    
    /**
     *  Подготовка правил для использования:
     *      - загружает файл с правилами
     *      - отпраляет их на компиляцию 
     *      - сохраняет в хранилище
     *
     * @return void
     * @uses Config::read() 
     */
    protected static function init()
    {
        $config = Config::read('routes');
        if (empty($config)) {
            throw new HttpException('Правила маршрутизации не найдены. Проверьте файл config/routes.php');
        }
        foreach ($config as $name => $val) {
            if (is_array($val) AND array_key_exists ('rout' , $val)) {
                if (!isset($val['regex'])) {
                    $val['regex'] = null;
                }
                $rout = self::compile($val['rout'], $val['regex']);
                
                $default = array_key_exists( 'default' , $val )? $val['default'] : null;
                self::$routes[$name] = array(
                    'rout' => $rout,
                    'default' => $default
                );
            }  
        }
    }
    
    /**
     * Компилирует правило роутинга(превращает в правило регулярного выражения).
     * 
     * @param string $rout Правило.
     * @param array $regex Регулярные выражения.
     * @return string Скомпилированное правило.
     * @uses Route::REGEX_ESCAPE
     * @uses Route::REGEX_SEGMENT
     */
    public static function compile($rout, $regex)
    {
        $expression = preg_replace('#'.self::REGEX_ESCAPE.'#', '\\\\$0', $rout);

        if (strpos($expression, '(') !== FALSE){
            $expression = str_replace(array('(', ')'), array('(?:', ')?'), $expression);
        }
            
	$expression = str_replace(array('<', '>'), array('(?P<', '>'.self::REGEX_SEGMENT.')'), $expression);
		
        if (is_array($regex)) {
            $search = $replace = array();
            foreach ($regex as $key => $value) {
            	$search[]  = "<$key>".self::REGEX_SEGMENT;
		$replace[] = "<$key>$value";
            }

            $expression = str_replace($search, $replace, $expression);
	}               
	
        return '#^'.$expression.'$#uD';
    }
}
