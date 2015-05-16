<?php
namespace Easy\Debug;

/**
 * 
 *
 * @package    Debug
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Vr
{
    
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
        $str = '<span style="color: blue">array</span>(<span style="color: orange">'.count($arr)."</span>)<br />".self::getLvlDelim()."{<br />";
        self::$lvl++;
        foreach ($arr as $key => $val) {
            $str .= self::getLvlDelim(-1).'"<span style="color: green">'.$key.'</span>" => '.self::dump($val)."<br />";
        }
        self::$lvl--;
        $str .= self::getLvlDelim().'}';
        return $str;
    }
    
    protected static function str($str) {
        return '<span style="color: blue">string</span>(<span style="color: orange">'.mb_strlen($str).'</span>) "<span style="color: green">'.$str.'</span>"';
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
    
    protected static function getLvlDelim($plus = 0) {
        $delim = '';
        $lvl = (self::$lvl*2)+$plus;
        for($i=0; $i < $lvl; $i++){
            $delim .= self::$delim;
        }
        return $delim;
    }
}
