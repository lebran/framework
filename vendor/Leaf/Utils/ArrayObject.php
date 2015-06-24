<?php
namespace Leaf\Utils;

/**
 * Данный класс позволяет работать с объектами как с массивами.
 *
 * @package    Utils
 * @version    2.1
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class ArrayObject implements \ArrayAccess, \Iterator, \Countable, \Serializable
{
    /**
     * Массив елементов ArrayObject.
     *
     * @var array
     */
    protected $array = array();

    /**
     * Разделитель.
     *
     * @var string
     */
    protected $delimiter = '.';

    /**
     * Возвращает количество елементов ArrayObject.
     *
     * @return int Количество елементов.
     */
    public function count()
    {
        return count($this->array);
    }

    /**
     * Возвращает итератор на первый элемент.
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->array);
    }

    /**
     * Проверяет, является ли текущий елемент концом.
     *
     * @return bool Конец ли массива?
     */
    public function valid()
    {
        return key($this->array) !== null;
    }

    /**
     * Возвращает текущий элемент ArrayObject.
     *
     * @return mixed Текущий елемент.
     */
    public function current()
    {
        return current($this->array);
    }

    /**
     * Переходит к следующему элементу.
     *
     * @return void
     */
    public function next()
    {
        next($this->array);
    }

    /**
     * Возвращает ключ текущего элемента ArrayObject.
     *
     * @return string Ключ текущего элемента.
     */
    public function key()
    {
        return key($this->array);
    }

    /**
     *
     * @param type $offset
     * @param type $action
     * @return boolean
     */
    final protected function _get($offset, $action = 'get')
    {
        $segments = explode($this->delimiter, $offset);
        $group = &$this->array;
        foreach ($segments as $segment => $val) {
            if (isset($group[$val])) {
                if ($segment == count($segments) - 1) {
                    switch ($action) {
                        case 'unset':
                            unset($group[$val]);
                            return true;
                        case 'exists':
                            return true;
                        case 'get':
                        default:
                            return $group[$val];
                    }
                } else {
                    $group = &$group[$val];
                }
            } else {
                return false;
            }
        }
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
        return $this->_get($offset, 'exists');
    }

    /**
     *
     * @param type $offset
     * @return type
     */
    public function offsetGet($offset)
    {
        return $this->_get($offset,'get');
    }

    /**
     *
     * @param type $offset
     * @param type $value
     */
    public function offsetSet($offset, $value)
    {
        $segments = explode($this->delimiter, $offset);
        $group    = &$this->array;
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
     * @return type
     */
    public function offsetUnset($offset)
    {
        return $this->_get($offset, 'unset');
    }

    /**
     *
     * @return type
     */
    public function serialize()
    {
        return serialize($this->array);
    }

    /**
     *
     * @param type $serialized
     */
    public function unserialize($serialized)
    {
        $this->array = unserialize($serialized);
    }

    /**
     * Рекурсивно конвертирует массив в одиночный с заданным разделителем.
     *
     *      $this->asAnnotation($test, 'test', '.');
     *
     *                  Входящий массив:
     *      array(
     *          "level11" => "value1",
     *          "level12" => array(
     *                          "level21" => "value2"
     *                         )
     *      )
     *
     *                 Исходящий массив:
     *      array(
     *          "test.level11" => "value1",
     *          "test.level12.level21" => "value2",
     *      )
     *
     * @param array  $arr   Конвертируемый массив.
     * @param string $name  Имя, которое добавится в начале.
     * @param string $delim Разделитель.
     *
     * @return array
     */
    protected function asAnnotation(array $arr, $name = false, $delim = '.')
    {
        $array = array();
        foreach ($arr as $key => $value) {
            $arr_name = ($name)?$name.$delim.$key:$key;
            is_array($value)?$array += $this->asAnnotation($value, $arr_name, $delim):$array[$arr_name] = $value;
        }

        return $array;
    }
}