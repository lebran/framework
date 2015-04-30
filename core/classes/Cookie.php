<?php

/**
 * Cодержит методы, которые помогают работать с куки.
 * 
 * @package Helpers
 * @author iToktor
 * @since 1.1.5
 */
abstract class Cookie {
    
    /**
     * @var array параметры для кук. 
     */
    public static $params = NULL;
    
    /**
     * Возвращает значение куки по ключу или дефолтное, если куки не найден.
     *
     * @param string $key - имя куки.  
     * @param mixed $default - значение, которое вернется, если куки не найден.
     * @return mixed
     */
    public static function get($key, $default = NULL){
    	if ( ! isset($_COOKIE[$key])){
            return $default;
        }
	
        return $_COOKIE[$key];        
    }

    /**
     * Устанавливает значение или масив кук по ключу.
     * 
     *      Cookie::set('test', array('1' => '1', '2' => '2'),
     *                  array('path' = > '/test/test', 'expiration' => 3600));
     * 
     * @param string $name - имя куки.
     * @param mixed $value - значение или масив.
     * @param array $params - параметры.
     */
    public static function set($name, $value, $params = array()){
        if(empty(self::$params)){
            self::$params = Config::read('cookie');
        }
        
        foreach(self::$params as $key => $val){
            if(empty($params[$key])){
                $params[$key] = $val;
            }
        }
        if ($params['expiration'] != 0){
            $params['expiration'] += time();
        }
                
        if(!is_array($value)){
            setcookie($name, $value, $params['expiration'] , $params['path'], $params['domain'],$params['secure'], $params['httponly']);
        }else{
            foreach ($value as $k => $v) {
                setcookie($name.'['.$k.']', $v, $params['expiration'] , $params['path'], $params['domain'],$params['secure'], $params['httponly']);
            }
        }   
    }
        
    /**
     * Удаление куки по ключу.
     * 
     * @param string $name - имя куки.
     * @param array $params - параметры с которыми обьявляли куки.
     */
    public static function delete($name, $params = array()){
        if(!isset($_COOKIE[$name])){
            return;
        }
        
        $value = NULL;
        if(!is_array($_COOKIE[$name])){
            unset($_COOKIE[$name]);
        }else{
            foreach ($_COOKIE[$name] as $k => &$v) {
                $value[$k] = NULL;
                unset($v); 
            }
        }
        
        $params += array('expiration' => -(time() + 86400));
        self::set($name, $value,$params);
    }
}