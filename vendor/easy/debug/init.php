<?php
namespace Easy\Debug;

use Easy\Debug\Controller\ToolbarController;

register_shutdown_function(function(){
    echo ToolbarController::render();
});