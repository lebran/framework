<?php
namespace Easy\Core;

/**
 * Класс для работы с конфигурациями
 * Добавлять конфигурационные файлы нужно в папку /config/.
 * Для загрузки используется метод "read"
 * 
 *  
 * @package    Core\Config
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
abstract class Config{
    /**
     * @var array массив для хранения конфигураций.
     */
    protected static $data = array();     
    
    /**
     * @var array
     */
    protected static $load_files = array();     
    
    /**
     * Метод для загрузки конфигурационных файлов.
     * Обьединяет настройки с одинаковыми названиями.
     * 
     * @param string $file - имя файла.
     * @param bool $set - сохранять ли их в общий массив(по умолчанию - нет).
     * @return array
     */
    public static function read($file, $set = FALSE)
    {
        if(isset(self::$load_files[$file])) {
            return self::$data[$file];
        }  
        
        $info = pathinfo($file);
        $type = 'php';
        $name = $file;
        
        if(isset($info['extension'])) {
            $type = $info['extension'];
            $name = substr($file, 0, -(strlen($type) + 1));
        }
        $class = 'Easy\\Core\\Config\\'.ucfirst($type);
        
        if(class_exists($class)) {
            $configs = $class::read($name);
            if($set){
                self::set($name ,$configs);
            }
            self::$load_files[$file] = TRUE;
            return $configs;
        } else {
            throw new Config\Exception('"'.$type.'" - в данный момент такой тип не поддержуется.');
        }
    }
    
    /**
     * Устанавливает значение конфигурации.
     * Используйте точечную анотацию.
     * 
     * @param string $name - имя конфигурации или групы.
     * @param mixed $config - значение конфигурации(й).
     */
    public static function set($name, $config)
    {
        $name = explode('.', $name);
        $key = array_shift($name);
        
        if(($total = count($name)) == 0){
            self::$data[$key] = $config;
        }else{
            $group = &self::$data[$key];
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
    public static function get($name)
    {
        $name = explode('.', $name);
        $key = array_shift($name);
        
        if(($total = count($name)) == 0){
            return self::$data[$key];
        }else{
            $group = &self::$data[$key];
            foreach ($name as $k => $v) {
                if(isset($group[$v])){
                    if($k == $total - 1){
                        return $group[$v];
                    }else{
                        $group = &$group[$v];
                    }
                }else{
                    throw new Config\Exception('Конфигурация '.$v.' не установлена');
                }
            }            
        }
    }  
}
