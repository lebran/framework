<?php
namespace Lebran\Event;

/**
 * This interface must for those classes that need EventsManager and fire events.
 *
 */
interface EventableInterface
{
    /**
     * Sets the events manager.
     *
     * @param Manager $em Manager object.
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