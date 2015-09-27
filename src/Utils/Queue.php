<?php
namespace Lebran\Utils;

/**
 * Priority queue, support elements with the same priority.
 *
 * @package    Utils
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Queue extends \SplPriorityQueue
{
    /**
     * @var int A very large number.
     */
    protected $serial = PHP_INT_MAX;

    /**
     * Inserts an element in the queue by sifting it up.
     *
     * @param mixed $value    The value to insert.
     * @param int   $priority The associated priority.
     */
    public function insert($value, $priority = 0)
    {
        parent::insert($value, array($priority, $this->serial--));
    }

    /**
     * Gets data as an array.
     *
     * @return array An array of data.
     */
    public function toArray()
    {
        $data = [];
        foreach ($this as $value) {
            $data[] = $value;
        }

        return $data;
    }
}