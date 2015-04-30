<?php

/**
 * Класс для работы с конфигурациями
 * Добавлять конфигурационные файлы нужно в папку /config/.
 * Для загрузки используется метод "read"
 * 
 * 
 * @package Base
 * @author iToktor
 * @since 1.1.5
 */
abstract class Config{
    /**
     * @var array массив для хранения конфигураций.
     */
    protected static $_data = array();     

    /**
     * Метод для загрузки конфигурационных файлов.
     * Обьединяет настройки с одинаковыми названиями.
     * 
     * @param string $name - имя файла.
     * @param bool $set - сохранять ли их в общий массив(по умолчанию - нет).
     * @return array
     * @uses Easy_Core::find_file()
     */
    public static function read($name, $set = FALSE){
        $configs = array();
        if(($settings = Easy_Core::find_file('config', $name, TRUE))){    
            foreach ($settings as $config) {
                $configs += include_once $config;
            }
        }   
        if($set){
            self::set($name ,$configs);
        }
        return $configs;
    }
    
    /**
     * Устанавливает значение конфигурации.
     * Используйте точечную анотацию.
     * 
     * @param string $name - имя конфигурации или групы.
     * @param mixed $config - значение конфигурации(й).
     */
    public static function set($name, $config){
        $name = explode('.', $name);
        $key = array_shift($name);
        
        if(($total = count($name)) == 0){
            self::$_data[$key] = $config;
        }else{
            $group = &self::$_data[$key];
            foreach ($name as $k => $v) {
                if($k == $total - 1){
                    $group[$v] = $config;
                }else{
                    $group = &$group[$v];
                }            
            }
        }
    }    
    
    /**
     * Получает значение конфигурации по ключу.
     * 
     *      Нужно использывать точечную анотацию для доступа к свойствам в групповых массивах.
     *      Config::get('database.config.driver'); // $_data[database][config][driver]
     * 
     * @param string $name - ключ.
     * @return string
     * @throws Easy_Exception
     */
    public static function get($name){
        $name = explode('.', $name);
        $key = array_shift($name);
        
        if(($total = count($name)) == 0){
            return self::$_data[$key];
        }else{
            $group = &self::$_data[$key];
            foreach ($name as $k => $v) {
                if(isset($group[$v])){
                    if($k == $total - 1){
                        return $group[$v];
                    }else{
                        $group = &$group[$v];
                    }
                }else{
                    throw new Easy_Exception('Конфигурация '.$v.' не установлена');
                }
            }            
        }
    }  
}
