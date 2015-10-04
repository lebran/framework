<?php
namespace Lebran\Http;

use Lebran\Http\Response\Exception;

/**
 * Implements a wrapper for the Http response. Generates Http package.
 *
 *       #####################
 *      #     Http package    #
 *       #####################
 *      #     Status line     #
 *      #---------------------#
 *      #                     #
 *      #       Headers       #
 *      #         ...         #
 *      #                     #
 *      #---------------------#
 *      #         Body        #
 *       #####################
 *
 * @package    Http
 * @version    2.0.0
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
    protected static $messages = [
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
    ];

    /**
     * The Http package body.
     *
     * @var string
     */
    protected $body = '';

    /**
     * Store headers.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Store cookies.
     *
     * @var array
     */
    protected $cookies = [];

    /**
     * The Http package status code.
     *
     * @var int
     */
    protected $status = 200;

    /**
     * The Http package version.
     *
     * @var string
     */
    protected $version = 'HTTP/1.1';

    /**
     * The name of attached file.
     *
     * @var array
     */
    protected $file;

    /**
     * Initialisation.
     *
     * @param string $body   Http package body.
     * @param int    $status Http status code.
     *
     * @throws \Lebran\Http\Response\Exception
     */
    public function __construct($body = '', $status = 200)
    {
        $this->setBody($body);
        $this->setStatusCode($status);
    }

    /**
     * Sets the version of the protocol Http.
     *
     * @param string $version Http protocol version (1.1 or 1.0).
     *
     * @return object Response object.
     * @throws \Lebran\Http\Response\Exception
     */
    public function setHttpVersion($version)
    {
        if ($version !== '1.0' || $version !== '1.1') {
            throw new Exception('Supports only 1.0 and 1.1!');
        }
        $this->version = 'HTTP/'.$version;
        return $this;
    }

    /**
     * Gets the version of the protocol Http.
     *
     * @return string Http version.
     */
    public function getHttpVersion()
    {
        return $this->version;
    }

    /**
     * Sets the status code for Http package.
     *
     * @param int $status Http status code.
     *
     * @return object Response object.
     * @throws \Lebran\Http\Response\Exception
     */
    public function setStatusCode($status)
    {
        if ($status >= 600 || $status < 100) {
            throw new Exception('Unknown status code (supports 100 ~ 599)!');
        }
        $this->status = $status;
        return $this;
    }

    /**
     * Gets the status code for Http package.
     *
     * @return int Http status code.
     */
    public function getStatusCode()
    {
        return $this->status;
    }

    /**
     * Adds headers in the storage that will be sent.
     *
     * @param mixed $headers Array: header name => header body.
     *
     * @return object Response object.
     */
    public function setHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Redirects.
     *
     * @param string $uri    Redirect uri.
     * @param int    $status Status code.
     *
     * @return object Response object.
     * @throws \Lebran\Http\Response\Exception
     */
    public function redirect($uri, $status = 302)
    {
        $this->setHeaders(['Location' => '/'.trim(trim($uri), '/')]);
        $this->setStatusCode($status);
        return $this;
    }

    /**
     * Gets http headers or header by key.
     *
     * @param string $name Header name.
     *
     * @return mixed Header or headers.
     */
    public function getHeaders($name = null)
    {
        return $name?$this->headers[$name]:$this->headers;
    }

    /**
     * Reset all headers.
     *
     * @return object Response object.
     */
    public function resetHeaders()
    {
        $this->headers = [];
        return $this;
    }

    /**
     * Sets the cookies for sending.
     *
     * @param object $cookies Cookies object.
     *
     * @return object Response object.
     */
    public function setCookies($cookies)
    {
        $this->cookies[] = $cookies;
        return $this;
    }

    /**
     * Sets the body for Http package.
     *
     * @param string $body Body for http package.
     *
     * @return object Response object.
     */
    public function setBody($body)
    {
        $this->body = (string)$body;
        return $this;
    }

    /**
     * Sets the json body for Http package.
     *
     * @param mixed $body    Json body for http package.
     * @param int   $options Json option.
     *
     * @return object Response object.
     */
    public function setJsonBody($body, $options = 0)
    {
        $this->setHeaders(['Content-Type' => 'application/json']);
        $this->body = json_encode($body, $options);
        return $this;
    }

    /**
     * Adds the body for Http package.
     *
     * @param string $body Body for http package.
     *
     * @return object Response object.
     */
    public function addBody($body)
    {
        $this->body .= (string)$body;
        return $this;
    }

    /**
     * Gets the body for Http package.
     *
     * @return string Body for Http package.
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sends the Http package headers.
     *
     * @return object Response object.
     * @throws \Lebran\Http\Response\Exception
     */
    public function sendHeaders()
    {
        if (headers_sent()) {
            throw new Exception('The headers have already been sent.');
        }

        $status_line = $this->version.' '.$this->status.' '.self::$messages[$this->status];
        header($status_line, true, $this->status);

        foreach ($this->headers as $name => $value) {
            header($name.':'.$value);
        }
        return $this;
    }

    /**
     * Sends the Cookies.
     *
     * @return object Response object.
     */
    public function sendCookies()
    {
        foreach ($this->cookies as $cookie) {
            $cookie->send();
        }

        return $this;
    }

    /**
     * Sends Http package.
     *
     * @return object Response object.
     * @throws \Lebran\Http\Response\Exception
     */
    public function send()
    {
        if (0 !== count($this->cookies)) {
            $this->sendCookies();
        }

        if (0 !== count($this->headers)) {
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
     * Sets an attached file to be sent at the end of the request
     *
     * @param string $file_path   The path to the attached file.
     * @param string $attach_name Name for attached file.
     * @param bool   $attachment  Attached or not.
     *
     * @return object Response object.
     */
    public function setFileToSend($file_path, $attach_name = null, $attachment = true)
    {
        if (!is_string($attach_name)) {
            $attach_name = basename($file_path);
        }

        if ($attachment) {
            $this->setHeaders(
                [
                    'Content-Description'       => 'File Transfer',
                    'Content-Type'              => 'application/octet-stream',
                    'Content-Disposition'       => 'attachment; filename='.$attach_name,
                    'Content-Transfer-Encoding' => 'binary'
                ]
            );
        }

        $this->file = $file_path;
        return $this;
    }
}