<?php
namespace Easy\Core;

/**
 * Фабрика для моделей.
 * 
 * @package    Core
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Model{
    /**
     * 
     * Создаем экземпляр выбраной модели 
     *
     *     $model = Model::make($name);
     *
     * @param string  $name Имя модели
     * @return Model
     */
    public static function make($name){
        foreach (array_keys(Autoloader::getNamespaces()) as $key) {
            if (class_exists($key.'Model\\'.ucfirst($name).'Model')) {
                $model = $key.'Model\\'.ucfirst($name).'Model';
            }    
        }
        if (empty($model)) {
            throw new Exception('Модель не найдена.') ;
        } else {
            return new $model;
        }
    }	
}