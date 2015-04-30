<?php
/**
 * Обрабатывает ответ, который отправляется обратно клиенту.
 * 
 * @package Base
 * @author iToktor
 * @since 1.0
 */
class Response{
	
    /**
     * @var array хранилище для заголовков.
     */
    protected $_headers = array(
	'Content-Type: text/html; charset=utf-8'
    );

    /** 
     * @var string тело ответа.
     */
    protected $_body;

    /**
     * Добавление заголовков.
     *
     * @param string $header - контент заголовка.
     * @return Response
     */
    public function add_header($header) {
        $this->_headers[] = $header;
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
        $this->add_header("Location:{$url}");
    }

    /**
     * Отправка заголовков.
     *
     * @return Response
     */
    public function send_headers() {
        foreach ($this->_headers as $header){
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
            $this->_body = $body;
        }else{
            return $this->_body;
        }
    }

}