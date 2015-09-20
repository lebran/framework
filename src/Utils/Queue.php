<?php
/**
 * Created by PhpStorm.
 * User: Roma
 * Date: 19.09.2015
 * Time: 23:41
 */

namespace Lebran\Utils;

class Queue extends \SplPriorityQueue
{
    protected $serial = PHP_INT_MAX;

    public function insert($value, $priority = 0)
    {
        parent::insert($value, array($priority, $this->serial--));
    }

    public function toArray()
    {
        foreach ($this as $value) {
            $data[] = $value;
        }

        return $data;
    }
}