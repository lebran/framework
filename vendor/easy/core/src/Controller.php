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
     * Хранилище для запроса.
     *
     * @var Request
     */
    public $request;
    
    /**
     * Хранилище для ответа.
     *
     * @var Response
     */
    public $response;
    
    /**
     * Имя шаблона.
     *
     * @var string
     */
    public $template = NULL;
    
    /**
     * Метод вызывается перед всеми действиями.
     *
     * @return void
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
     * @return void
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
     * @param Request $request Текущий запрос.
     * @param Response $response Текущий ответ.
     * @return void
     */
    public function __construct(Request $request, Response $response) {
        $this->request = $request;
        $this->response = $response;
    }
    
    /**
     * Метод вызывается после всех действий.
     *
     * @return void
     */
    public function last(){}
    
    /**
     * Редирект
     * 
     * @param string $uri Ури редиректа.
     * @return void
     */		
    public function redirect( $uri = '', $code = 302) {
        $this->response->redirect($uri, $code);
    }
    
}
