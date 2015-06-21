<?php
namespace Leaf\Core;

/**
 * PSR - 4 Автолоадер
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
        'Leaf\\Core\\' => array(CORE_SRC_PATH)
    );

    /**
     * Ассоциативный массив. Ключи содержат абсолютные имена классов, значение — пути для классов.
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
        spl_autoload_register(array(__NAMESPACE__.'\\Autoloader', 'loadClass'));
    }

    /**
     * Деактивирует загрузчик в стеке загрузчиков SPL.
     *
     * @return void
     */
    public static function unregister()
    {
        spl_autoload_unregister(array(__NAMESPACE__.'\\Autoloader', 'loadClass'));
    }

    /**
     * Добавляет базовую директорию к префиксу пространства имён.
     *
     * @param string $prefix  Префикс пространства имён.
     * @param string $dir     Базовая директория для файлов классов из пространства имён.
     * @param bool   $prepend Если true, добавить базовую директорию в начало стека, иначе в конец.
     *
     * @return void
     */
    public static function addNamespace($prefix, $dir, $prepend = false)
    {
        $prefix = trim($prefix, '\\').'\\';
        $dir    = rtrim($dir, DIRECTORY_SEPARATOR).'/';

        if (!isset(self::$prefixes[$prefix])) {
            self::$prefixes[$prefix] = array();
        }

        if ($prepend) {
            array_unshift(self::$prefixes[$prefix], $dir);
        } else {
            array_push(self::$prefixes[$prefix], $dir);
        }
    }

    /**
     * Добавляет базовые директории к префиксам пространств имён.
     *
     * @param array $namespaces Mассив: префикс => базовая директория.
     *
     * @return void
     */
    public static function addNamespaces(array $namespaces)
    {
        foreach ($namespaces as $prefix => $base_dir) {
            self::addNamespace($prefix, $base_dir);
        }
    }

    /**
     * Возвращает массив: префикс => базовая директория.
     *
     * @return array Массив добавленных неймспейсов.
     */
    public static function getNamespaces()
    {
        return self::$prefixes;
    }

    /**
     * Добавляет пути к классам. Любой добавленный класс будет сразу же загружен без поиска пути.
     *
     * @param array $classes Mассив: неймспейс => путь.
     *
     * @return void
     */
    public static function addClasses(array $classes)
    {
        self::$classes += $classes;
    }

    /**
     * Установка псевдонимов классам. Используется для перекрытия системных классов.
     *
     * @param array $aliases Массив: оригинал => псевдоним.
     *
     * @return void
     */
    public static function addAliases(array $aliases)
    {
        foreach ($aliases as $class => $alias) {
            class_alias($alias, $class);
        }
    }

    /**
     * Загружает файл для заданного имени класса.
     *
     * @param string $class Абсолютное имя класса.
     *
     * @return bool Статус загрузки файла.
     */
    public static function loadClass($class)
    {
        if (!empty(self::$classes[$class]) and self::requireFile(self::$classes[$class])) {
            return true;
        }

        $prefix = $class;
        while (($pos = strrpos($prefix, '\\'))) {
            $prefix         = substr($class, 0, $pos + 1);
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
     * @param string $prefix         Префикс пространства имён.
     * @param string $relative_class Относительное имя класса.
     *
     * @return bool Статус загрузки файла.
     */
    protected static function loadMappedFile($prefix, $relative_class)
    {
        if (!isset(self::$prefixes[$prefix])) {
            return false;
        }

        foreach (self::$prefixes[$prefix] as $dir) {
            $file = $dir.str_replace('\\', '/', $relative_class).'.php';
            if (self::requireFile($file)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Если файл существует, загружаем его.
     *
     * @param string $file файл для загрузки.
     *
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
