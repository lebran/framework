<?php
namespace Leaf\Core\Utils;

/**
 * Description of Finder
 *
 * @author Roma
 */
class Finder
{
    /**
     * Хранилище путей для поиска файлов.
     *
     * @var array
     */
    protected static $path = array(CORE_PATH, APP_PATH);

    /**
     * Ищет файл по заданым параметрам.
     *
     *      Leaf::findFile('images', 'header', 'jpeg', true);
     *      Пути подходящие под эти параметры:
     *          - vendor/leaf/core/images/header.jpeg,
     *          - application/images/header.jpeg,
     *          - vendor/leaf/test/images/header.jpeg  // Если инициализизирован модуль "test"
     *
     * @param string $subfolder Под-папка в которой искать.
     * @param string $name Имя файла.
     * @param string $extension Тип файла.
     * @param bool $return_all Возвращать все найденые файли или первый?
     * @return mixed Полный путь на найденый файл(ы) или false.
     */
    public static function file($subfolder, $name, $extension = 'php', $return_all = false) {
        $fname = $name.'.'.$extension;
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
     * Метод установки или получения(ничего не передавать) путей для поиска файлов.
     *
     * @param mixed $path Новый путь.
     * @param boolean $delete Удалять ли предыдущие пути.
     * @return array Массив добавленых путей в системе.
     * @uses Arr::merge()
     */
    public static function addPath($path, $delete = false)
    {
        if($delete){
            self::$path = is_array($path)? $path: array($path);
        } else {
            if (is_array($path)) {
                Arr::merge(self::$path, $path, true);
            } else {
                array_unshift(self::$path, $path);
            }
        }
    }

    /**
     *
     * @return type
     */
    public static function getPath()
    {
        return self::$path;
    }
}