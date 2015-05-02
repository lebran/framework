<?php
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
 * @package Base
 * @author iToktor
 * @since 1.2.5
 */
class Request {
    
    /**
     * @var Request хранилище для текущего запроса. 
     */
    protected static $_current = NULL;
    
    /**
     * @var Request хранилище для инициализирующего запроса. 
     */
    protected static $_initial = NULL;    
    
    /**
     * @var Response хранилище для ответа. 
     */
    protected $_response; 
    
    /**
     * @var string контроллер.
     */
    protected $_controller;
    
    /**
     * @var string действие.
     */
    protected $_action;
    
    /**
     * @var array параметры.
     */
    protected $_params = array();
    
    /**
     * @var string директория.
     */
    protected $_directory = NULL;

    /**
     * @var string адресс запроса.
     */
    protected $_uri = NULL;
    
    /**
     * Сеттер, если передать значение.
     * Геттер, если ничего не передавать.
     * 
     * @param sring $controller - контроллер.
     * @return mixed
     */
    public function controller($controller = NULL) {
        if($controller !== NULL){
            $this->_controller = $controller;
        }else{
            return $this->_controller;
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
    public function action($action = NULL) {
        if($action !== NULL){
            $this->_action = $action;
        }else{
            return $this->_action;
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
    public function params($params = NULL) {
        if($params === NULL){
            return $this->_params;
        }else if(is_array($params)){
            Arr::merge($this->_params, $params);
        }else{
            return $this->_params[$params];
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
    public function directory($directory = NULL) {
        if($directory !== NULL){
            $this->_directory = $directory;
        }else{
            return $this->_directory;
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
    public function uri($uri = NULL) {
        if($uri !== NULL){
            $this->_uri = $uri;
        }else{
            return $this->_uri;
        }
        
        return $this;
    }
    
    /**
     * Геттер для ответа.
     * @return Response
     */
    public static function response(){
        return $this->_response;
    }
    
    /**
     * Геттер для текущего запросаю.
     * @return Request
     */
    public static function current(){
        return self::$_current;
    }
    
    /**
     * Геттер для инициализирующего запроса. 
     * @return Request
     */
    public static function initial(){
        return self::$_initial;
    }
    
    
    /**
     * Инициализирует запрос
     * 
     * @param string $uri - адрес запроса.
     * @return Request
     */
    public static function make($uri = NULL) {
        if(self::$_initial === NULL){    
            self::$_current = self::$_initial = $request = new Request($uri);            
        }else{
            self::$_current = $request = new Request($uri);
        }
        
        return $request;
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
    private function __construct($uri) {
        if($uri === NULL){
            $this->uri(trim($_SERVER[ 'REQUEST_URI' ], DS ));
        }else{
            $this->uri(trim($uri, DS ));
        }
        
        $routes = Route::get();
        $matches = array();
        $params = array();
                
        if(empty($routes)){
            throw new Easy_Exception('Правила роутинга отсутствуют. Используйте файл config/routes.php, для их добавления.');
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
            throw new Easy_Exception('Правило маршрутизации не найдено или составлено неправильно!!!');
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
        
        $controller = 'Controller_';
        if(!empty($params['directory'])){
            $params['directory'] = array_map('ucfirst', explode(DS, $params['directory']));
            $params['directory'] = implode('_', $params['directory']);
            $controller .= $params['directory'].'_';
            $this->directory(Arr::extract($params, 'directory'));
        }
        $controller .= ucfirst(Arr::extract($params, 'controller'));
        $this->controller($controller);
        
        $this->action(Arr::extract($params, 'action').'_action');
        
        if(empty($this->_controller) or empty($this->_action)){
            throw new Easy_Exception('Не задан контроллер или действие, проверьте правила маршрутизации!!!');
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
    public function execute(){		

        if( class_exists($this->controller())){
            $refl = new ReflectionClass($this->controller());
            $controller = $refl->newInstanceArgs(array($this, $this->_response = new Response()));
        }else{
            throw new Easy_Exception('Не найден контроллер') ;
        }
        
        if(method_exists($controller, 'run')) {
            $controller->run();
        } else {
            throw new Easy_Exception('Не найден метод запуска контроллера');
        }
        
        return $this->_response;
    }  
}
