<?php
    return array(
        'classes' => array(
            // Core\Http
            'Leaf\\Core\\Http\\Request' => CORE_SRC_PATH.'Http'.DS.'Request.php',
            'Leaf\\Core\\Http\\Router' => CORE_SRC_PATH.'Http'.DS.'Route.php',
            'Leaf\\Core\\Http\\Response' => CORE_SRC_PATH.'Http'.DS.'Response.php',
            'Leaf\\Core\\Http\\HttpException' => CORE_SRC_PATH.'Http'.DS.'HttpException.php',

            // Core\Mvc
            'Leaf\\Core\\Mvc\\Controller' => CORE_SRC_PATH.'Mvc'.DS.'Controller.php',
            'Leaf\\Core\\Mvc\\Model' => CORE_SRC_PATH.'Mvc'.DS.'Model.php',
            'Leaf\\Core\\Mvc\\Layout' => CORE_SRC_PATH.'Mvc'.DS.'Layout.php',
            'Leaf\\Core\\Mvc\\Middleware' => CORE_SRC_PATH.'Mvc'.DS.'Middleware.php',
            'Leaf\\Core\\Mvc\\ViewException' => CORE_SRC_PATH.'Mvc'.DS.'ViewException.php',
            'Leaf\\Core\\Mvc\\View' => CORE_SRC_PATH.'Mvc'.DS.'View.php',

            // Core\Config
            'Leaf\\Core\\Config\\Config' => CORE_SRC_PATH.'Config'.DS.'Config.php',
            'Leaf\\Core\\Config\\ConfigException' => CORE_SRC_PATH.'Config'.DS.'ConfigException.php',
            'Leaf\\Core\\Config\\Driver\\DriverInterface' => CORE_SRC_PATH.'Config'.DS.'Driver'.DS.'DriverInterface',
            'Leaf\\Core\\Config\\Driver\\DriverPhp' => CORE_SRC_PATH.'Config'.DS.'Driver'.DS.'DriverPhp',
            
            // Core\Utils
            'Leaf\\Core\\Utils\\Arr' => CORE_SRC_PATH.'Utils'.DS.'Arr.php',
            'Leaf\\Core\\Utils\\Html' => CORE_SRC_PATH.'Utils'.DS.'Html.php',
            'Leaf\\Core\\Utils\\Cookie' => CORE_SRC_PATH.'Utils'.DS.'Cookie.php',
            'Leaf\\Core\\Utils\\Finder' => CORE_SRC_PATH.'Utils'.DS.'Finder.php'
        ),
        'aliases' => array()
    );

