<?php
namespace Easy\Core;

use Easy\Core\Utils\Arr;

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
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Request {
    
    /**
     * @var Request хранилище для текущего запроса. 
     */
    protected static $current = NULL;
    
    /**
     * @var Request хранилище для инициализирующего запроса. 
     */
    protected static $initial = NULL;    
    
    /**
     * @var Response хранилище для ответа. 
     */
    protected $response; 
    
    /**
     * @var string контроллер.
     */
    protected $controller;
    
    /**
     * @var string действие.
     */
    protected $action;
    
    /**
     * @var array параметры.
     */
    protected $params = array();
    
    /**
     * @var string директория.
     */
    protected $directory = NULL;

    /**
     * @var string адресс запроса.
     */
    protected $uri = NULL;
    
    /**
     * Сеттер, если передать значение.
     * Геттер, если ничего не передавать.
     * 
     * @param sring $controller - контроллер.
     * @return mixed
     */
    public function controller($controller = NULL) 
    {
        if($controller !== NULL){
            $this->controller = $controller;
        }else{
            return $this->controller;
        }
        
        return $this;
    }
    
    /**
     * Сеттер, если передать значение.
     * Геттер, если ничего не передавать.
     * 
     * @param sring $action - действие.
     * @return mixed
     */
    public function action($action = NULL) 
    {
        if($action !== NULL){
            $this->action = $action;
        }else{
            return $this->action;
        }
        
        return $this;
    }
    
    /**
     * Если ничего не передавать работает как геттер(отправляет массив параметров).
     * При передаче массива работает как сеттер(добавляет этот массив).
     * При передаче значение отправляет параметр с таким ключем.
     * 
     *      $request->params('id'); // получим значение в ячейке 'id'
     * 
     * @param mixed $params - массив параметров или ключ.
     * @return mixed
     */
    public function params($params = NULL) 
    {
        if($params === NULL){
            return $this->params;
        }else if(is_array($params)){
            Arr::merge($this->params, $params);
        }else{
            return $this->params[$params];
        }
        
        return $this;
    }
    
    /**
     * Сеттер, если передать значение.
     * Геттер, если ничего не передавать.
     * 
     * @param sring $directory - директория.
     * @return mixed
     */
    public function directory($directory = NULL) 
    {
        if($directory !== NULL){
            $this->directory = $directory;
        }else{
            return $this->directory;
        }
        
        return $this;
    }
    
    /**
     * Сеттер, если передать значение.
     * Геттер, если ничего не передавать.
     * 
     * @param sring $uri - адрес запроса.
     * @return mixed
     */
    public function uri($uri = NULL) 
    {
        if($uri !== NULL){
            $this->uri = $uri;
        }else{
            return $this->uri;
        }
        
        return $this;
    }
    
    /**
     * Геттер для ответа.
     * @return Response
     */
    public static function response()
    {
        return $this->response;
    }
    
    /**
     * Геттер для текущего запросаю.
     * @return Request
     */
    public static function current()
    {
        return self::$current;
    }
    
    /**
     * Геттер для инициализирующего запроса. 
     * @return Request
     */
    public static function initial()
    {
        return self::$initial;
    }
    
    /**
     * Инициализирует запрос
     * 
     * @param string $uri - адрес запроса.
     * @return Request
     */
    public static function make($uri = NULL) 
    {
        if(self::$initial === NULL){    
            self::$current = self::$initial = new Request($uri);            
        }else{
            self::$current = new Request($uri);
        }
        
        return self::$current;
    }
    
    /**
     * Подготовка:
     *  - поиск правила маршрутизации 
     *  - извлечение необходимых данных для передачи работы контроллеру
     * 
     * @param string $uri - адрес запроса.
     * @throws Easy_Exception
     * @uses Route::get()
     * @uses Arr::extract()
     */
    private function __construct($uri) 
    {
        if($uri === NULL){
            $this->uri(trim($_SERVER[ 'REQUEST_URI' ], DS ));
        }else{
            $this->uri(trim($uri, DS ));
        }
        
        $routes = Route::get();
        $matches = array();
        $params = array();
                
        if(empty($routes)){
            throw new Exception('Правила роутинга отсутствуют. Используйте файл config/routes.php, для их добавления.');
        }
        
        foreach ($routes as $value) { 
            if(preg_match($value['rout'], $this->uri(), $matches)){            
                $default = $value['default'];
                break;
            }
        }
        
        foreach ($matches as $key => $value){
            if (is_int($key)){
                continue;
            }
            $params[$key] = $value;
        }

        if(empty($params) and empty($default)){
            throw new Exception('Правило маршрутизации не найдено или составлено неправильно!!!');
        }
        
        if(!(array_key_exists ( 'action' , $params ))){
            $params['action'] = $default['action'];
        }
        if(!(array_key_exists ( 'controller' , $params ))){
            $params['controller'] = $default['controller'];
        }
        if(!array_key_exists ( 'directory' , $params) and isset($default['directory'])){
            $params['directory'] = $default['directory'];
        }
        
        $controller = 'Controller\\';
        if(!empty($params['directory'])){
            $params['directory'] = array_map('ucfirst', explode(DS, $params['directory']));
            $params['directory'] = implode('\\', $params['directory']);
            $controller .= $params['directory'].'\\';
            $this->directory(Arr::extract($params, 'directory'));
        }
        $controller .= ucfirst(Arr::extract($params, 'controller')).'Controller';
        $this->controller($controller);
        
        $this->action(Arr::extract($params, 'action').'Action');
        
        if(empty($this->controller) or empty($this->action)){
            throw new Exception('Не задан контроллер или действие, проверьте правила маршрутизации!!!');
        }
                
        $this->params($params);
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
        foreach (array_keys(Autoloader::getNamespaces()) as $key) {
            if( class_exists($key.$this->controller)){
                $this->controller($key.$this->controller);
                $refl = new \ReflectionClass($this->controller);
                $controller = $refl->newInstanceArgs(array($this, $this->response = new Response()));
            }    
        }
 
        if(empty($controller)){
            throw new Exception('Не найден контроллер');
        }else{
            if(method_exists($controller, 'run')) {
                $controller->run();
            } else {
                throw new Easy_Exception('Не найден метод запуска контроллера');
            }
        }
        return $this->response;
    }  
}
