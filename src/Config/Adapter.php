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
     * Initialisation.
     *
     * @param string $path The path to the file.
     * @param array  $data The data for storage.
     */
    public function __construct($path = null, array $data = [])
    {
        parent::__construct($data);

        if (!$path) {
            $this->load($path);
        }
    }

    /**
     * Loads config file and saves it to storage.
     *
     * @param string $path The path to the file.
     *
     * @return object Adapter object.
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