<?php
namespace Easy\Core;

/**
 * Обрабатывает ответ, который отправляется обратно клиенту.
 *
 * @package    Core
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Response{
	
    /**
     * @var array хранилище для заголовков.
     */
    protected $headers = array(
	'Content-Type: text/html; charset=utf-8'
    );

    /** 
     * @var string тело ответа.
     */
    protected $body;

    /**
     * Добавление заголовков.
     *
     * @param string $header - контент заголовка.
     * @return Response
     */
    public function addHeader($header) {
        $this->headers[] = $header;
        return $this;
    }

    /**
     * Редирект
     *
     * @param string $url - УРЛ редиректа.
     */
    public function redirect($url) {
        $url = trim($url, DS);
        $base = Config::get('system.base_url');
        $url = $base.$url;
        $this->addHeader("Location:{$url}");
    }

    /**
     * Отправка заголовков.
     *
     * @return Response
     */
    public function sendHeaders() {
        foreach ($this->headers as $header){
            header($header);
        }
        return $this;
    }

    /**
     * Сеттер, если передать значение.
     * Геттер, если ничего не передавать.
     * 
     * @param string $body - тело ответа.
     * @return string 
     */
    public function body($body = NULL) {
    	if($body !== NULL){
            $this->body = $body;
        }else{
            return $this->body;
        }
    }
    
    public function __toString() 
    {
        return $this->sendHeaders()->body();
    }

}