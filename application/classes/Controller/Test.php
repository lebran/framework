<?php
/**
 * Тестовый контроллер
 *
 * @author iToktor
 */
class Controller_Test extends Controller_Layout{
    public $render = FALSE;
    
    public function hello_world() {
        $view = View::make('hello_world')->set('hello_world', 'Hello World!!!')->render();
        $this->response->body($view);
    }
}
