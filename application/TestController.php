<?php
namespace Lebran\App;

use Lebran\Di\Container;

/**
 * Description of TestController
 *
 * @author Roma
 */
class TestController //implements \Lebran\Di\InjectionInterface
{
    public $test = 'Privet';

    public function __construct($param1 = '', $param2 = '')
    {
        echo $param1.'    '.$param2;
    }

    public function test($param)
    {
        echo ($this->test = $param);
    }

    public function setDi(Container $di)
    {
        foreach ($di->get('autoloader')->getNamespaces() as $key => $value) {
            echo $key.' => '.$value;
        }
    }

    public function getDi()
    {
        return 'Helloaadsa';
    }

    public function __toString()
    {
        return $this->test;
    }
}