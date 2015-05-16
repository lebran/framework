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
     * @var string имя обвертки.
     */
    public $layout = 'index';
    
    /**
     * @var bool авто-рендеринг.
     */
    public $render = TRUE;
        
    /**
     * Если включен авто-рендеринг, загружаем базовый шаблон(обвертки).
     * При переопределении обязательно вызывать(parent::first()).
     * 
     * @uses Config::get()
     * @uses Config::set()
     * @uses View::make()
     */
    public function first() {
        parent::first();
        if($this->render === TRUE ){
            $path = TPL_PATH . $this->template;
            $this->layout = View::make($this->layout, $path);
        } 
    }
    
    /**
     * Если включен авто-рендеринг, загрузка в ответ отрендериного базового шаблона(обвертки).
     * При переопределении обязательно вызывать(parent::first()).
     */
    public function last() {
        parent::last();
        if($this->render === TRUE ){
            $this->response->body($this->layout->render());
        }   
    }
}    