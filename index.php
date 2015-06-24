<?php
/**
 * Время запуска приложения.
 * 
 * @var string
 */
define('LEAF_START_TIME', microtime(true));

/**
 * Количество памяти выделенной PHP
 *
 * @var string
 */
define('LEAF_START_MEM', memory_get_usage());

/**
 * Разделитель директорий ( '/', '\' ).
 * 
 * @var string
 */
define( 'DS', '/' ) ;
	
/**
 * Полный путь к директории приложения.
 * 
 * @var string
 */
define( 'APP_PATH', $_SERVER['DOCUMENT_ROOT'].DS.'application'.DS) ;

/**
 * Полный путь к директории vendor.
 * 
 * @var string
 */
define( 'VENDOR_PATH', $_SERVER['DOCUMENT_ROOT'].DS.'vendor'.DS) ;

/**
 * Полный путь к директории фреймворка.
 * 
 * @var string
 */
define( 'LEAF_PATH', VENDOR_PATH.'Leaf'.DS) ;
   
/**
 * Полный путь к директории шаблонов.
 * 
 * @var string
 */
define( 'TPL_PATH', $_SERVER['DOCUMENT_ROOT'] . DS . 'templates' . DS) ;
	
require_once LEAF_PATH.'Autoloader.php';

$autoloader = new \Leaf\Autoloader();

$autoloader->addNamespaces(
    array(
        'Leaf' => LEAF_PATH,
        'Leaf\\App' => APP_PATH
    )
);

$autoloader->register();

$di = new Leaf\Di\Container();

$di->set('autoloader', $autoloader, true);

$di->set('request', function (){
    return new \Leaf\Http\Request();
}, true);

$di->set('response', function (){
    return new \Leaf\Http\Response();
}, true);

$di->set('cookies', function (){
    return new \Leaf\Http\Cookies();
}, true);

$di->set('router', function () {
    $router = new \Leaf\Mvc\Router();
    $router->add('test', '(<controller>(/<action>(/<id>)))')
        ->defaults(
            array(
                'controller' => 'test',
                'action' => 'index'
            )
        )
        ->regex(
            array(
                'id' => '\d+'
            )
        )
        ->middlewares(
            array(
                'test'
            )
        )
        ->callback(
            function($id) {
                return $id + 20;
            }
        );
}, true);

$di->get('router');

