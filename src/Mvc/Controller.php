<?php
namespace Lebran\Mvc;

class Controller
{
    protected $di;

    final public function __construct($di)
    {
        $this->di = $di;
        if (method_exists($this, 'initialize')) {
           $this->initialize();
        }
    }

    public function __get($service)
    {
        return $this->di->get($service);
    }
}