<?php
namespace Lebran\Utils;

/**
 * Lebran\Di\Storage it's a helper for storage data. Allows using the array syntax.
 * Implementing \Countable, \Serializable, \JsonSerializable, \IteratorAggregate.
 * It is used notation to access both multidimensional arrays.
 *
 *                                  Example
 * <code>
 *      $storage = new \Lebran\Utils\Storage();
 *      $storage['key'] = ['key1' => 'value1', ['key2' => value2, 'value3']];
 *      echo $storage['key.0.key2'];
 * </code>
 *
 * @package    Utils
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Storage implements \ArrayAccess, \IteratorAggregate, \Countable, \Serializable
{

    /**
     * The storage of data.
     *
     * @var array
     */
    protected $storage = [];

    /**
     * The notation for storage access.
     *
     * @var string
     */
    protected $delimiter = '.';

    /**
     * Initialisation.
     *
     * @param array $data The data for storage.
     */
    public function __construct(array $data = [])
    {
        $this->storage = $data;
    }

    /**
     * Merge of storages.
     *
     * @param Storage $storage Another storage.
     *
     * @return object Storage object
     */
    public function merge(Storage $storage)
    {
        $this->storage = array_merge_recursive($this->storage, $storage->toArray());
        return $this;
    }

    /**
     * Sets the value by key.
     *
     * @param mixed $key   The key to set.
     * @param mixed $value The value to set.
     *
     * @return object Storage object
     */
    public function set($key, $value)
    {
        if (is_null($key)) {
            $this->storage[] = $value;
        } else {
            $segments = explode($this->delimiter, $key);
            $group    = &$this->storage;
            foreach ($segments as $segment => $val) {
                if ($segment == count($segments) - 1) {
                    $group[$val] = $value;
                } else {
                    !(isset($group[$val]) && !is_array($group[$val]))?:$group[$val] = [];
                    $group = &$group[$val];
                }
            }
        }
        return $this;
    }

    /**
     * Gets the value by key.
     *
     * @param mixed $key     The key to get.
     * @param mixed $default Value that will be sent if no search results.
     *
     * @return mixed Searches values or default.
     */
    public function get($key, $default = null)
    {
        $segments = explode($this->delimiter, $key);
        $group    = &$this->storage;
        foreach ($segments as $segment => $value) {
            if (isset($group[$value])) {
                if ($segment == count($segments) - 1) {
                    return $group[$value];
                } else {
                    $group = &$group[$value];
                }
            } else {
                break;
            }
        }
        return $default;
    }

    /**
     * Whether a value by key exists.
     *
     * @param mixed $key The key to check for.
     *
     * @return boolean True on success or false on failure.
     */
    public function has($key)
    {
        return is_null($this->get($key))?false:true;
    }

    /**
     * Removes the value by key.
     *
     * @param mixed $key The key to remove.
     *
     * @return object Storage object
     */
    public function remove($key)
    {
        $segments = explode($this->delimiter, $key);
        $group    = &$this->storage;
        foreach ($segments as $segment => $value) {
            if (isset($group[$value])) {
                if ($segment == count($segments) - 1) {
                    unset($group[$value]);
                } else {
                    $group = &$group[$value];
                }
            } else {
                break;
            }
        }
        return $this;
    }

    /**
     * Gets storage as array.
     *
     * @return array Storage data.
     */
    public function toArray()
    {
        return $this->storage;
    }

    /**
     * Whether a offset exists.
     *
     * @param mixed $offset An offset to check for.
     *
     * @return boolean True on success or false on failure.
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve.
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set.
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset.
     *
     * @param mixed $offset The offset to unset.
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * String representation of object.
     *
     * @return string The string representation of the object.
     */
    public function serialize()
    {
        return serialize($this->storage);
    }

    /**
     * Constructs the object.
     *
     * @param string $serialized The string representation of the object.
     *
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->storage = unserialize($serialized);
    }

    /**
     * Counts elements of an storage.
     *
     * @return int The count of storage elements.
     */
    public function count()
    {
        return count($this->storage);
    }

    /**
     * Retrieve an external iterator.
     *
     * @return \ArrayIterator An instance of an object implementing Traversable.
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->storage);
    }
}