<?php
namespace Lebran\Di;

/**
 * It's trait help you realize InjectableInterface.
 *
 * @package    Di
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
trait Injectable
{
    /**
     * Store for di container.
     *
     * @var
     */
    protected $di;

    /**
     * Sets the dependency injection container.
     *
     * @param object $di Container object.
     *
     * @return void
     */
    public function setDi($di)
    {
        $this->di = $di;
    }

    /**
     * Returns the dependency injection container.
     *
     * @return object Container object.
     */
    public function getDi()
    {
        return $this->di;
    }
}