<?php
namespace Easy\Core\Utils;

use Easy\Core\Config;
use Easy\Core\Request;

/**
 *  Мини-шаблонизатор: передает данные в шаблон и рендерит их.
 * 
 *                             ПРИМЕР
 *      
 *      // Создаем массив данных 
 *      $data = array( 'name' => 'Roman', 'age' => '27' );
 *      
 *      $view = View::make('index') // Выбираем вид
 *                  ->set($data)      // Устанавливаем 
 *                  ->set('n', 'Ann') // значения
 *                  ->render();       // Рендерим
 *                       
 *                      (Запрос внутри запроса)
 *      // Делаем запрос к контроллеру coments действию show.
 *      // Добавляем данные в соответствующую ячейку в обертке
 *      $this->layout->coments = Request::make('/coments/show')
 *                                     ->execute() 
 *                                     ->body();
 * 
 *      // Кидаем наш вид в главный шаблон под псевдонимом content
 *      $this->layout->content = $view;
 * 
 * @package    Core\Utils
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class View {
    /**
     * @var string полный путь к виду.
     */
    protected $view;
    	
    /**
     * @var array хранилище для переменных вида.
     */
    protected $vars = array() ;
    
    /**
     * Фабрика для видов 
     * 
     * @param string $view - имя вида.
     * @param  $path - путь.
     * @return View
     */
    public static function make($view = NULL, $path = NULL){
        return new View($view, $path);
    }

    /**
     * Подготовка полного пути для файла вида.
     * 
     * @param string $view - имя вида.
     * @param string $path - путь.
     * @uses Config::get()
     * @uses Request::current()
     */
    public function __construct($view, $path) {
        
        $template = Config::get('system.template');
        
        if(empty($view)) {
            $view = Request::current()->controller().DS.Request::current()->action() ;
	}
        
        if(empty($path)){
            $this->view = TPL_PATH.$template.DS.'view'.DS.trim($view, DS).'.php';
        }else{
            $this->view = trim($path, DS).DS.trim($view, DS).'.php';
        }
    }
		
    /**
     * Назначение переменных вида
     * @param mixed $var
     *		1) если string, тогда имя переменной в виде,
     *		2) если array, то массив array( 'var' => 'value' )
     * @param mixed $value - значение переменной вида для случая (1)
     * @return View 
     * @uses Arr::merge()
     */
    public function set( $var, $value = NULL ) {
        if( is_array( $var ) ) { 
            Arr::merge($this->vars, $var);
        } else {
            $this->vars[ $var ] = $value ;
	}
        
        return $this;
    }
    
    
		
    /**
     * Рендеринг Вида
     * 
     * @return string
     * @uses Config::get()
     */
    public function render() {			
        $prefix = Config::get('system.view_prefix');
        if(empty($prefix)){
            extract( $this->vars) ;
        }else{
            extract( $this->vars, EXTR_PREFIX_ALL, $prefix) ;
        }
        
        ob_start();
        include $this->view;
	$view = ob_get_clean();
               
        return $view;		
    }
    
    /**
     * Сеттер
     * 
     * @param string $name - имя переменной 
     * @param string $value - значение
     */
    public function __set($name, $value) {
        $this->vars[$name] = $value;   
    }
    
    /**
     * Геттер
     * 
     * @param string $name - имя переменной 
     */
    public function &__get($name) {
        return $this->vars[$name];
    }
    
    /**
     * Удаление несуществующих переменных.
     * 
     * @param string $name - имя удаляемой ячейки.
     */
    public function __unset($name) {
        unset($this->vars[$name]);
    }
    
}