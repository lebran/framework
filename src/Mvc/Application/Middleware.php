<?php
namespace Lebran\Mvc\Application;

/**
 * Middleware provide a convenient mechanism for filtering
 * HTTP requests entering your application.
 *
 * @package    Mvc
 * @subpackage Application
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
abstract class Middleware
{
    /**
     * @var object Reference to the next middleware.
     */
    protected $next;

    /**
     * Perform actions specific to this middleware
     * and optionally call the next.
     *
     * @return mixed Response.
     */
    abstract public function call();

    /**
     * Sets next middleware.
     *
     * This method injects the next middleware into
     * this middleware so that it may optionally
     * be called when appropriate.
     *
     * @param object $next Middleware object.
     */
    public function setNext($next)
    {
        $this->next = $next;
    }

    /**
     * Gets next middleware.
     *
     * This method retrieves the next middleware
     * previously injected into this middleware.
     *
     * @return object $next Middleware object.
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Calls next middleware in queue.
     *
     * @return object Response object.
     */
    protected function next()
    {
        return $this->next->call();
    }
}