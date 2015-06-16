<?php
namespace Leaf\Debug;

/**
 * 
 *
 * @package    Debug
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Variable
{   
    public static function dump($var) {
        switch (gettype($var)){
            case 'array': 
                return self::arr($var);
            case 'string': 
                return self::string($var);
            case 'object': 
                return self::object($var);
            case 'integer': 
                return self::integer($var);
            case 'double': 
                return self::double($var);
            case 'boolean': 
                return self::boolean($var);
            case 'resource': 
                return self::res();
            case 'NULL': 
                return self::null();
            default: 
                return self::unknown();
        }
    }
    
    protected static function arr($arr) { 
        $str = '<span style="color: blue">array </span>(<span style="color: orange">'.count($arr)."</span>)<br />".self::getLvlDelim()."{<br />";
        self::$lvl++;
        foreach ($arr as $key => $val) {
            $str .= self::getLvlDelim(-1).'"<span style="color: green">'.$key.'</span>" => '.self::dump($val)."<br />";
        }
        self::$lvl--;
        $str .= self::getLvlDelim().'}';
        return $str;
    }
    
    protected static function string($str) {
        return '<span style="color: blue">string </span>(<span style="color: orange">'.mb_strlen($str).'</span>) "<span style="color: green">'.$str.'</span>"';
    }
    
    protected static function object($obj) {
        $obj = (array)$obj;
        $str = '<span style="color: blue">object </span>(<span style="color: orange">'.count($obj)."</span>)<br />".self::getLvlDelim()."{<br />";
        self::$lvl++;
        foreach ($obj as $key => $val) {
            $str .= self::getLvlDelim(-1).'"<span style="color: green">'.$key.'</span>" => '.self::dump($val)."<br />";
        }
        self::$lvl--;
        $str .= self::getLvlDelim().'}';
        return $str;
    }
    
    protected static function integer($num) {
        return '<span style="color: blue">integer </span>('.$num.')';
    }
    
    protected static function double($num) {
        return '<span style="color: blue">double </span>('.$num.')';
    }
    
    protected static function boolean($bool) {
        return $bool? 'true' : 'false';
    }
    
    protected static function res() {
        return '(resource)';
    }
    
    protected static function null() {
        return 'null';
    }
    
    protected static function unknown() {
        return '(unknown type)';
    }
    
    protected static $delim = "&nbsp&nbsp&nbsp&nbsp";
    
    protected static $lvl = 0;

    protected static function getLvlDelim($plus = 0) {
        $delim = '';
        $lvl = (self::$lvl*2)+$plus;
        for($i=0; $i < $lvl; $i++){
            $delim .= self::$delim;
        }
        return $delim;
    }
}
