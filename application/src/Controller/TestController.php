<?php
namespace Easy\App\Controller;

use Easy\Core\Utils\Layout;
use Easy\Core\View;

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
        \Toolbar::msg($_SERVER);
    }
}
