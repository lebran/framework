<?php
namespace Lebran\Mvc\View;

class Blocks implements ExtensionInterface
{
    protected $stack = array();
    protected $blocks = array();
    protected $parents = array();

    public function getName()
    {
        return 'blocks';
    }

    public function getMethods()
    {
        return array(
            'block' => 'block',
            'endblock' => 'endblock',
            'output' => 'output',
            'parent' => 'parent'
        );
    }

    public function block($name)
    {
        $this->stack[] = $name;
        ob_start();
    }

    public function endblock($last = false)
    {
        $name = array_pop($this->stack);
        $view = ob_get_clean();
        if (isset($this->parents[$name], $this->blocks[$name])) {
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

    public function output($name)
    {
        if (array_key_exists($name, $this->blocks)) {
            echo $this->blocks[$name];
        }
    }

    public function parent()
    {
        $key = end($this->stack);
        echo $this->parents[$key] = hash('sha256', $key);
    }
}