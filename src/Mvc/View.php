<?php
/**
 * Created by PhpStorm.
 * User: mindkicker
 * Date: 17.08.15
 * Time: 15:16
 */

namespace Lebran\Mvc;

use \Lebran\Utils\Storage;

class View extends Storage
{
    protected $extensions = array();
    protected $methods = array();
    protected $template;
    protected $dir;

    public function __construct(array $extensions = array()){
        foreach ($extensions as $extension) {
            $this->extensions[$extension->getName()] = $extension;
            foreach ($extension->getMethods() as $key => $value){
                $this->methods[$key] = array($extension, $value);
            }
        }
    }

    public function registerDir($dir)
    {
        $this->dir = rtrim(trim($dir), '/').'/';
    }

    public function render($template, array $params = array())
    {
        $this->template = $template;
        $this->storage  = array_merge_recursive($this->storage, $params);

        extract($this->storage);
        ob_start();
        include $this->dir.$this->template.'.php';

        if(array_key_exists('inheritance', $this->extensions)) {
            while (!empty($this->extensions['inheritance']->parent)) {
                include $this->dir.array_pop($this->extensions['inheritance']->parent).'.php';
            }
        }
        return ob_get_clean();
    }



    protected function import($filename){
        extract($this->storage, EXTR_SKIP);
        include $this->dir.$filename.'.php';
    }

    public function __call($method, $parameters)
    {
        if(array_key_exists($method, $this->methods)){
            call_user_func_array($this->methods[$method], $parameters);
        }
    }

    public function enableShortTags()
    {
        $_this = $this;
        $this->storage += [
            'extend' => function($parent) use($_this){
                $_this->extend($parent);
            },
            'block' => function($name) use($_this){
                $_this->block($name);
            },
            'endblock' => function() use($_this){
                $_this->endblock();
            },
            'output' => function($name) use($_this){
                $_this->output($name);
            },
            'parent' => function() use($_this){
                $_this->parent();
            },
            'import' => function($filename) use($_this){
                $_this->import($filename);
            }
        ];
    }
}