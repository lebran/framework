<?php
namespace Lebran\Config;

use Lebran\Utils\Storage;

/**
 * Lebran\Config it's a component that provides easy access to the any configs using adapters.
 * If you want to use other types of configs, you need to create an adapter and extends this class.
 *
 * @package    Config
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
abstract class Adapter extends Storage
{
    /**
     * The extension of configs.
     *
     * @var string
     */
    protected $extension = '';

    /**
     * Initialisation.
     *
     * @param string $path The path to the file.
     *
     * @throws \Lebran\Config\Exception
     */
    public function __construct($path)
    {
        if (is_file($path.$this->extension)) {
            parent::__construct($this->load($path));
        } else {
            throw new Exception('File "'.basename($path.$this->extension).'" not found.');
        }
    }

    /**
     * Loads config file.
     *
     * @param string $path The path to the file.
     *
     * @return array Config file as array.
     */
    abstract public function load($path);

    /**
     * Writes config in file.
     *
     * @param string $path The path to the file.
     *
     * @return object Adapter object.
     */
    abstract public function write($path);
}