<?php
namespace Leaf\Core\Config;

/**
 * Методы, которые должны быть в каждом драйвере конфигов.
 *
 * @package    Core
 * @subpackage Config
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
interface ConfigInterface 
{            
    /**
     * Метод для загрузки конфигурационных файлов.
     *
     * @param type $name
     */
    public static function read($name);
    
}
