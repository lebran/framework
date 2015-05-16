<?php
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
 * Полный путь к классам директории приложения.
 * 
 * @var string
 */
define( 'APP_SRC_PATH', APP_PATH.'src'.DS) ;

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
define( 'EASY_PATH', VENDOR_PATH.'easy'.DS) ;

/**
 * Полный путь к директории ядра фреймворка.
 * 
 * @var string
 */
define( 'CORE_PATH', EASY_PATH.'core'.DS) ;

/**
 * Полный путь к классам директории ядра фреймворка.
 * 
 * @var string
 */
define( 'CORE_SRC_PATH', CORE_PATH.'src'.DS) ;
   
/**
 * Полный путь к директории шаблонов.
 * 
 * @var string
 */
define( 'TPL_PATH', $_SERVER['DOCUMENT_ROOT'] . DS . 'templates' . DS) ;
	
//
//  Регистрация автолоадера
//
    
    require CORE_PATH.'src'.DS.'Autoloader.php';
    Easy\Core\Autoloader::register();
        
//
//  Инициализация ядра
//  
    
    Easy\Core\Easy::init();
	
//  
//  Передаем управление избранному контроллеру 
//
    
    echo Easy\Core\Http\Request::make()
        ->execute()
        ->sendHeaders();
