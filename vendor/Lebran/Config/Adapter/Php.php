<?php
namespace Lebran\Config\Adapter;

use Lebran\Config\Adapter;
use Lebran\Config\Exception;

/**
 * It's adapter for PHP config files.
 *
 * @package    Config
 * @subpackage Adapter
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Php extends Adapter
{
    /**
     * The extension of php configs.
     *
     * @var string
     */
    protected $extension = '.php';

    /**
     * Loads php config file.
     *
     * @param string $path The path to the file.
     *
     * @return array Config file as array.
     * @throws \Lebran\Config\Exception
     */
    public function load($path)
    {
        $config = include $path.$this->extension;
        if (is_array($config)) {
            return $config;
        } else {
            throw new Exception('File "'.basename($path.$this->extension).'" must contain array.');
        }
    }

    /**
     * Writes config in file.
     *
     * @param string $path The path to the file.
     *
     * @return object Adapter\Php object.
     * @throws \Lebran\Config\Exception
     */
    public function write($path)
    {
        if (!is_dir(dirname($path))) {
            throw new Exception('Folder "'.dirname($path).'" not exists.');
        }
        $config = "<?php\n    return ".$this->writeHelper($this->storage, 2).";";
        file_put_contents($path.$this->extension, $config);
        return $this;
    }

    /**
     * Compiles array into string.
     *
     * @param array $array The array of data.
     * @param int   $level Nesting level.
     *
     * @return string Compiled an array.
     */
    final protected function writeHelper(array $array, $level = 0)
    {
        $string = "[\n";
        $keys   = array_keys($array);
        foreach ($array as $key => $value) {
            $string .= str_repeat('    ', $level)
                .(is_int($key)?$key:"'$key'")." => "
                .(is_array($value)?
                    $this->writeHelper($value, $level + 1):
                    "'".(string)$value."'")
                .((end($keys) === $key)?'':',')."\n";
        }
        $string .= str_repeat('    ', $level - 1)."]";
        return $string;
    }

}