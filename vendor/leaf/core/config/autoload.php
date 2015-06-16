<?php
    return array(
        'classes' => array(
            // Core
            'Leaf\\Core\\Leaf' => CORE_SRC_PATH.'Leaf.php',
            'Leaf\\Core\\Request' => CORE_SRC_PATH.'Request.php',
            'Leaf\\Core\\Route' => CORE_SRC_PATH.'Route.php',
            'Leaf\\Core\\Response' => CORE_SRC_PATH.'Response.php',
            'Leaf\\Core\\Exception' => CORE_SRC_PATH.'Exception.php',
            'Leaf\\Core\\Controller' => CORE_SRC_PATH.'Controller.php',
            'Leaf\\Core\\Model' => CORE_SRC_PATH.'Model.php',

            // Core\Config
            'Leaf\\Core\\Config\\Exception' => CORE_SRC_PATH.'Config'.DS.'Exception.php',
            'Leaf\\Core\\Config\\ConfigInterface' => CORE_SRC_PATH.'Config'.DS.'ConfigInterface.php',
            'Leaf\\Core\\Config\\Php' => CORE_SRC_PATH.'Config'.DS.'Php.php',
            
            // Core\Utils
            'Leaf\\Core\\Utils\\Arr' => CORE_SRC_PATH.'Utils'.DS.'Arr.php',
            'Leaf\\Core\\Utils\\View' => CORE_SRC_PATH.'Utils'.DS.'View.php',
            'Leaf\\Core\\Utils\\Layout' => CORE_SRC_PATH.'Utils'.DS.'Layout.php',
            'Leaf\\Core\\Utils\\Html' => CORE_SRC_PATH.'Utils'.DS.'Html.php',
            'Leaf\\Core\\Utils\\Cookie' => CORE_SRC_PATH.'Utils'.DS.'Cookie.php',
            'Leaf\\Core\\Utils\\Validator' => CORE_SRC_PATH.'Utils'.DS.'Validator.php'
        ),
        'aliases' => array()
    );

