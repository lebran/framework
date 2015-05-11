<?php
namespace Easy\Core\Config;

use Easy\Core\Easy;

/**
 *  
 * @package    Core\Config
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Php implements ConfigInterface
{   
    public static $extension = 'php';
    
    /**
     * Метод для загрузки конфигурационных файлов.
     * Обьединяет настройки с одинаковыми названиями.
     * 
     * @param string $name - имя файла.
     * @return array
     */
    public static function read($name) 
    {
        $configs = array();
        if(($settings = Easy::findFile('config', $name, self::$extension, TRUE))){    
            foreach ($settings as $config) {
                $configs += include_once $config;
            }
        } 
        return $configs;
    }
    
}
