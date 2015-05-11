<?php
namespace Easy\App\Controller;

use Easy\Core\Utils\Layout;
use Easy\Core\Utils\View;

/**
 * Тестовый контроллер
 *
 * @author iToktor
 */
class TestController extends Layout{    
    public function helloWorldAction() {
        $view = View::make('hello_world')->set('hello_world', 'Hello World!!!')->render();
        $this->layout->index = $view;
    }
}
