<?php

/**
 * Description of Debug
 *
 * @package Debug
 * @author iToktor
 * @since 1.0
 */
class Debug_Var {
    
    protected static $delim = "&nbsp&nbsp&nbsp&nbsp";
    
    protected static $lvl = 0;
    
    public static function dump($var) {
        switch (gettype($var)){
            case 'array': 
                return self::arr($var);
            case 'string': 
                return self::str($var);
            case 'object': 
                self::obj();
            break;
            case 'integer': 
                return self::str($var);
        }
    }
    
    protected static function arr($arr) { 
        $str = "Array<br />".self::get_lvl_delim()."(<br />";
        self::$lvl++;
        foreach ($arr as $key => $val) {
            $str .= self::get_lvl_delim(-1)."[".$key.'] => '.self::dump($val)."<br />";
        }
        self::$lvl--;
        $str .= self::get_lvl_delim().')';
        return $str;
    }
    
    protected static function str($str) {
        return '(string) "'.$str.'"';
    }
    
    protected static function get_lvl_delim($plus = 0) {
        $delim = '';
        $lvl = (self::$lvl*2)+$plus;
        for($i=0; $i < $lvl; $i++){
            $delim .= self::$delim;
        }
        return $delim;
    }
}
