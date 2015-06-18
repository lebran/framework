<?php
namespace Leaf\Core\Mvc;

use Leaf\Core\Utils\Arr;

/**
 *  Мини-шаблонизатор: передает данные в шаблон и рендерит их.
 *  Реализована возможность использования под видов.
 *  Для установки данных в под ячейки, используется точечная аннотация.
 * 
 *                             ПРИМЕР
 *      
 *      // Создаем массив данных 
 *      $data = array( 'name' => 'Roman', 'surname' => 'Kritskiy', 'age' => '27');
 *      
 *      //  Выбираем view под именем "member"
 *      View::make('member')
 *          //  Устанавливаем под вид(about) в ячейку, которая будет доступна как $member['about']
 *          ->partial('about', $data, 'member.about')
 *          //  Устанавливаем значение в ячейку, которая будет доступна как $member['gender']
 *          ->set('member.gender', 'male')
 *          //  Рендерим и отправляем
 *          ->render();
 * 
 * @package    Core
 * @subpackage Mvc
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class View {
    /**
     * Название вида.
     *
     * @var string
     */
    protected $view;

    /**
     * Абсолютный путь к папке шаблона.
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
     * Отправляет объект View.
     * 
     * @param string $view Имя вида.
     * @param string $template Название шаблона.
     * @param string $path Путь к папке 'templates'.
     * @return View
     */
    public static function make($view , $template = 'default', $path = false){
        return new View($view, $template, $path);
    }

    /**
     * Генерирует абсолютный путь к файлу вида.
     * 
     * @param string $view Имя вида.
     * @param string $path Путь к папке 'templates'.
     * @return void
     */
    public function __construct($view, $template, $path) {
        $this->layout = ($path? trim($path, DS).DS.'templates'.DS : TPL_PATH).$template.DS;
        $this->view = trim($view, DS).'.php';
    }

    /**
     * Рендерит под вид и отправляет его.
     * Если передать 3 параметр, установит в ячейку с соответствующим именем.
     *
     * @param string $view Имя вида.
     * @param array $vars Массив переменных под вида.
     * @param string $var Ячейка, куда установить под вид.
     * @return string|View Объект или отрендереный шаблон.
     * @throws ViewException
     */
    public function partial($view, array $vars = array(), $var = false)
    {
        $view = $this->layout.trim($view, DS).'.php';
        if (!file_exists($view)) {
            throw new ViewException('Файл вида "'.$view.'" не найден.');
        }

        $render = $this->obInclude($view, $vars);
        if ($var) {
            Arr::setAnnotation($var, $render, $this->vars);
            return $this;
        } else {
            return $render;
        }       
    }

    /**
     * Отправляет абсолютный путь к шаблону.
     *
     * @return string Путь к шаблону.
     */
    public function getLayoutPath()
    {
        return $this->layout;
    }

    /**
     * Устанавливает переменные вида.
     *
     * @param string|array $var
     *		1) если string, тогда имя переменной в виде,
     *		2) если array, то массив array( 'var' => 'value' )
     * @param mixed $value Значение переменной вида для случая (1).
     * @return View 
     */
    public function set($var, $value)
    {
        Arr::setAnnotation($var, $value, $this->vars);    
        return $this;
    }

    /**
     * Вспомогательный метод для рендеринга.
     *
     * @param string $view Абсолютный путь к файлу вида.
     * @param array $vars Переменные вида.
     * @return string
     */
    protected function obInclude($view, $vars)
    {
        extract($vars);
        ob_start();
        include $view;
        return ob_get_clean();
    }

    /**
     * Рендерит вид.
     * 
     * @return string Отрендереный шаблон.
     * @throws ViewException
     */
    public function render()
    {
        if (!file_exists($this->layout.$this->view)) {
            throw new ViewException('Файл вида "'.$this->layout.$this->view.'" не найден.');
        }

        return $this->obInclude($this->layout.$this->view, $this->vars);
    }

    /**
     * Сеттер
     * 
     * @param string $name Имя переменной.
     * @param string $value Значение.
     * @return void
     */
    public function __set($name, $value)
    {
        $this->vars[$name] = $value;   
    }
    
    /**
     * Геттер
     * 
     * @param string $name Имя переменной.
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