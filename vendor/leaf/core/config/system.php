<?php
    return array(
        // Включена ли система
        'offline' => '1',
        
        // Сообщение в отключенном состоянии
        'offline_message' => 'Извините, но сайт временно не работает по техническим причинам.',

        // Показ ошибок
        'error_reporting' => E_ALL,

        // Путь к папке для хранение логов
        'log_path' => APP_PATH.'temp'.DS.'logs'.DS,

        // Обработчик исключений
        'exception_handler' => array('Leaf\\Core\\Leaf', 'exceptionHandler'),
        
        // Обработчик ошибок 
        'error_handler' => array('Leaf\\Core\\Leaf', 'errorHandler'),
        
        // Базовый URL
        'base_url' => '/',
                
        // Кодировка ввода и вывода данных
        'charset' => 'utf-8',
        
        // Пользовательськие пути 
        'path' => array()
    );