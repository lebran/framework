<?php
namespace Lebran\Mvc\View\Extension;

/**
 * Template helper.
 *
 * @package    Mvc
 * @subpackage View
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Helper implements ExtensionInterface
{

    /**
     * Gets the name of extension.
     *
     * @return string Extension name.
     */
    public function getName()
    {
        return 'helper';
    }

    /**
     * Gets array: alias => method name.
     *
     * @return array An array of methods name.
     */
    public function getMethods()
    {
        return [
            '_' => 'escape'
        ];
    }

    public function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}