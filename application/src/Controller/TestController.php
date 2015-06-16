<?php
namespace Leaf\App\Controller;

use Leaf\Core\Mvc\View;
use Leaf\Core\Mvc\Layout;

/**
 * Тестовый контроллер
 *
 * @author iToktor
 */
class TestController extends Layout
{    
    public function helloWorldAction()
    {
        $view = View::make('views/hello_world')->set('hello_world', 'Hello World!!!')->render();
        $this->layout->index = $view;
    }
}
