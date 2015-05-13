<?php
namespace Easy\Core;

use Easy\Core\Utils\Arr;

/**
 *                  ЯДРО СИСТЕМЫ
 * Устанавливает первоначальные настройки для запуска:
 *      
 *      - пути для поиска файлов,
 *      - инициализация модулей,
 *      - установка обработчиков ошибок и исключений,
 *      - регистрация автозагрузчика,
 *      - обработка и установка базовых настроек(system).
 * 
 * Имеет методы-помощники низкого уровня.
 *  
 * @package    Core
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
abstract class Easy
{ 
     
    /**
     * Хранилище путей для поиска файлов.
     *
     * @var array
     */
    protected static $path = array(CORE_PATH, APP_PATH);

    /**
     * Хранилище запущеных модулей.
     *
     * @var array 
     */
    protected static $modules = array();
    
    /**
     * Показует был ли запущен init().
     *
     * @var boolean 
     */
    protected static $init = FALSE;

    /**
     * Инициализация ядра, установка необходимых первоначальных настроек.
     * 
     * @uses Config::get() 
     * @uses Config::read()
     * @uses Autoloader::addNamespace()
     * @return void
     */
    public static function init() 
    {
        //Метод может быть запущен 1 раз
        if (self::$init) {
            return;
	}
        self::$init = TRUE;

        // Загружаем файл базовых настроек
        $config = Config::read('system');        

        // Проверка включена ли система
        if (!(boolean)$config['offline']) {
            die($config['offline_message']);
        }

        // Устанавливаем обработчик исключений
        if (empty($config['exception_handler'])) {
            $config['exception_handler'] = array(self, 'exceptionHandler');
        } 
        set_exception_handler($config['exception_handler']);

        // Устанавливаем обработчик ошибок
        if (empty($config['error_handler'])) {
            $config['error_handler'] = array(self, 'errorHandler');
        } 
        set_error_handler($config['error_handler']);
        
        // Обработка базового url для дальнейшего использования
        if (empty($config['base_url'])) {
            $config['base_url'] = DS;
        } else {
            $url = trim(trim($config['base_url']), DS).DS;
            
            if (strlen($url) > 1) {
                $url = DS.$url;
            }
            $config['base_url'] = $url;
        }
        
        // Проверка на наличие настройки базового шаблона
        if (empty($config['template']) and is_dir(TPL_PATH.'default')) {
            $config['template'] = 'default';
        }
        
        // Проверка на наличие префикса для переменных шаблона
        if (empty($config['view_prefix'])) {
            $config['view_prefix'] = '';
        }
        $config['view_prefix'] = trim($config['view_prefix']);
        
        // Установка кодировки ввода и вывода
        if (empty($config['charset'])) {
            $config['charset'] = 'utf-8';
        }
        $config['charset'] = strtolower($config['charset']);
        mb_internal_encoding($config['charset']);
        
        // Задаем замещающий символ на случай, когда кодировка входных данных задана неверно
        // или код символа не существует в кодировке выходных данных. 
        mb_substitute_character('none');
        
        // Добавляем пользовательськую директорию
        Autoloader::addNamespace('Easy\\App', APP_PATH.'src');
        
        // Инициализируем модули
        self::modules(Config::read('modules'));

        // Добавляем пути к пользовательским файлам
        if (isset($config['path']) and is_array($config['path'])) {
            self::path($config['path']);
        }

        // Устанавливаем обработаные настройки
        Config::set('system', $config);
        
        // Добавляем пути и псевдонимы классам
        $autoload = Config::read('autoload');
        Autoloader::addClasses($autoload['classes']);
        Autoloader::addAlias($autoload['aliases']);
    }    
            
    /**
     * Метод установки или получения(ничего не передавать) путей для поиска файлов.
     * 
     * @param mixed $path Новый путь.
     * @param boolean $delete Удалять ли предыдущие пути.
     * @return array Массив добавленых путей в системе.
     * @uses Arr::merge()
     */
    public static function path($path = null, $delete = false) 
    {
        if (empty($path)) {
            return self::$path;
        } else if($delete){
            self::$path = is_array($path)? $path: array($path);
        } else {
            if (is_array($path)) {
                Arr::merge(self::$path, $path, true);
            } else {
                array_unshift(self::$path, $path);
            }
        }
    }
        
    /**
     * Ищет файл по заданым параметрам.
     * 
     *      Easy::findFile('images', 'header', 'jpeg', true);
     *      Пути подходящие под эти параметры:
     *          - vendor/easy/core/images/header.jpeg,
     *          - application/images/header.jpeg,
     *          - vendor/easy/test/images/header.jpeg  // Если инициализизирован модуль "test"
     * 
     * @param string $subfolder Под-папка в которой искать.
     * @param string $name Имя файла.
     * @param string $extension Тип файла.
     * @param bool $return_all Возвращать все найденые файли или первый?
     * @return mixed Полный путь на найденый файл(ы) или false.
     */
    public static function findFile($subfolder, $name, $extension = 'php', $return_all = FALSE) {
        $fname = $name.'.'.$extension;
        $found_files = array();
        foreach (self::path() as $folder) {
            $file = trim($folder, DS).DS.trim($subfolder, DS).DS.$fname;
            if (file_exists($file)) {
                $found_files[] = $file;
            }
        }
		
        if (!empty($found_files) and !$return_all) {
            return $found_files[0];
        } else if (!empty($found_files)) {
            return $found_files;
        } else {
            return false;
        }
    }
    
    /**
     * Инициализирует переданый перечень модулей,
     * если ничего не отправлять - вернет массив запущенных.
     * 
     *      Для добавления нового модуля необходимо добавить его в файл modules
     *      в папке core/config или раскомментировать существующий.
     * 
     *      Пример файла modules:
     *      
     *      return array(
     *        // имя        // путь
     *        // 'db' => MOD_PATH.'database',
     *        'test' => MOD_PATH.'test'
     *      );
     * 
     * 
     * 
     * @param array $modules Перечень модулей.
     * @return array Массив запущеных модулей.
     * @throws \Exception
     */
    public static function modules(array $modules = null){
        if ($modules === NULL) {
            return self::$modules;
	}
        
        self::path(CORE_PATH, TRUE);
        
	foreach ($modules as $name => $path) {
            if (is_dir($path)) {
                self::path($path); 
                self::$modules[$name] = $path;
            } else {
                throw new Exception('Неправильный путь к модулю "'.$name.'" или его не существует!!!');
            }
	}
        
        self::path(APP_PATH);
        
	foreach (self::$modules as $path) {
            $init = trim($path,DS).DS.'init.php';
            if (is_file($init)) {
		require_once $init;
            }
	}
    }
    
    /**
     * Перехватчик не обработанных исключений.
     * 
     * @param \Exception $e Исключение.
     * @return void
     */
    public static function exceptionHandler(\Exception $e){
        echo '<center>'.$e->getMessage().'</center>';
        echo '<center> В файле: '.$e->getFile().'</center>';
        echo '<center> Строка: '.$e->getLine().'</center>';
    }
    
    /**
     * Задает обработчик ошибок
     * 
     * @param int $errno Уровень ошибки.
     * @param string $errstr Сообщение об ошибке.
     * @param string $errfile Имя файла, в котором произошла ошибка.
     * @param int $errline Номер строки, в которой произошла ошибка.
     * @return void
     * @throws ErrorException
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline){     
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
}