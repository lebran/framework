<?php
/**
 * Время запуска приложения.
 * 
 * @var string
 */
define('LEBRAN_START_TIME', microtime(true));

/**
 * Количество памяти выделенной PHP
 *
 * @var string
 */
define('LEBRAN_START_MEM', memory_get_usage());

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
define( 'LEBRAN_PATH', VENDOR_PATH.'Lebran'.DS) ;
   
/**
 * Полный путь к директории шаблонов.
 * 
 * @var string
 */
define( 'TPL_PATH', $_SERVER['DOCUMENT_ROOT'] . DS . 'templates' . DS) ;

error_reporting(E_ALL);

require_once LEBRAN_PATH.'Autoloader.php';

$autoloader = new \Lebran\Autoloader();

$autoloader->addNamespaces(
    array(
        'Lebran' => LEBRAN_PATH,
        'Lebran\\App' => APP_PATH
    )
);

$autoloader->register();

$di = new Lebran\Di\Container();

$di->set('autoloader', $autoloader, true);

$di->set('request', function (){
    return new \Lebran\Http\Request();
}, true);

$di->set('response', function (){
    return new \Lebran\Http\Response();
}, true);

$di->set('cookies', function (){
    return new \Lebran\Http\Cookies();
}, true);

$di->set('test', function ($param1, $param2){
    return new \Lebran\App\TestController($param1, $param2);
});
$di->set('test', array(
    'class' => '\Lebran\App\TestController',
    'arguments' => array(
        array('type' => 'class', 'name' => '\Lebran\App\TestController', 'arguments' => array(
            array('type' => 'class', 'name' => '\Lebran\App\TestController'),
            array('type' => 'class', 'name' => 'test', 'arguments' => array(
                array('type' => 'parameter', 'value' => 'yoyoyiyyo'),
                array('type' => 'parameter', 'value' => 'Pidari')
            )),
        )),
        array('type' => 'parameter', 'value' => 'World'),
    ),
    'calls' => array(
        array('method' => 'test', 'arguments' => array(
            array('type' => 'parameter', 'value' => 'Pizdec')
        ))
    ),
    'properties' => array(
        array('name' => 'test', 'value' => array(
            'value' => ' QQQQQQQQQQQQQQQQQQ ', 'type' => 'parameter'
        ))
    )
));

$di->get('test');



/*$di->set('router', function () {
    $router = new \Lebran\Mvc\Router();
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
*/


//$di['cookies']['system.fdfdf.asdasd.teg'] = array('adssad'=>'asasd', 'asdasdas' => 'asd');


//unset($di['cookies']['system.fdfdf.asdasd.teg']);

//$di->get('cookies')->set('system.fdfdf.asdasd.teg', array('adssad'=>'asasd', 'asdasdas' => 'asd'));

//var_dump($_COOKIE);

