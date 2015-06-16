<?php
namespace Leaf\Debug;

use Leaf\Debug\Controller\ToolbarController;

register_shutdown_function(function(){
    echo ToolbarController::render();
});