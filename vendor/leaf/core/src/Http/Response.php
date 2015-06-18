<?php
namespace Leaf\Core\Http;

/**
 * Реализует обертку для Http ответа. Формирует Http пакет.
 *
 *       #####################
 *      #      Http пакет     #
 *       #####################
 *      #        Статус       #
 *      #---------------------#
 *      #                     #
 *      #       Заголовки     #
 *      #          ...        #
 *      #                     #
 *      #---------------------#
 *      #         Тело        #
 *       #####################
 *
 * @package    Core
 * @subpackage Http
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Response{

    /**
     * Описание статус кодов.
     *
     * @var array
     */
    protected static $messages = array(
        // Информационные 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Успешные 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Перенаправление 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',

        // Ошибка клиента 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        423 => 'Locked',
        429 => 'Too Many Requests',

        // Ошибка сервера 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded'
    );
        
    /**
     * Тело Http пакета.
     *
     * @var string 
     */
    protected $body = '';

    /**
     * Хранилище для заголовков.
     *
     * @var array 
     */
    protected $headers = array(
	'Content-Type' => 'text/html; charset=utf-8'
    );

    /**
     * Статус код.
     *
     * @var int
     */
    protected $status_сode = 200;
    
    /**
     * Версия Http.
     *
     * @var string
     */
    protected $version = 'HTTP/1.1';

    /**
     * Отправляет тело Http пакета.
     *
     * @return string Тело ответа.
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Устанавливает тело Http пакета.
     *
     * @param string $body Тело ответа.
     * @return Response 
     */
    public function setBody($body)
    {
        $this->body .= (string)$body;
        return $this;
    }

    /**
     * Добавляет заголовки.
     *
     * @param string|array $header Имя заголовка или массив: имя => тело.
     * @param string $value Тело заголовка.
     * @return Response
     */
    public function setHeaders($header, $value = null)
    {
        if (is_array($header)) {
            foreach ($header as $name => $value) {
                $this->headers[$name] = $value;
            }
        }
        $this->headers[$header] = $value;
        return $this;
    }

    /**
     * Отправляет заголовки.
     *
     * @return Response
     */
    public function sendHeaders()
    {
        if (headers_sent()) {
            throw new HttpException('Заголовки уже были отправлены.');
        }
        
        $status_line = $this->version.' '.$this->status_сode.' '.self::$messages[$this->status_сode];
        header($status_line, true, $this->status_сode);

        foreach ($this->headers as $name => $value) {
            header($name . ':' . $value);
        }
        return $this;
    }

    /**
     * Редирект
     *
     * @param string $uri Ури редиректа.
     * @param int $code Статус код.
     * @return void
     */
    public function redirect($uri, $code = 302) {
        $this->addHeaders('Location', trim($uri));
        $this->setStatusCode($code);
    }

    /**
     * Устанавливает статус код для Http пакета.
     * 
     * @param int $status_code Статус код.
     * @return Response
     * @throws HttpException
     */
    public function setStatusCode($status_code) {
        if ($status_code >= 600 || $status_code < 100) {
            throw new HttpException('Неизвестный статус код (поддерживаются 100 ~ 599)!');
        }
        $this->status_сode = $status_code;
        return $this;
    }
    
    /**
     * Устанавливает версию протокола Http.
     * 
     * @param string $version версия Http протокола (1.1 или 1.0).
     * @return Response
     * @throws HttpException
     */
    public function setHttpVersion($version) {
        if ($version != '1.0' || $version != '1.1') {
            throw new HttpException('Поддерживаются только 1.0 и 1.1!');
        }
        $this->version = 'HTTP/'.$version;
        return $this;
    }

    /**
     * Отправляет тело пакета, если пытаются вывести объект.
     *
     * @return string Тело Http пакета.
     */
    public function __toString() 
    {
        return (string)$this->getBody();
    }

}