<?php
namespace Easy\Core;

/**
 *  
 * @package    Core
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Autoloader
{
    /**
     * Ассоциативный массив. Ключи содержат префикс пространства имён, значение — массив базовых директорий для классов
     * в этом пространстве имён.
     *
     * @var array
     */
    protected static $prefixes = array(
            'Easy\\Core\\' => array(CORE_SRC_PATH)
    );

    /**
     * Регистрирует загрузчик в стеке загрузчиков SPL.
     *
     * @return void
     */
    public static function register()
    {
        spl_autoload_register(array(self, 'loadClass'));
    }

    /**
     * Добавляет базовую директорию к префиксу пространства имён.
     *
     * @param string $prefix Префикс пространства имён.
     * @param string $base_dir Базовая директория для файлов классов из пространства имён.
     * @param bool $prepend Если true, добавить базовую директорию в начало стека. В этом случае она будет
     * проверяться первой.
     * @return void
     */
    public static function addNamespace($prefix, $base_dir, $prepend = false)
    {
        $prefix = trim($prefix, '\\') . '\\';
        $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR) . '/';

        if (!isset(self::$prefixes[$prefix])) {
            self::$prefixes[$prefix] = array();
        }

        if ($prepend) {
            array_unshift(self::$prefixes[$prefix], $base_dir);
        } else {
            array_push(self::$prefixes[$prefix], $base_dir);
        }
    }
    
    /**
     * 
     * @return type
     */
    public static function getNamespaces()
    {
        return self::$prefixes;
    }

    /**
     * Загружает файл для заданного имени класса.
     *
     * @param string $class Абсолютное имя класса.
     * @return mixed Если получилось, полное имя файла. Иначе false.
     */
    public static function loadClass($class)
    {
        $prefix = $class;
        while (($pos = strrpos($prefix, '\\'))) {
            $prefix = substr($class, 0, $pos + 1);
            $relative_class = substr($class, $pos + 1);
                    
            if (($mapped_file = self::loadMappedFile($prefix, $relative_class))) {
                return $mapped_file;
            }

            $prefix = rtrim($prefix, '\\');
        }
        return false;
    }

    /**
     * Загружает соответствующий префиксу пространства имён и относительному имени класса файл.
     *
     * @param string $prefix Префикс пространства имён.
     * @param string $relative_class Относительное имя класса.
     * @return mixed false если файл не был загружен. Иначе имя загруженного файла.
     */
    protected static function loadMappedFile($prefix, $relative_class)
    {
        if (!isset(self::$prefixes[$prefix])) {
            return false;
        }
        
        foreach (self::$prefixes[$prefix] as $base_dir) {
            $file = $base_dir.str_replace('\\', '/', $relative_class).'.php';
            if (self::requireFile($file)) {
                return $file;
            }
        }
        return false;
    }

    /**
     * Если файл существует, загружеаем его.
     *
     * @param string $file файл для загрузки.
     * @return bool true если файл существует, false если нет.
     */
    protected static function requireFile($file)
    {
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        return false;
    }
}
