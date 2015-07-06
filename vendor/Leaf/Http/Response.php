<?php
namespace Leaf\Http;

use Leaf\Http\Response\Exception;

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
 * @package    Http
 * @version    2.1
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Response
{
    /**
     * Status code descriptions.
     *
     * @var array
     */
    protected $messages = array(
        // Information 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // Redirect 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        // Client errors 4xx
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
        // Server errors 5xx
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
     * The Http package body.
     *
     * @var string
     */
    protected $body = '';

    /**
     * Storage headers.
     *
     * @var array
     */
    protected $headers = array();

    /**
     * The Http package status code.
     *
     * @var int
     */
    protected $status_сode;

    /**
     * Http version.
     *
     * @var string
     */
    protected $version = 'HTTP/1.1';

    /**
     * @var
     */
    protected $file;

    /**
     *
     *
     * @param string $body
     * @param int    $status_code
     *
     * @throws Exception
     */
    public function __construct($body = '', $status_code = 200)
    {
        $this->setBody($body);
        $this->setStatusCode($status_code);
    }

    /**
     * Устанавливает версию протокола Http.
     *
     * @param string $version версия Http протокола (1.1 или 1.0).
     *
     * @return Response
     * @throws HttpException
     */
    public function setHttpVersion($version)
    {
        if ($version != '1.0' || $version != '1.1') {
            throw new Exception('Поддерживаются только 1.0 и 1.1!');
        }
        $this->version = 'HTTP/'.$version;
        return $this;
    }

    /**
     *
     *
     * @return string
     */
    public function getHttpVersion()
    {
        return $this->version;
    }

    /**
     * Устанавливает статус код для Http пакета.
     *
     * @param int $status_code Статус код.
     *
     * @return Response
     */
    public function setStatusCode($status_code)
    {
        if ($status_code >= 600 || $status_code < 100) {
            throw new Exception('Unknown status code (supports 100 ~ 599)!');
        }
        $this->status_сode = $status_code;
        return $this;
    }

    /**
     *
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->status_сode;
    }

    /**
     * Добавляет заголовки.
     *
     * @param mixed $headers Имя заголовка или массив: имя => тело.
     *
     * @return Response
     */
    public function setHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Редирект
     *
     * @param string $uri  Ури редиректа.
     * @param int    $code Статус код.
     *
     * @return void
     */
    public function redirect($uri, $code = 302)
    {
        $this->addHeaders('Location', trim($uri));
        $this->setStatusCode($code);
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function getHeaders($name = null)
    {
        return $name?$this->headers[$name]:$this->headers;
    }

    /**
     *
     *
     * @return $this
     */
    public function resetHeaders()
    {
        $this->headers = array();
        return $this;
    }

    /**
     * Устанавливает тело Http пакета.
     *
     * @param string $body Тело ответа.
     *
     * @return Response
     */
    public function setBody($body)
    {
        $this->body = (string)$body;
        return $this;
    }

    public function appendBody($body)
    {
        $this->body .= (string)$body;
        return $this;
    }

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
     * Отправляет заголовки.
     *
     * @return Response
     */
    public function sendHeaders()
    {
        if (headers_sent()) {
            throw new Exception('Заголовки уже были отправлены.');
        }

        $status_line = $this->version.' '.$this->status_сode.' '.$this->messages[$this->status_сode];
        header($status_line, true, $this->status_сode);

        foreach ($this->headers as $name => $value) {
            header($name.':'.$value);
        }
        return $this;
    }

    public function send()
    {
        if (!empty($this->headers)) {
            $this->sendHeaders();
        }

        if (is_string($this->file) and strlen($this->file)) {
            readfile($this->file);
        } else {
            echo $this->body;
        }

        return $this;
    }

    /**
     * Отправляет тело пакета, если пытаются вывести объект.
     *
     * @return string Тело Http пакета.
     */
    public function __toString()
    {
        return $this->send();
    }

    /**
     * Sets an attached file to be sent at the end of the request
     *
     * @param string filePath
     * @param string attachmentName
     *
     * @return asd
     */
    public function setFileToSend($file_path, $attachment_name = null, $attachment = true)
    {
        if (!is_string($attachment_name)) {
            $attachment_name = basename($attachment_name);
        }

        if ($attachment) {
            $this->sendHeaders(
                array(
                    'Content-Description'       => 'File Transfer',
                    'Content-Type'              => 'application/octet-stream',
                    'Content-Disposition'       => 'attachment; filename='.$attachment_name,
                    'Content-Transfer-Encoding' => 'binary'
                )
            );
        }

        $this->file = $file_path;
        return $this;
    }
}