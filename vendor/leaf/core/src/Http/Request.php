<?php
namespace Leaf\Core\Http;

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
    protected $params;

    /**
     * Директория.
     *
     * @var string 
     */
    protected $directory = null;

    /**
     *
     * @var type
     */
    protected $post;

    /**
     *
     * @var type
     */
    protected $get;

    /**
     *
     * @var type
     */
    protected $method;

    /**
     * Подготовка:
     *  - поиск правила маршрутизации
     *  - извлечение необходимых данных для передачи работы контроллеру
     *
     * @throws HttpException
     * @uses Route::check()
     * @uses Arr::extract()
     */
    public function __construct($params)
    {
        if(empty($params['controller']) and empty($params['action'])){
            throw new HttpException('Ошибка 404');
        }

        $this->controller = 'Controller\\';
        if(!empty($params['directory'])){
            $params['directory'] = array_map('ucfirst', explode(DS, $params['directory']));
            $params['directory'] = implode('\\', $params['directory']);
            $this->controller .= ($this->directory = $params['directory']).'\\';
        }
        $this->controller .= ucfirst($params['controller']).'Controller';

        $this->action = $params['action'].'Action';
        $this->params = $params;

        $this->post = $_POST = array_map('trim', $_POST);
        $this->get = $_GET = array_map('trim', $_GET);

        $this->method = $_SERVER['REQUEST_METHOD'];
    }
  
    /**
     * Геттер для контроллера.
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
     * Gets a POST value by key from request
     * @param string $key a key
     * @param null|mixed $default value that will be settled, if no POST key-value found
     * @return string a POST value
     */
    public function getPost($key = false, $default = false) {
       return ($key)? (isset($this->post[$key]) ? $this->post[$key] : $default) : $this->post;
    }
    
    /**
     *
     * @param type $key
     * @param type $default
     */
    public function getGet($key = false, $default = false)
    {
        return ($key)? (isset($this->get[$key]) ? $this->get[$key] : $default) : $this->get;
    }

    /**
     * Checks if request method is POST
     * @return bool true, if POST, else - false
     */
    public function isPost() {
        return 'POST' == $this->getMethod();
    }
    /**
     * Gets a name of a method of a request from it's headers
     * @return string METHOD name
     */
    public function getMethod() {
        return $this->method;
    }
    /**
     * Checks if request method is GET
     * @return bool true, if GET, else - false
     */
    public function isGet() {
        return 'GET' == $this->getMethod();
    }
    /**
     * Checks if request method is an ASYNC request
     * @return bool true, if it's a XmlHttpReques, else - false
     */
    public function isXMLHttpRequest() {
        return 'XMLHttpRequest' == $this->getHeader('X_REQUESTED_WITH');
    }
    /**
     * Gets a value of header from request
     * @param string $header a name of header
     * @return string|null a headers value, if there is no such header - null
     * @throws \InvalidArgumentException if $header name is empty
     */
    public function getHeader($header) {
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (isset($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers[$header])) {
                return $headers[$header];
            }
            $header = strtolower($header);
            foreach ($headers as $key => $value) {
                if (strtolower($key) == $header) {
                    return $value;
                }
            }
        }
        return null;
    }
    
    /**
     * Checks if user uses secure connection (HTTPS)
     * @return bool true, if HTTPS
     */
    public function isHTTPS() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }
}
