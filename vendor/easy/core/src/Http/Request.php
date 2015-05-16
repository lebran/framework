<?php
namespace Easy\Core\Http;

use Easy\Core\Utils\Arr;
use Easy\Core\Autoloader;

/**
 * Использует класс Route, что бы определить 
 * какому контроллеру нужно передать работу.
 * 
 *                      ПРИМЕР
 * 
 *      Request::make('admin/login')
 *              ->execute()
 *              ->send_headers()    // Методы класса
 *              ->body();           //   Response
 *  
 * @package    Core
 * @subpackage Http
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Request {
    
    /**
     * Хранилище для текущего запроса.
     *
     * @var Request 
     */
    protected static $current;
    
    /**
     * Хранилище для инициализирующего запроса.
     *
     * @var Request 
     */
    protected static $initial = false;

    /**
     * Инициализирует запрос
     *
     * @param string $uri Адрес запроса.
     * @return Request Обьект созданого запроса.
     */
    public static function make($uri = false)
    {
        self::$current = new self($uri);
        if(self::$initial){
            self::$initial = self::$current;
        }
        return self::$current;
    }

    /**
     * Геттер для текущего запроса.
     * 
     * @return Request
     */
    public static function current()
    {
        return self::$current;
    }

    /**
     * Геттер для инициализирующего запроса.
     *
     * @return Request
     */
    public static function initial()
    {
        return self::$initial;
    }

    /**
     * Хранилище для ответа.
     *
     * @var Response
     */
    protected $response;

    /**
     * Контроллер.
     *
     * @var string
     */
    protected $controller;

    /**
     * Действие.
     *
     * @var string 
     */
    protected $action;

    /**
     * Параметры.
     *
     * @var array 
     */
    protected $params = array();

    /**
     * Директория.
     *
     * @var string 
     */
    protected $directory = NULL;

    /**
     * Адресс запроса.
     *
     * @var string 
     */
    protected $uri = NULL;

    /**
     * Подготовка:
     *  - поиск правила маршрутизации
     *  - извлечение необходимых данных для передачи работы контроллеру
     *
     * @param string $uri Адрес запроса.
     * @throws HttpException
     * @uses Route::check()
     * @uses Arr::extract()
     */
    private function __construct($uri)
    {
        $this->uri = trim(($uri)? $uri: $_SERVER[ 'REQUEST_URI' ], DS );

        $params = Route::check($this->uri);

        if(empty($params['controller']) or empty($params['action'])){
            throw new HttpException('Не задан контроллер или действие, проверьте правила маршрутизации.');
        }

        $this->controller = 'Controller\\';
        if(!empty($params['directory'])){
            $params['directory'] = array_map('ucfirst', explode(DS, $params['directory']));
            $params['directory'] = implode('\\', $params['directory']);
            $this->controller .= ($this->directory = Arr::extract($params, 'directory')).'\\';
        }
        $this->controller .= ucfirst(Arr::extract($params, 'controller')).'Controller';

        $this->action = Arr::extract($params, 'action').'Action';  
        $this->params = $params;
    }

    /**
     * Передает работу выбраному пользователем контроллеру.
     *
     * @return Response - ссылка на ответ.
     * @throws Easy_Exception
     * @uses Response
     * @uses ReflectionClass
     */
    public function execute()
    {
        $this->response = new Response();

        foreach (array_keys(Autoloader::getNamespaces()) as $key) {
            if (class_exists($key.$this->controller)) {
                $this->controller = $key.$this->controller;
                $refl = new \ReflectionClass($this->controller);
                $controller = $refl->newInstanceArgs(array($this, $this->response));
                break;
            }
        }

        if (empty($controller) or !method_exists($controller, 'run') or !method_exists($controller, $this->action)) {
            $this->response->setStatusCode(404);
        } else {
            $controller->run();
        }
        
        return $this->response;
    }  
  
    /**
     * Геттер для контроллер.
     * 
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }
    
    /**
     * Геттер для действия.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }
    
    /**
     * Если ничего не передавать работает как геттер(отправляет массив параметров).
     * При передаче значение отправляет параметр с таким ключем.
     * 
     *      $request->params('id'); // получим значение в ячейке 'id'
     * 
     * @param mixed $params Имя параметра.
     * @return mixed Выбраный или все параметры.
     */
    public function getParams($params = false)
    {       
        return ($params)? $this->params[$params]: $this->params;
    }
    
    /**
     * Геттер для директории.
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }
    
    /**
     * Геттер для uri.
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }
    
    /**
     * Геттер для ответа.
     * 
     * @return Response
     */
    public static function response()
    {
        return $this->response;
    }
}
