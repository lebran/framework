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
                return self::obj();
            case 'integer': 
                return self::int($var);
            case 'double': 
                return self::dbl($var);
            case 'boolean': 
                return self::bool($var);
            case 'resource': 
                return self::res();
            case 'NULL': 
                return self::nul();
            default: 
                return self::unk();
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
    
    protected static function obj() {
        return '(object)';
    }
    
    protected static function int($num) {
        return '(integer) '.$num;
    }
    
    protected static function dbl($num) {
        return '(double) '.$num;
    }
    
    protected static function bool($bool) {
        return $bool? 'TRUE' : 'FALSE';
    }
    
    protected static function res() {
        return '(resource)';
    }
    
    protected static function nul() {
        return 'NULL';
    }
    
    protected static function unk() {
        return '(unknown type)';
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
