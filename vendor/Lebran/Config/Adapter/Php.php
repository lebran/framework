<?php
/**
 * Created by PhpStorm.
 * User: mindkicker
 * Date: 15.07.15
 * Time: 13:49
 */

namespace Framework\Config\Adapter;

use Framework\Config\Adapter;
use Framework\Config\Exception;

class Php extends Adapter
{
    protected $extension = '.php';

    /**
     *
     * @param $path
     *
     * @return mixed
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
     *
     * @param $path
     *
     * @return mixed
     */
    public function write($path)
    {

    }
}