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
 * @package    Core
 * @subpackage Utils
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class View {
    /**
     * Полный путь к виду.
     *
     * @var string
     */
    protected $view;
    	
    /**
     * Хранилище для переменных вида.
     *
     * @var array
     */
    protected $vars = array() ;
    
    /**
     * Фабрика для видов 
     * 
     * @param string $view Имя вида.
     * @param $path Путь.
     * @return View
     */
    public static function make($view = null, $path = null){
        return new View($view, $path);
    }

    /**
     * Подготовка полного пути для файла вида.
     * 
     * @param string $view Имя вида.
     * @param string $path Путь.
     * @return void
     * @uses Config::get()
     * @uses Request::current()
     */
    public function __construct($view, $path) {
        
        $template = Config::get('system.template');
        
        if (empty($view)) {
            $view = Request::current()->controller().DS.Request::current()->action() ;
	}
        
        if (empty($path)) {
            $this->view = TPL_PATH.$template.DS.'view'.DS.trim($view, DS).'.php';
        } else {
            $this->view = trim($path, DS).DS.trim($view, DS).'.php';
        }
    }
		
    /**
     * Назначение переменных вида
     * @param mixed $var
     *		1) если string, тогда имя переменной в виде,
     *		2) если array, то массив array( 'var' => 'value' )
     * @param mixed $value Значение переменной вида для случая (1).
     * @return View 
     * @uses Arr::merge()
     */
    public function set($var, $value = null) {
        if (is_array($var)) {
            Arr::merge($this->vars, $var);
        } else {
            $this->vars[ $var ] = $value ;
	}
        
        return $this;
    }
    	
    /**
     * Рендеринг Вида
     * 
     * @return string Отрендеренный шаблон.
     * @uses Config::get()
     */
    public function render() {			
        $prefix = Config::get('system.view_prefix');
        if (empty($prefix)) {
            extract( $this->vars) ;
        } else {
            extract( $this->vars, EXTR_PREFIX_ALL, $prefix) ;
        }
        
        ob_start();
        include $this->view;             
        return ob_get_clean();
    }
    
    /**
     * Сеттер
     * 
     * @param string $name Имя переменной
     * @param string $value Значение
     * @return void
     */
    public function __set($name, $value) {
        $this->vars[$name] = $value;   
    }
    
    /**
     * Геттер
     * 
     * @param string $name Имя переменной
     * @return void
     */
    public function &__get($name) {
        return $this->vars[$name];
    }
    
    /**
     * Удаление несуществующих переменных.
     * 
     * @param string $name Имя удаляемой ячейки.
     * @return void
     */
    public function __unset($name) {
        unset($this->vars[$name]);
    }
    
}