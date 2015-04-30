<?php
    return array(
        // Автозагрузчик
        'autoload' => array('Easy_Core', 'autoload'),
        
        // Обработчик исключений
        'exception_handler' => array('Easy_Core', 'exception_handler'),
        
        // Обработчик ошибок 
        'error_handler' => array('Easy_Core', 'error_handler'),
        
        // Базовый URL
        'base_url' => '/',
        
        // Базовый шаблон
        'template' => 'default',
        
        // Префикс для переменных шаблона
        'view_prefix' => '',
        
        // Кодировка ввода и вывода данных
        'charset' => 'utf-8',
        
        // Пользовательськие пути 
        'include_path' => array()
    );