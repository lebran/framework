<?php
namespace Lebran;

/**
 * Autoloader implement PSR - 4.
 *
 * @version    2.1
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Autoloader
{
    /**
     * An associative array where the key is a namespace prefix and the value
     * is an array of base directories for classes in that namespace.
     *
     * @var array
     */
    protected $prefixes = array();

    /**
     * An associative array where the key is a absolute class name and the value is a path for class.
     *
     * @var array
     */
    protected $classes = array();

    /**
     * Register loader with SPL autoloader stack.
     *
     * @return void
     */
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    /**
     * Un-register loader with SPL autoloader stack.
     *
     * @return void
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    /**
     * Adds a base directory for a namespace prefix.
     *
     * @param string $prefix  Namespace prefix.
     * @param string $dir     A base directory for class files in the namespace.
     * @param bool   $prepend If true, prepend the base directory to the stack instead of appending it.
     *
     * @return void
     */
    public function addNamespace($prefix, $dir, $prepend = false)
    {
        $prefix = trim($prefix, '\\').'\\';
        $dir    = rtrim($dir, DIRECTORY_SEPARATOR).'/';

        if (!isset($this->prefixes[$prefix])) {
            $this->prefixes[$prefix] = array();
        }

        if ($prepend) {
            array_unshift($this->prefixes[$prefix], $dir);
        } else {
            array_push($this->prefixes[$prefix], $dir);
        }
    }

    /**
     * Adds a bases directory for a namespaces prefix.
     *
     * @param array $namespaces Array: 'prefix' => 'dir'.
     *
     * @return void
     */
    public function addNamespaces(array $namespaces)
    {
        foreach ($namespaces as $prefix => $dir) {
            $this->addNamespace($prefix, $dir);
        }
    }

    /**
     * Get register prefixes.
     *
     * @return array 'prefix' => 'dir'.
     */
    public function getNamespaces()
    {
        return $this->prefixes;
    }

    /**
     * Adds a classpath. Each class will be added immediately loaded without search path.
     *
     * @param array $classes Array: absolute class name => path for file.
     *
     * @return void
     */
    public function addClasses(array $classes)
    {
        $this->classes += $classes;
    }

    /**
     * Loads the class file for a given class name.
     *
     * @param string $class The fully-qualified class name.
     *
     * @return mixed The mapped file name on success, or boolean false on failure.
     */
    public function loadClass($class)
    {
        if (!empty($this->classes[$class]) && $this->requireFile($this->classes[$class])) {
            return true;
        }

        $prefix = $class;
        while (($pos = strrpos($prefix, '\\'))) {
            $prefix         = substr($class, 0, $pos + 1);
            $relative_class = substr($class, $pos + 1);

            if ($this->loadMappedFile($prefix, $relative_class)) {
                return true;
            }

            $prefix = rtrim($prefix, '\\');
        }
        return false;
    }

    /**
     * Load the mapped file for a namespace prefix and relative class.
     *
     * @param string $prefix         The namespace prefix.
     * @param string $relative_class The relative class name.
     *
     * @return mixed Boolean false if no mapped file can be loaded, or the
     * name of the mapped file that was loaded.
     */
    protected function loadMappedFile($prefix, $relative_class)
    {
        if (!isset($this->prefixes[$prefix])) {
            return false;
        }

        foreach ($this->prefixes[$prefix] as $dir) {
            $file = $dir.str_replace('\\', '/', $relative_class).'.php';
            if ($this->requireFile($file)) {
                return true;
            }
        }
        return false;
    }

    /**
     * If a file exists, require it from the file system.
     *
     * @param string $file The file to require.
     *
     * @return bool True if the file exists, false if not.
     */
    protected function requireFile($file)
    {
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        return false;
    }
}
