<?php
namespace Easy\Core\Utils;

use Easy\Core\Controller;

/**
 * Класс помощник для создания шаблонов,
 * состоящих из нескольких маленьких(видов),
 * реализует принцип обвертки.
 * 
 * @package    Core
 * @subpackage Utils
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
abstract class Layout extends Controller {
    
    /**
     * Имя обвертки.
     *
     * @var string 
     */
    public $layout = 'index';
    
    /**
     * Авто-рендеринг.
     *
     * @var bool 
     */
    public $render = true;
        
    /**
     * Если включен авто-рендеринг, загружаем базовый шаблон(обвертки).
     * При переопределении обязательно вызывать(parent::first()).
     *
     * @return void
     * @uses Config::get()
     * @uses Config::set()
     * @uses View::make()
     */
    public function first() {
        parent::first();
        if ($this->render === true) {
            $path = TPL_PATH . $this->template;
            $this->layout = View::make($this->layout, $path);
        } 
    }
    
    /**
     * Если включен авто-рендеринг, загрузка в ответ отрендериного базового шаблона(обвертки).
     * При переопределении обязательно вызывать(parent::first()).
     * @return void
     */
    public function last() {
        parent::last();
        if ($this->render === true) {
            $this->response->body($this->layout->render());
        }   
    }
}    