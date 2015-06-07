<?php
namespace Easy\Core\Config;

use Easy\Core\Easy;

/**
 * Драйвер для php конфигов
 *
 * @package    Core
 * @subpackage Config
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
     * При нахождении нескольких файлов с одинаковым названием - рекурсивно обьединяет в 1 конфиг.
     * 
     * @param string $name - имя файла.
     * @return array
     */
    public static function read($name) 
    {
        $configs = array();
        if (($settings = Easy::findFile('config', $name, self::$extension, true))) {
            foreach ($settings as $config) {
                $configs = array_merge_recursive(include $config, $configs);
            }
        } 
        return $configs;
    }
    
}
