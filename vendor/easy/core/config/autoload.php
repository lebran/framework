<?php
    return array(
        'classes' => array(
            // Core
            'Easy\\Core\\Easy' => CORE_SRC_PATH.'Easy.php',
            'Easy\\Core\\Request' => CORE_SRC_PATH.'Request.php',
            'Easy\\Core\\Route' => CORE_SRC_PATH.'Route.php',
            'Easy\\Core\\Response' => CORE_SRC_PATH.'Response.php',
            'Easy\\Core\\Exception' => CORE_SRC_PATH.'Exception.php',
            'Easy\\Core\\Controller' => CORE_SRC_PATH.'Controller.php',
            'Easy\\Core\\Model' => CORE_SRC_PATH.'Model.php',

            // Core\Config
            'Easy\\Core\\Config\\Exception' => CORE_SRC_PATH.'Config'.DS.'Exception.php',
            'Easy\\Core\\Config\\ConfigInterface' => CORE_SRC_PATH.'Config'.DS.'ConfigInterface.php',
            'Easy\\Core\\Config\\Php' => CORE_SRC_PATH.'Config'.DS.'Php.php',
            
            // Core\Utils
            'Easy\\Core\\Utils\\Arr' => CORE_SRC_PATH.'Utils'.DS.'Arr.php',
            'Easy\\Core\\Utils\\View' => CORE_SRC_PATH.'Utils'.DS.'View.php',
            'Easy\\Core\\Utils\\Layout' => CORE_SRC_PATH.'Utils'.DS.'Layout.php',
            'Easy\\Core\\Utils\\Html' => CORE_SRC_PATH.'Utils'.DS.'Html.php',
            'Easy\\Core\\Utils\\Cookie' => CORE_SRC_PATH.'Utils'.DS.'Cookie.php',
            'Easy\\Core\\Utils\\Validator' => CORE_SRC_PATH.'Utils'.DS.'Validator.php'
        ),
        'aliases' => array()
    );

