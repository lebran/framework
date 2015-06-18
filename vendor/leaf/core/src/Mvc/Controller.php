<?php
namespace Leaf\Core\Mvc;

/**
 * Базовый контроллер.
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
     * Хранилище для объекта запроса.
     *
     * @var Request
     */
    public $request;
    
    /**
     * Хранилище для объекта ответа.
     *
     * @var Response
     */
    public $response;
    
    /**
     * Название шаблона.
     *
     * @var string
     */
    public $template = 'default';
    
    /**
     * Вызывается перед всеми действиями.
     *
     * @return void
     */
    public function first(){}
    
    /**
     * Метод запуска контроллера.
     *
     * @return Response
     */
    public function run($action) {
        $this->first();
        $this->{$action}();
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
     * Вызывается после всех действий.
     *
     * @return void
     */
    public function last(){}
    
    /**
     * Редирект
     *
     * @param string $uri Ури редиректа.
     * @param int $code Статус код.
     * @return void
     */		
    public function redirect( $uri = '', $code = 302) {
        $this->response->redirect($uri, $code);
    }
    
}
