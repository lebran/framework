<?php
namespace Leaf\Core\Mvc;

use Leaf\Core\Exception;

/**
 * Базовый контролллер.
 *
 * @package    Core
 * @subpackage Mvc
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
    public $template = 'default';
    
    /**
     * Метод вызывается перед всеми действиями.
     *
     * @return void
     */
    public function first(){}
    
    /**
     * Метод запуска контроллера.
     *
     * @return void
     * @throws Exception
     */
    public function run($action) {
        $this->first();
        
        if (method_exists($this, $action)) {
            $this->{$action}();
        } else {
            throw new Exception('Не найден метод '. $action);
        }
        
        $this->last();

        return $this->response;
    }
    
    /**
     * Конструктор
     * 
     * @param Request $request Текущий запрос.
     * @param Response $response Текущий ответ.
     * @return void
     */
    public function __construct($request, $response) {
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
