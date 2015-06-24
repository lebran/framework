<?php
namespace Leaf\Core\Utils;

/**
 * Вспомогательный класс, для поиска файлов в установленных папках.
 *
 * @package    Core
 * @subpackage Utils
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Finder
{
    /**
     * Хранилище путей для поиска файлов.
     *
     * @var array
     */
    protected static $path = array();

    /**
     * Ищет файл по заданным параметрам.
     *
     *      Finder::file('images', 'header', 'jpeg', true);
     *      Пути подходящие под эти параметры:
     *          - vendor/leaf/core/images/header.jpeg,
     *          - application/images/header.jpeg,
     *          - vendor/leaf/test/images/header.jpeg  // Если инициализирован модуль "test"
     *
     * @param string $subfolder  Под папка в которой искать.
     * @param string $name       Имя файла.
     * @param string $extension  Расширение файла.
     * @param bool   $return_all Отправлять все найденные файлы или первый?
     *
     * @return mixed Полный путь на найденый файл(ы) или false, если файл(ы) не найден.
     */
    public static function file($subfolder, $name, $extension = 'php', $return_all = false)
    {
        $fname       = $name.'.'.$extension;
        $found_files = array();
        foreach (self::getPath() as $folder) {
            $file = trim($folder, DS).DS.trim($subfolder, DS).DS.$fname;
            if (file_exists($file)) {
                $found_files[] = $file;
            }
        }

        if (!empty($found_files) and !$return_all) {
            return $found_files[0];
        } else if (!empty($found_files)) {
            return $found_files;
        } else {
            return false;
        }
    }

    /**
     * Устанавливает пути для поиска файлов.
     *
     * @param string|array $path   Новый путь(и).
     * @param boolean      $delete Удалять ли предыдущие пути.
     *
     * @return void
     */
    public static function addPath($path, $delete = false)
    {
        if ($delete) {
            self::$path = is_array($path)?$path:array($path);
        } else {
            if (is_array($path)) {
                Arr::merge(self::$path, $path, true);
            } else {
                array_unshift(self::$path, $path);
            }
        }
    }

    /**
     * Отправляет добавленные пути для поиска файлов.
     *
     * @return array Добавленные пути.
     */
    public static function getPath()
    {
        return self::$path;
    }
}