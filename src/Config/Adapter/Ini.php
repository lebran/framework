<?php
namespace Lebran\Config\Adapter;

use Lebran\Config\Adapter;
use Lebran\Config\Exception;

/**
 * It's adapter for INI config files.
 *
 * @package    Config
 * @subpackage Adapter
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Ini extends Adapter
{
    /**
     * The extension of ini configs.
     *
     * @var string
     */
    protected $extension = '.ini';

    /**
     * Parse sections or not.
     *
     * @var bool
     */
    protected $sections;

    /**
     * Initialisation.
     *
     * @param string $path
     * @param bool   $sections
     *
     * @throws \Lebran\Config\Exception
     */
    public function __construct($path = null, $sections = false)
    {
        $this->sections = $sections;
        parent::__construct($path);
    }

    /**
     * Loads ini config file and saves it to storage.
     *
     * @param string $path The path to the file.
     *
     * @return object Adapter\Ini object.
     * @throws \Lebran\Config\Exception
     */
    public function load($path)
    {
        $data = parse_ini_file($path.$this->extension, $this->sections);
        if ($data === false) {
            throw new Exception('Configuration file "'.basename($path.$this->extension).'" can\'t be loaded');
        }
        foreach ($data as $section_key => $section) {
            if (is_array($section)) {
                foreach ($section as $key => $value) {
                    $this->set($section_key.$this->delimiter.$key, $value);
                }
            } else {
                $this->set($section_key, $section);
            }
        }
        return $this;
    }

    /**
     * Sets parse sections or not.
     *
     * @param bool $sections Parse sections or not.
     *
     * @return object Adapter\Ini object.
     */
    public function parseSections($sections = false)
    {
        $this->sections = $sections;
        return $this;
    }

    /**
     * Writes config in file.
     *
     * @param string $path The path to the file.
     *
     * @return object Adapter\Ini object.
     */
    public function write($path)
    {
        $config = '';
        foreach ($this->storage as $sec_key => $sec_value) {
            if (is_array($sec_value)) {
                $name = '';
                $this->sections?$config .= '['.$sec_key.']'.PHP_EOL:$name .= $sec_key;

                foreach ($this->writeHelper($name, $sec_value) as $key => $value) {
                    $config .= $key.'="'.(string)$value.'"'.PHP_EOL;
                }
            } else {
                $config .= $sec_key.'="'.(string)$sec_value.'"'.PHP_EOL;;
            }
        }
        file_put_contents($path.$this->extension, $config);
        return $this;
    }

    /**
     * Recursively converts into a single array.
     *
     *      $this->setHelper('test', $test);
     *
     *      Incoming array:
     *          [
     *              "level11" => "value1",
     *              "level12" => [
     *                  "level21" => "value2"
     *              ]
     *          ]
     *
     *      Outgoing array:
     *          [
     *              "test.level11" => "value1",
     *              "test.level12.level21" => "value2",
     *          ]
     *
     * @param array  $array Convertible array.
     * @param string $name  The name that will be added at the beginning.
     *
     * @return array Processed array.
     */
    final protected function writeHelper($name, array $array)
    {
        $temp = [];
        foreach ($array as $key => $value) {
            $new_name = (string)(($name !== '')?$name.$this->delimiter.$key:$key);
            if (is_array($value)) {
                $temp = array_merge($temp, $this->writeHelper($new_name, $value));
            } else {
                $temp[$new_name] = $value;
            }
        }
        return $temp;
    }


}