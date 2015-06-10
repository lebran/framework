<?php
namespace Easy\Core;

use Easy\Core\Utils\Arr;

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
     * Название вьюхи.
     *
     * @var string
     */
    protected $view;

    /**
     * Путь к шаблону.
     *
     * @var string
     */
    protected $layout;
    	
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
    public static function make($view = false, $path = false, $template = false){
        if (!$template) {
            $template = Config::get('system.template');
        }
        return new View($view, $path, $template);
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
    public function __construct($view, $path, $template) {       
        if (!$view) {
            $view = Request::current()->controller().DS.Request::current()->action() ;
	}
        $this->layout = ($path? trim($path, DS).DS.'templates'.DS : TPL_PATH).$template.DS;
        $this->view = trim($view, DS).'.php';
    }

    public function partial($view, array $vars = array(), $var = false)
    {
        $view = $this->layout.trim($view, DS).'.php';
        if (!file_exists($view)) {
            throw new Exception('Файл вида "'.$view.'" не найден.');
        }

        $render = $this->obInclude($view, $vars);
        if ($var) {
            Arr::setAnnotation($var, $render, $this->vars);
            return $this;
        } else {
            return $render;
        }
        
    }

    public function getLayoutPath()
    {
        return $this->layout;
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
    public function set($var, $value)
    {
        Arr::setAnnotation($var, $value, $this->vars);    
        return $this;
    }

    /**
     *
     * @param type $view
     * @return type
     */
    protected function obInclude($view, $vars = false)
    {
        $vars = $vars? $vars : $this->vars;
        $prefix = Config::get('system.view_prefix');
        if (empty($prefix)) {
            extract($vars);
        } else {
            extract($vars, EXTR_PREFIX_ALL, $prefix);
        }
        ob_start();
        include $view;
        return ob_get_clean();
    }

    /**
     * Рендеринг Вида
     * 
     * @return string Отрендеренный шаблон.
     * @uses Config::get()
     */
    public function render()
    {
        if (!file_exists($this->layout.$this->view)) {
            throw new Exception('Файл вида "'.$this->layout.$this->view.'" не найден.');
        }

        return $this->obInclude($this->layout.$this->view);
    }

    /**
     * Сеттер
     * 
     * @param string $name Имя переменной
     * @param string $value Значение
     * @return void
     */
    public function __set($name, $value)
    {
        $this->vars[$name] = $value;   
    }
    
    /**
     * Геттер
     * 
     * @param string $name Имя переменной
     * @return void
     */
    public function &__get($name)
    {
        return $this->vars[$name];
    }
    
    /**
     * Удаление несуществующих переменных.
     * 
     * @param string $name Имя удаляемой ячейки.
     * @return void
     */
    public function __unset($name)
    {
        unset($this->vars[$name]);
    }
    
}