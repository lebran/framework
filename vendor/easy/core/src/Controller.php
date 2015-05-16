<?php
namespace Easy\Core;

use Easy\Core\Http\Request;
use Easy\Core\Http\Response;

/**
 * Базовый контролллер.
 *
 * @package    Core
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
abstract class Controller {
    /**
     * @var Request хранилище для запроса.
     */
    public $request;
    
    /**
     * @var Response хранилище для ответа.
     */
    public $response;
    
    /**
     * @var string имя шаблона. 
     */
    public $template = NULL;
    
    /**
     * Метод вызывается перед всеми действиями.
     */
    public function first(){
        if(empty($this->template)){
            $this->template = Config::get('system.template');
        }else{
            Config::set('system.template', $this->template);
        }
    }
    
    /**
     * Метод запуска контроллера.
     * 
     * @throws Easy_Exception
     */
    public function run() {
        $this->first();
        
        $action = $this->request->getAction();
        if(method_exists($this, $action)){
            $this->{$action}();
        }else{
            throw new Exception('Не найден метод '. $action);
        }
        
        $this->last();
    }
    
    /**
     * Конструктор
     * 
     * @param Request $request - текущий запрос.
     * @param Response $response - текущий ответ.
     */
    public function __construct(Request $request, Response $response) {
        $this->request = $request;
        $this->response = $response;
    }
    
    /**
     * Метод вызывается после всех действий.
     */
    public function last(){}
    
    /**
     * Редирект
     * 
     * @param string $url - УРЛ редиректа.
     */		
    public function redirect( $url = '' ) {
        $this->response->redirect($url);
    }
    
}
