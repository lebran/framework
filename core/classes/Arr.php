<?php

/**
 * Cодержит методы, которые помогают работать с масивами.
 *
 * @package Helpers
 * @author iToktor
 * @since 1.1
 */
class Arr{

    /**
     * Извлекает значение из массива по ключу.
     * 
     * @param array &$arr - Массив
     * @param string $key - Ключ массива
     * @return mixed 
     */
    public static function extract( &$arr, $key){
        $val = $arr[$key];
        unset($arr[$key]);
        return $val;
    }
    
    /**
     * Присоединяет массив arr2 к arr1 в конец.
     * 
     * @param array $arr1 - исходный массив.
     * @param array $arr2 - присоединяемый массив.
     * @return array
     */
    public static function merge(&$arr1, $arr2) {
        $arr1 = array_merge($arr1, $arr2);
        return $arr1;
    }
}
