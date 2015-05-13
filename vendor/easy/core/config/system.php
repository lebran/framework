<?php
    return array(
        // Включена ли система
        'offline' => '1',
        
        // Сообщение в отключенном состоянии
        'offline_message' => 'Извините, но сайт временно не работает по техническим причинам.',

        // Показ ошибок
        'error_reporting' => '-1',

        // Путь к папке для хранение логов
        'log_path' => APP_PATH.'temp'.DS.'logs'.DS,

        // Обработчик исключений
        'exception_handler' => array('Easy\\Core\\Easy', 'exceptionHandler'),
        
        // Обработчик ошибок 
        'error_handler' => array('Easy\\Core\\Easy', 'errorHandler'),
        
        // Базовый URL
        'base_url' => '/',
        
        // Базовый шаблон
        'template' => 'default',
        
        // Префикс для переменных шаблона
        'view_prefix' => '',
        
        // Кодировка ввода и вывода данных
        'charset' => 'utf-8',
        
        // Пользовательськие пути 
        'path' => array()
    );