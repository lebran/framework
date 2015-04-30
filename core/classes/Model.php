<?php

/**
 * Фабрика для моделей.
 * 
 * @package Base
 * @author iToktor
 * @since 1.1
 */
class Model{
    /**
     * 
     * Создаем экземпляр выбраной модели 
     *
     *     $model = Model::make($name);
     *
     * @param string  $name - имя модели
     * @return Model
     */
    public static function make($name){
        $class = 'Model_'.ucfirst($name);
    	return new $class;
    }	
}