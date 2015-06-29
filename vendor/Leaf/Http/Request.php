<?php
namespace Leaf\Http;

/**
 * Реализует обертку для Http запроса с расширенным функционалом.
 *
 *      - Удобный доступ к заголовкам запроса
 *      - Методы проверки типа запроса
 *      - Доступ к обработанным глобальным массивам (POST, GET)
 *
 * @package    Http
 * @version    2.1
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Request
{

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
     * Обработанный глобальный массив SERVER.
     *
     * @var array
     */
    protected $server;

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
    public function __construct()
    {      
        array_walk_recursive($_POST, 'trim');
        array_walk_recursive($_GET, 'trim');
        array_walk_recursive($_SERVER, 'trim');

        $this->post = $_POST;
        $this->get  = $_GET;
        $this->server = $_SERVER;

        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Если ничего не передавать, отправляет массив значений POST.
     * При передаче ключа отправляет значение с таким ключом.
     *
     * @param string $key     Ключ по которому будет идти поиск.
     * @param mixed  $default Значение которое будет отправлено, если поиск не дал результатов.
     *
     * @return array|string Массив POST, значение по ключу или default.
     */
    public function getPost($key = false, $default = false)
    {
        return ($key)?(isset($this->post[$key])?$this->post[$key]:$default):$this->post;
    }

    /**
     * Если ничего не передавать, отправляет массив значений GET.
     * При передаче ключа отправляет значение с таким ключом.
     *
     * @param string $key     Ключ по которому будет идти поиск.
     * @param mixed  $default Значение которое будет отправлено, если поиск не дал результатов.
     *
     * @return array|string Массив GET, значение по ключу или default.
     */
    public function getGet($key = false, $default = false)
    {
        return ($key)?(isset($this->get[$key])?$this->get[$key]:$default):$this->get;
    }

    /**
     * Если ничего не передавать, отправляет массив значений SERVER.
     * При передаче ключа отправляет значение с таким ключом.
     *
     * @param string $key     Ключ по которому будет идти поиск.
     * @param mixed  $default Значение которое будет отправлено, если поиск не дал результатов.
     *
     * @return array|string Массив SERVER, значение по ключу или default.
     */
    public function getServer($key = false, $default = false)
    {
        return ($key)?(isset($this->server[$key])?$this->server[$key]:$default):$this->server;
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
     *
     * @return string|bool Значение заголовка, если поиск не дал результатов - false.
     */
    public function getHeader($header)
    {
        $temp = 'HTTP_'.strtoupper(str_replace('-', '_', $header));
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
    public function isPost()
    {
        return 'POST' == $this->getMethod();
    }

    /**
     * Проверяет, является ли get методом запроса.
     *
     * @return bool true, если метод GET, иначе - false.
     */
    public function isGet()
    {
        return 'GET' == $this->getMethod();
    }

    /**
     * Проверяет, является ли запрос асинхронным.
     *
     * @return bool true, если метод XmlHttpRequest, иначе - false.
     */
    public function isXMLHttpRequest()
    {
        return 'XMLHttpRequest' == $this->getHeader('X_REQUESTED_WITH');
    }

    /**
     * Проверяет, использовал ли пользователь защищенное соединение. (HTTPS)
     *
     * @return bool true, если HTTPS, иначе - false.
     */
    public function isHTTPS()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }
}
