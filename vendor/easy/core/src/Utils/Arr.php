<?php
namespace Easy\Core\Utils;

/**
 * Cодержит методы, которые помогают работать с масивами.
 *
 * @package    Core
 * @subpackage Utils
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Arr{

    /**
     * Извлекает значение из массива по ключу.
     * 
     * @param array &$arr - Массив
     * @param string $key - Ключ массива
     * @return mixed 
     */
    public static function extract( &$arr, $key )
    {
        $val = $arr[$key];
        unset($arr[$key]);
        return $val;
    }
    
    /**
     * Присоединяет массив arr2 к arr1 в конец.
     * 
     * @param array $arr1 - исходный массив.
     * @param array $arr2 - присоединяемый массив.
     * @param bool $prepend - добавить в начало.
     * @return array
     */
    public static function merge( &$arr1, $arr2 , $prepend = FALSE) 
    {
        $arr1 = ($prepend)? array_merge($arr2, $arr1): array_merge($arr1, $arr2);
        return $arr1;
    }
}
