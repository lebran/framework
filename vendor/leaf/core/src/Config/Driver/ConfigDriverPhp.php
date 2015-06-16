<?php
namespace Leaf\Core\Config\Driver;

use Leaf\Core\Utils\Finder;

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
class ConfigDriverPhp implements ConfigDriverInterface
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
        if (($settings = Finder::file('config', $name, self::$extension, true))) {
            foreach ($settings as $config) {
                $configs = array_merge_recursive(include $config, $configs);
            }
        } 
        return $configs;
    }
    
}
