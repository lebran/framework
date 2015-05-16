<?php
namespace Easy\Core;

/**
 * 
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
     * Ассоциативный массив. Ключи содержат префикс пространства имён,
     * значение — массив базовых директорий для классов в этом пространстве имён.
     *
     * @var array
     */
    protected static $prefixes = array(
            'Easy\\Core\\' => array(CORE_SRC_PATH)
    );

    /**
     * Ассоциативный массив. Ключи содержат пространства имён, значение — пути для классов.
     *
     * @var array
     */
    protected static $classes = array();

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
     * Деактивирует загрузчик в стеке загрузчиков SPL.
     *
     * @return void
     */
    public static function unregister()
    {
        spl_autoload_unregister(array(self, 'loadClass'));
    }

    /**
     * Добавляет базовую директорию к префиксу пространства имён.
     *
     * @param string $prefix Префикс пространства имён.
     * @param string $base_dir Базовая директория для файлов классов из пространства имён.
     * @param bool $prepend Если true, добавить базовую директорию в начало стека.
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
     * Добавляет базовые директории к префиксам пространств имён.
     *
     * @param array $namespaces Mассив: префикс => базовая директория.
     * @return void
     */
    public function addNamespaces(array $namespaces)
    {
        foreach ($namespaces as $prefix => $base_dir) {
            self::addNamespace($prefix, $base_dir);
        }
    }
    
    /**
     * Возвращает массив: префикс => базовая директория.
     *
     * @return type
     */
    public static function getNamespaces()
    {
        return self::$prefixes;
    }

    /**
     * Добавляет пути к классам. Любой добавленный класс будет сразу же загружен без поиска пути.
     *
     * @param array $classes Mассив: namespace => путь.
     * @return void
     */
    public static function addClasses(array $classes)
    {
        self::$classes += $classes;
    }

    /**
     * Установка псевдонимов. Используется для перекрытия системных классов.
     *
     * @param string|array $class Оригинал или массив: оригинал => псевдоним.
     * @param type $alias Псевдоним для класса.
     * @return void
     */
    public static function addAliases($class, $alias = null)
    {
        if (is_array($class)) {
            foreach ($class as $class => $alias) {
                self::addAlias($class, $alias);
            }
        }
        if (is_string($alias) and is_string($class)) {
            class_alias($alias, $class);
        }
    }

    /**
     * Загружает файл для заданного имени класса.
     *
     * @param string $class Абсолютное имя класса.
     * @return bool Статус загрузки файла.
     */
    public static function loadClass($class)
    {
        if (!empty(self::$classes[$class]) and self::requireFile(self::$classes[$class])) {
            return true;
        }

        $prefix = $class;
        while (($pos = strrpos($prefix, '\\'))) {
            $prefix = substr($class, 0, $pos + 1);
            $relative_class = substr($class, $pos + 1);
                    
            if ((self::loadMappedFile($prefix, $relative_class))) {
                return true;
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
     * @return bool Статус загрузки файла.
     */
    protected static function loadMappedFile($prefix, $relative_class)
    {
        if (!isset(self::$prefixes[$prefix])) {
            return false;
        }
        
        foreach (self::$prefixes[$prefix] as $base_dir) {
            $file = $base_dir.str_replace('\\', '/', $relative_class).'.php';
            if (self::requireFile($file)) {
                return true;
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
