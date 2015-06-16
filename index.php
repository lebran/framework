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
define( 'LEAF_PATH', VENDOR_PATH.'leaf'.DS) ;

/**
 * Полный путь к директории ядра фреймворка.
 * 
 * @var string
 */
define( 'CORE_PATH', LEAF_PATH.'core'.DS) ;

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
    Leaf\Core\Autoloader::register();
        
//
//  Инициализация ядра
//  
    
    echo Leaf\Core\Leaf::make()
            ->execute()
            ->sendHeaders();
