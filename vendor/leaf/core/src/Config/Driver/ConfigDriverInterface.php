<?php
namespace Leaf\Core\Config\Driver;

/**
 * Интерфейс для драйверов конфигураций.
 *
 * @package    Core
 * @subpackage Config
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
interface ConfigDriverInterface
{            
    /**
     * Читает конфигурации.
     *
     * @param string $name Имя.
     * @return array Массив загруженных конфигураций.
     */
    public static function read($name);   
}
