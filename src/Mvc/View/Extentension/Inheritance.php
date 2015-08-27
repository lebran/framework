<?php
/**
 * Created by PhpStorm.
 * User: mindkicker
 * Date: 27.08.15
 * Time: 17:11
 */

namespace Lebran\Mvc\View\Extentension;

class Inheritance
{
    protected $stack = array();
    protected $blocks = array();
    protected $parents = array();
    public $parent = array();

    public function getName()
    {
        return 'inheritance';
    }

    public function getMethods()
    {
        return array(
            'extend' => 'extend',
            'block' => 'block',
            'endblock' => 'endblock',
            'output' => 'output',
            'parent' => 'parent'
        );
    }

    public function extend($parent)
    {
        $this->parent[] = $parent;
    }

    public function block($name)
    {
        $this->stack[] = $name;
        ob_start();
    }

    public function endblock()
    {
        $name = array_pop($this->stack);
        $view = ob_get_clean();
        if (isset($this->parents[$name]) && isset($this->blocks[$name])) {
            $this->blocks[$name] = $view = str_replace($this->parents[$name], $view, $this->blocks[$name]);
        }

        if (isset($this->blocks[$name])) {
            if (empty($this->parent)) {
                echo $this->blocks[$name];
            }
        } else {
            if (!empty($this->parent)) {
                $this->blocks[$name] = $view;
            } else {
                echo $view;
            }
        }
    }

    public function output($name)
    {
        if (isset($this->blocks[$name])) {
            echo $this->blocks[$name];
        }
    }

    public function parent()
    {
        $key = end($this->stack);
        echo $this->parents[$key] = hash('sha256', $key);
    }
}