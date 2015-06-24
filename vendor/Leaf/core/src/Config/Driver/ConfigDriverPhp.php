<?php
namespace Leaf\Core\Config\Driver;

use Leaf\Core\Utils\Finder;

/**
 * Драйвер для конфигураций на php.
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
    /**
     * Расширение подключаемых конфигурационных файлов.
     *
     * @var string
     */
    public static $extension = 'php';

    /**
     * Метод для загрузки конфигурационных php файлов.
     * При нахождении нескольких файлов с одинаковым названием - рекурсивно объединяет в 1.
     *
     * @param string $name Имя файла.
     *
     * @return array Массив загруженных конфигураций.
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
