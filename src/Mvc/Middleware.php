<?php
namespace Lebran\Mvc;

abstract class Middleware
{
    protected $next;

    abstract public function call();

    public function setNext($next)
    {
        $this->next = $next;
    }

    public function getNext()
    {
        return $this->next;
    }
}