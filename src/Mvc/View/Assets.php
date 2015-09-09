<?php
namespace Lebran\Mvc\View;

/**
 * Class Assets
 *
 * @package    Mvc
 * @subpackage View
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Assets implements ExtensionInterface
{
    /**
     * Initialisation. Sets folder.
     *
     * @param string $folder Path to folder.
     */
    public function __construct($folder)
    {
        $this->folder = rtrim(trim($folder), '/').'/';
    }

    /**
     * Gets the name of extension.
     *
     * @return string Extension name.
     */
    public function getName()
    {
        return 'assets';
    }

    /**
     * Gets array: alias => method name.
     *
     * @return array An array of methods name.
     */
    public function getMethods()
    {
        return [
            'css' => 'css'
        ];
    }

    /**
     *
     *
     * @return string
     */
    public function css()
    {
    }
}