<?php
namespace Leaf\Core\Http;

/**
 * Реализует обертку для Http запроса с расширенным функционалом.
 *
 *      - Удобный доступ к заголовкам запроса
 *      - Методы проверки типа запроса
 *      - Доступ к обработанным глобальным массивам (POST, GET)
 *      - Обработка имен контроллера, действия, директории
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
     * Полное имя контроллера, включает имя директории.
     *
     * @var string
     */
    protected $controller;

    /**
     * Обработанное имя действия.
     *
     * @var string 
     */
    protected $action;

    /**
     * Параметры запроса, сегменты.
     *
     * @var array 
     */
    protected $params;

    /**
     * Обработанное имя директории, если она была передана.
     *
     * @var string 
     */
    protected $directory = false;

    /**
     * Обработанный глобальный массив POST.
     *
     * @var array
     */
    protected $post;

    /**
     * Обработанный глобальный массив GET.
     *
     * @var array
     */
    protected $get;

    /**
     * Метод запроса.
     *
     * @var string
     */
    protected $method;

    /**
     * Инициализация:
     *      - обработка переданных данных, а так же Http запроса
     *
     * @return void
     * @throws HttpException
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
     * Отправляет полное имя контроллера.
     * 
     * @return string Имя контроллера.
     */
    public function getController()
    {
        return $this->controller;
    }
    
    /**
     * Отправляет обработанное имя действия.
     *
     * @return string Имя действия.
     */
    public function getAction()
    {
        return $this->action;
    }
    
    /**
     * Если ничего не передавать, отправляет массив параметров.
     * При передаче значение отправляет параметр с таким ключем.
     * 
     *      $request->params('id'); // получим значение в ячейке 'id'
     * 
     * @param string $params Имя параметра.
     * @return array|string Выбранный или все параметры.
     */
    public function getParams($params = false)
    {       
        return ($params)? $this->params[$params] : $this->params;
    }
    
    /**
     * Отправляет обработанное имя директории, если она была передана.
     *
     * @return string Имя директории.
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Если ничего не передавать, отправляет массив значений POST.
     * При передаче ключа отправляет значение с таким ключом.
     *
     * @param string $key Ключ по которому будет идти поиск.
     * @param mixed $default Значение которое будет отправлено, если поиск не дал результатов.
     * @return array|string Массив POST, значение по ключу или default.
     */
    public function getPost($key = false, $default = false) {
       return ($key)? (isset($this->post[$key]) ? $this->post[$key] : $default) : $this->post;
    }
    
    /**
     * Если ничего не передавать, отправляет массив значений GET.
     * При передаче ключа отправляет значение с таким ключом.
     *
     * @param string $key Ключ по которому будет идти поиск.
     * @param mixed $default Значение которое будет отправлено, если поиск не дал результатов.
     * @return array|string Массив GET, значение по ключу или default.
     */
    public function getGet($key = false, $default = false)
    {
        return ($key)? (isset($this->get[$key]) ? $this->get[$key] : $default) : $this->get;
    }

    /**
     * Отправляет имя метода, полученного из заголовков запроса.
     * 
     * @return string Метод запроса.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Отправляет значение заголовка из запроса.
     * 
     * @param string $header Имя заголовка.
     * @return string|bool Значение заголовка, если поиск не дал результатов - false.
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
     * Проверяет, является ли post методом запроса.
     * 
     * @return bool true, если метод POST, иначе - false
     */
    public function isPost() {
        return 'POST' == $this->getMethod();
    }
    
    /**
     * Проверяет, является ли get методом запроса.
     * 
     * @return bool true, если метод GET, иначе - false.
     */
    public function isGet() {
        return 'GET' == $this->getMethod();
    }
    /**
     * Проверяет, является ли запрос асинхронным.
     * 
     * @return bool true, если метод XmlHttpRequest, иначе - false.
     */
    public function isXMLHttpRequest() {
        return 'XMLHttpRequest' == $this->getHeader('X_REQUESTED_WITH');
    }
    
    /**
     * Проверяет, использовал ли пользователь защищенное соединение. (HTTPS)
     * @return bool true, если HTTPS, иначе - false.
     */
    public function isHTTPS() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }
}
