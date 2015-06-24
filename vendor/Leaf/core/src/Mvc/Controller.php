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
abstract class Controller extends Middleware
{
    /**
     * Название шаблона.
     *
     * @var string
     */
    protected $template = 'default';

    /**
     * Название шаблона.
     *
     * @var string
     */
    protected $middlewares = array();

    /**
     * Метод запуска контроллера.
     *
     * @return object Объект Http ответа.
     */
    final public function call()
    {
        $this->before();
        $this->{$this->app->request->getAction()}();
        $this->after();
        return $this->app->response;
    }

    /**
     * Вызывается перед всеми действиями.
     *
     * @return void
     */
    public function before()
    {
    }

    /**
     * Вызывается после всех действий.
     *
     * @return void
     */
    public function after()
    {
    }

    /**
     *
     * @param type $param
     */
    final public function addMiddleware($middleware, $params = array())
    {
        $this->middlewares[] = array('middleware' => $middleware, 'params' => $params);
    }

    /**
     *
     * @param type $param
     */
    final public function getMiddlewares()
    {
        return $this->middlewares;
    }
}
