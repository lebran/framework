<?php
/**
 * Created by PhpStorm.
 * User: mindkicker
 * Date: 15.07.15
 * Time: 13:16
 */

namespace Framework\Config;

abstract class Adapter implements \ArrayAccess, \Serializable
{
    /**
     * Массив елементов ArrayObject.
     *
     * @var array
     */
    protected $configs = array();

    /**
     * @var string
     */
    protected $extension = '';

    /**
     *
     * @param $path
     */
    public function __construct($path)
    {
        if (is_file($path.$this->extension)) {
            $this->configs = $this->load($path);
        } else {
            throw new Exception('File "'.basename($path.$this->extension).'" not found.');
        }
    }

    /**
     *
     * @param $path
     *
     * @return mixed
     */
    abstract public function load($path);

    /**
     *
     * @param $path
     *
     * @return mixed
     */
    abstract public function write($path);

    /**
     * @param Adapter $config
     *
     * @return $this
     */
    public function merge(Adapter $config)
    {
        $this->configs = array_merge_recursive($this->configs, $config->toArray());
        return $this;
    }

    /**
     *
     *
     * @return array
     */
    public function toArray()
    {
        return $this->configs;
    }/** @noinspection PhpUndefinedClassInspection */

    /**
     * @param      $offset
     * @param null $default
     *
     * @return null|type
     */
    public function get($offset, $default = null)
    {
        return $this->offsetExists($offset)?$this->offsetGet($offset):$default;
    }

    /**
     *
     *
     * @param type $offset
     *
     * @return type
     */
    public function offsetExists($offset)
    {
        return (bool)$this->getHelper($offset);
    }

    /**
     *
     * @param type $offset
     *
     * @return type
     */
    public function offsetGet($offset)
    {
        return $this->getHelper($offset);
    }

    /**
     *
     * @param type $offset
     * @param type $value
     */
    public function offsetSet($offset, $value)
    {
        $segments = explode('.', $offset);
        $group    = &$this->configs;
        foreach ($segments as $segment => $val) {
            if ($segment == count($segments) - 1) {
                $group[$val] = $value;
            } else {
                $group = &$group[$val];
            }
        }
    }

    /**
     *
     * @param type $offset
     *
     * @return type
     */
    public function offsetUnset($offset)
    {
        if($this->offsetExists($offset)) {
            $segments = explode('.', $offset);
            $group    = &$this->configs;
            foreach ($segments as $segment => $value) {
                if ($segment == count($segments) - 1) {
                    unset($group[$value]);
                } else {
                    $group = &$group[$value];
                }
            }
        } else {
            throw new Exception('"'.$offset.'" cannot be removed because not found.');
        }
    }

    /**
     *
     * @return type
     */
    public function serialize()
    {
        return serialize($this->configs);
    }

    /**
     *
     * @param type $serialized
     */
    public function unserialize($serialized)
    {
        $this->configs = unserialize($serialized);
    }

    final protected function getHelper($offset)
    {
        $segments = explode('.', $offset);
        $group    = &$this->configs;
        foreach ($segments as $segment => $value) {
            if (isset($group[$value])) {
                if ($segment == count($segments) - 1) {
                    return $group[$value];
                } else {
                    $group = &$group[$value];
                }
            } else {
                return null;
            }
        }
    }
}