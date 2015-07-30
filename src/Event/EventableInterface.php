<?php
namespace Lebran\Event;

/**
 * This interface must for those classes that need EventsManager and fire events.
 *
 * @package    Event
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
interface EventableInterface
{
    /**
     * Sets the events manager.
     *
     * @param Manager $em Event manager object.
     *
     * @return void
     */
    public function setEventManager(Manager $em);

    /**
     * Gets the internal event manager
     *
     * @return object Manager object.
     */
    public function getEventManager();
}