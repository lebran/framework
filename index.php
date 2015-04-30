<?php
/**
 * Разделитель директорий ( '/', '\' )
 * @var string
 */
define( 'DS', '/' ) ;
	
/**
 * Полный путь к директории приложения
 * @var string
 */
define( 'APP_PATH', $_SERVER['DOCUMENT_ROOT'] . DS . 'application' . DS) ;
	
/**
 * Полный путь к директории ядра 
 * @var string
 */
define( 'CORE_PATH', $_SERVER['DOCUMENT_ROOT'] . DS . 'core' . DS) ;

/**
 * Полный путь к директории модулей
 * @var string
 */
define( 'MOD_PATH', $_SERVER['DOCUMENT_ROOT'] . DS . 'modules' . DS) ;
    
/**
 * Полный путь к директории шаблонов
 * @var string
 */
define( 'TPL_PATH', $_SERVER['DOCUMENT_ROOT'] . DS . 'templates' . DS) ;
	
//
//  Подключаем ядро системы	
//
    
    require CORE_PATH.'classes'.DS.'Easy'.DS.'Core.php';
    
//
//  Инициализация ядра
//  
    
    Easy_Core::init();
	
//  
//  Передаем управление избранному контроллеру 
//
    
echo Request::make()
        ->execute()
        ->send_headers()
        ->body();
