<?php

register_shutdown_function(
    function () {
        echo \Leaf\Debug\Controller\ToolbarController::render();
    }
);

function vard($var)
{
    echo \Leaf\Debug\Variable::dump($var);
}

function tmsg($msg)
{
    \Leaf\Debug\Controller\ToolbarController::msg($msg);
}