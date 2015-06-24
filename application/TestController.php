<?php
namespace Leaf\App;

/**
 * Description of TestController
 *
 * @author Roma
 */
class TestController implements \Leaf\Di\InjectionInterface
{
    public $test = 'Privet';

    public function test($param)
    {
        echo ($this->test = $param);
    }

    public function setDi($di)
    {
        foreach ($di->get('autoloader')->getNamespaces() as $key => $value) {
            echo $key.' => '.$value;
        }
    }

    public function getDi()
    {
        return 'Helloaadsa';
    }
}