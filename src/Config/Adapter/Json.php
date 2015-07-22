<?php
/**
 * Created by PhpStorm.
 * User: mindkicker
 * Date: 22.07.15
 * Time: 14:15
 */

namespace Lebran\Config\Adapter;

use Lebran\Config\Adapter;
use Lebran\Config\Exception;

/**
 * It's adapter for JSON config files.
 *
 * @package    Config
 * @subpackage Adapter
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Json extends Adapter
{
    /**
     * The extension of json configs.
     *
     * @var string
     */
    protected $extension = '.json';

    /**
     * Loads json config file and saves it to storage.
     *
     * @param string $path The path to the file.
     *
     * @return object Adapter\Json object.
     * @throws \Lebran\Config\Exception
     */
    public function load($path)
    {
        if (!($json = json_decode(file_get_contents($path.$this->extension), true ))) {
            throw new Exception(json_last_error_msg(), json_last_error());
        }
        $this->storage = array_merge_recursive($this->storage, $json);
        return $this;
    }

    /**
     * Writes config in file.
     *
     * @param string $path The path to the file.
     *
     * @return object Adapter\Json object.
     * @throws \Lebran\Config\Exception
     */
    public function write($path)
    {
        if (!($json = json_encode($this->storage, JSON_PRETTY_PRINT))) {
            throw new Exception(json_last_error_msg(), json_last_error());
        }
        file_put_contents($path.$this->extension, $json);
        return $this;
    }
}