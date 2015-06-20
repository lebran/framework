<?php
namespace Leaf\App\Middleware;

use Leaf\Core\Mvc\Middleware;

/**
 * Description of TestMiddlewares
 *
 * @author Roma
 */
class TestMiddleware extends Middleware
{
    public function call()
    {
        $response = $this->next->call();
        
        $response->setBody('Middleware работает йоу йоу йоу!!!');

        return $response;
    }
}