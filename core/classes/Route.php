<?php
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
 * @package Base
 * @author iToktor
 * @since 1.1  
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
     * @var string хранилище правили роутинга. 
     */
    protected static $_routes = NULL;
    
    /**
     * Геттер для правил.
     * 
     * @param string $name - имя правила(по умолчанию все).
     * @return string|array правило(а)
     */    
    public static function get($name = NULL){
        if(empty(self::$_routes)){
            new Route;
        }
        if(!empty($name)){
            return self::$_routes[$name];
        }
        
        return self::$_routes;
        
    }
    
    /**
     *  Подготовка правил для использования:
     *      - загружает файл с правилами
     *      - отпраляет их на компиляцию 
     *      - сохраняет в хранилище
     * 
     * @uses Config::read() 
     */
    private function __construct(){
        $config = Config::read('routes');
        
        foreach ($config as $name => $val){
            if(is_array($val) AND array_key_exists ( 'rout' , $val )){    
                if(!isset($val['regex'])){
                    $val['regex'] = NULL;
                }
                $rout = Route::compile($val['rout'], $val['regex']);
                
                $default = NULL;
                if( array_key_exists ( 'default' , $val )){    
                    $default = $val['default'];
                }
                self::$_routes[$name] = array(
                    'rout' => $rout,
                    'default' => $default
                );
            }  
        }
    }
    
    /**
     * Компилирует правила роутинга.
     * 
     * @param string $uri - правило.
     * @param array $regex - регулярніе выражения.
     * @return string скомпилированое правило
     * @uses Route::REGEX_ESCAPE
     * @uses Route::REGEX_SEGMENT
     */
    public static function compile($uri, $regex){
        $expression = preg_replace('#'.Route::REGEX_ESCAPE.'#', '\\\\$0', $uri);

        if (strpos($expression, '(') !== FALSE){
            $expression = str_replace(array('(', ')'), array('(?:', ')?'), $expression);
        }
            
	$expression = str_replace(array('<', '>'), array('(?P<', '>'.Route::REGEX_SEGMENT.')'), $expression);
		
        if (is_array($regex)){
            $search = $replace = array();
            foreach ($regex as $key => $value){
            	$search[]  = "<$key>".Route::REGEX_SEGMENT;
		$replace[] = "<$key>$value";
            }

            $expression = str_replace($search, $replace, $expression);
	}               
	
        return '#^'.$expression.'$#uD';
    }
}
