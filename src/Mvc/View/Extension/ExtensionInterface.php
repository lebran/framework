<?php
namespace Lebran\Mvc\View\Extension;

/**
 * A common interface for extensions.
 *
 * @package    Mvc
 * @subpackage View
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
interface ExtensionInterface
{
    /**
     * Gets the name of extension.
     *
     * @return string Extension name.
     */
    public function getName();

    /**
     * Gets array: alias => method name.
     *
     * @return array An array of methods name.
     */
    public function getMethods();
}