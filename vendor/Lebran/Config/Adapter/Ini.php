<?php
namespace Lebran\Config\Adapter;

use Lebran\Config\Adapter;

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
     * Loads ini config file.
     *
     * @param string $path The path to the file.
     *
     * @return array Config file as array.
     */
    public function load($path)
    {
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
    }
}