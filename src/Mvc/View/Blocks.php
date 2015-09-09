<?php
namespace Lebran\Mvc\View;

/**
 * By combining layouts and blocks, allows you to “build up” your pages using predefined blocks.
 *
 * @package    Mvc
 * @subpackage View
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Blocks implements ExtensionInterface
{
    /**
     * @var array Stack for blocks.
     */
    protected $stack = [];

    /**
     * @var array An array of blocks content.
     */
    protected $blocks = [];

    /**
     * @var array Stack for parent hashes.
     */
    protected $parents = [];

    /**
     * Gets the name of extension.
     *
     * @return string Extension name.
     */
    public function getName()
    {
        return 'blocks';
    }

    /**
     * Gets array: alias => method name.
     *
     * @return array An array of methods name.
     */
    public function getMethods()
    {
        return [
            'block'    => 'block',
            'endblock' => 'endblock',
            'output'   => 'output',
            'parent'   => 'parent'
        ];
    }

    /**
     * Start a new section block.
     *
     * @param string $name The name of block.
     */
    public function block($name)
    {
        $this->stack[] = $name;
        ob_start();
    }

    /**
     * End the last section block.
     *
     * @param bool $last True - print block.
     */
    public function endblock($last = false)
    {
        $name = array_pop($this->stack);
        $view = ob_get_clean();
        if (array_key_exists($name, $this->parents) && array_key_exists($name, $this->blocks)) {
            $this->blocks[$name] = $view = str_replace($this->parents[$name], $view, $this->blocks[$name]);
        }

        if (array_key_exists($name, $this->blocks)) {
            if ($last) {
                echo $this->blocks[$name];
            }
        } else {
            if ($last) {
                echo $view;
            } else {
                $this->blocks[$name] = $view;
            }
        }
    }

    /**
     * Returns the content for a section block.
     *
     * @param string $name    The name of block.
     * @param string $default Default block content.
     */
    public function output($name, $default = '')
    {
        if (array_key_exists($name, $this->blocks)) {
            echo $this->blocks[$name];
        } else {
            echo $default;
        }
    }

    /**
     * Returns the parent block content.
     *
     * @return void
     */
    public function parent()
    {
        $key = end($this->stack);
        echo $this->parents[$key] = hash('sha256', $key);
    }
}