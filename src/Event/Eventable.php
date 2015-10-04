<?php
namespace Lebran\Event;

/**
 * It's trait help you realize EventableInterface.
 *
 * @package    Event
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
trait Eventable
{
    /**
     * Storage for internal event manager.
     *
     * @var object
     */
    protected $em;

    /**
     * Sets the events manager.
     *
     * @param object $em Event manager object.
     *
     * @return object
     */
    public function setEventManager($em)
    {
        $this->em = $em;
        return $this;
    }

    /**
     * Gets the internal event manager
     *
     * @return object Manager object.
     */
    public function getEventManager()
    {
        return $this->em;
    }
}