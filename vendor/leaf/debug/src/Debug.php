<?php

/**
 * Description of Debug
 *
 * @package Debug
 * @author iToktor
 * @since 1.0
 */
class Debug {
    
    /**
     * 
     * @var type 
     */
    protected static $_e;
        
    
    
    

    /**
     * 
     */
    public static function init() {
        set_exception_handler(function (Leaf_Exception $e) {
            
        });
        
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
        
        register_shutdown_function(function () {
            $shutdown_errors = array(E_PARSE, E_ERROR, E_USER_ERROR);
            if($error = error_get_last() AND in_array($error['type'], $shutdown_errors)){
                ob_get_level() AND ob_clean();
                Leaf_Core::exception_handler(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
                exit(1);
            }
        });
    }
    
    /*
     * 
     */
    public static function toolbar() {
        return Request::make('toolbar'.DS.'run')->execute()->body();
    }
    
    
    
    public static function get_msgs(){
        return self::$_msgs;
    }
}
