<?php
namespace Lebran\Event;

/**
 * Created by PhpStorm.
 * User: mindkicker
 * Date: 27.07.15
 * Time: 15:08
 */
class Manager
{
    protected $listeners = [];


    public function attach($name, $listener)
    {
        $this->listeners[$name] = $listener;
    }

    public function fire($name, $object, array $data = null)
    {
        $listeners = [$name];
        while (($pos = strrpos($name, '.'))) {
            $name = $listeners[] = substr($name, 0, $pos);
        }

        foreach ($listeners as $listener) {
            if (!empty($this->listeners[$listener])) {
                $this->fireHelper($listener, $object, $data);
            }
        }
    }

    protected function fireHelper($listener, $object, $data)
    {
        if (is_callable($this->listeners[$listener])) {
            call_user_func_array($this->listeners[$listener], [$object, $data]);
        } else if (is_array()) {
        }
    }
}