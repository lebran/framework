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
     * @var array пути для поиска файлов.
     */
    protected static $path = array(CORE_PATH, APP_PATH);

    /**
     * @var array запущеные модули. 
     */
    protected static $modules = array();
    
    /**
     * @var boolean показует был ли запущен init().
     */
    protected static $init = FALSE;

    /**
     * Инициализация ядра, установка первоначальных настроек.
     * 
     * @uses Config::get() 
     * @uses Config::read() 
     */
    public static function init() 
    {
        //Метод может быть запущен 1 раз
        if (self::$init){
            return;
	}
        self::$init = TRUE;
                
        // Загружаем файл базовых настроек
        $config = Config::read('system');        
        
        // Устанавливаем обработчик исключений
        if(empty($config['exception_handler'])){
            $config['exception_handler'] = array(self, 'exceptionHandler');
        } 
        set_exception_handler($config['exception_handler']);

        // Устанавливаем обработчик ошибок
        if(empty($config['error_handler'])){
            $config['error_handler'] = array(self, 'errorHandler');
        } 
        set_error_handler($config['error_handler']);
        
        // Обработка базового url для дальнейшего использования
        if(empty($config['base_url'])){
            $config['base_url'] = DS;
        }else{
            $url = trim(trim($config['base_url']), DS).DS;
            
            if(strlen($url) > 1){
                $url = DS.$url;
            }
            $config['base_url'] = $url;
        }
        
        // Проверка на наличие настрйки базового шаблона
        if(empty($config['template']) and is_dir(TPL_PATH.'default')){
            $config['template'] = 'default';
        }
        
        // Проверка на наличие префикса для переменных шаблона
        if(empty($config['view_prefix'])){
            $config['view_prefix'] = '';
        }
        $config['view_prefix'] = trim($config['view_prefix']);
        
        // Установка кодировки ввода и вывода
        if(empty($config['charset'])){
            $config['charset'] = 'utf-8';
        }
        $config['charset'] = strtolower($config['charset']);
        mb_internal_encoding($config['charset']);
        
        // Задаем замещающий символ на случай, когда кодировка входных данных задана неверно
        // или код символа не существует в кодировке выходных данных. 
        mb_substitute_character('none');
        
        //
        Autoloader::addNamespace('Easy\\App', APP_PATH.'src');
        
        // Инициализируем модули
        self::modules(Config::read('modules'));
        
        // Добавляем пути к пользовательским файлам
        if(isset($config['path']) and is_array($config['path'])){
            self::path($config['path']);
        }
        
        // Устанавливаем обработаные настройки
        Config::set('system', $config);
    }    
            
    /**
     * Метод добавления новых путей для поиска файлов или
     * если ничего не передавать работает как геттер.
     * 
     * @param mixed $path - новый путь.
     * @param boolean $delete - удалять ли предыдущие пути.
     * @return array
     * @uses Arr::merge()
     */
    public static function path($path = null, $delete = false) 
    {
        if(empty($path)){    
            return self::$path;
        }else if($delete){
            if(is_array($path)){    
                self::$path = $path;
            }else{
                self::$path = array($path);
            }
        }else{
            if(is_array($path)){
                Arr::merge(self::$path, $path, true);
            }else{
                array_unshift(self::$path, $path);
            }
        }
    }
        
    /**
     * Ищет файл по заданым параметрам.
     * 
     *      Easy::findFile('images', 'header', TRUE, 'jpeg');
     *      Пути подходящие под эти параметры:
     *          - core/images/header.jpeg,    
     *          - modules/test/images/header.jpeg. // Если инициализизирован модуль "Test"
     * 
     * @param string $subfolder - под-папка в которой искать.
     * @param string $name - имя файла.
     * @param string $extension - тип файла.
     * @param bool $return_all - возвращать все найденые файли или первый.
     * @return mixed полный путь на найденый файл(ы) или False.
     */
    public static function findFile($subfolder, $name, $extension = 'php', $return_all = FALSE) {
        $fname = $name.'.'.$extension;
        $found_files = array();
        foreach (self::path() as $folder){
            $file = trim($folder, DS).DS.trim($subfolder, DS).DS.$fname;
            if (file_exists($file)){	
                $found_files[] = $file;
            }
        }
		
        if (!empty($found_files) and !$return_all){
            return $found_files[0];
        }else if (!empty($found_files)) {
            return $found_files;
        }else{
            return FALSE;
        }
    }
    
    /**
     * Инициализирует переданый перечень модулей,
     * если ничего не отправлять вернет массив запущенных.
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
     * @param array $modules - перечень модулей.
     * @return array массив модулей
     * @throws Easy_Exception
     */
    public static function modules(array $modules = null){
        if ($modules === NULL){
            return self::$modules;
	}
        
        self::path(CORE_PATH, TRUE);
        
	foreach ($modules as $name => $path){
            if (is_dir($path)){
                self::path($path); 
                self::$modules[$name] = $path;
            }else{
                throw new Exception('Неправильный путь к модулю '.$name.' или его не существует!!!');
            }
	}
        
        self::path(APP_PATH);
        
	foreach (self::$modules as $path)	{
            $init = trim($path,DS).DS.'init.php';

            if (is_file($init)){
		require_once $init;
            }
	}
    }
    
    /**
     * Перехватчик не обработанных исключений.
     * 
     * @param Easy_Exception $e - исключение.
     */
    public static function exceptionHandler(\Exception $e){
        echo '<center>'.$e->getMessage().'</center>';
        echo '<center> В файле: '.$e->getFile().'</center>';
        echo '<center> Строка: '.$e->getLine().'</center>';
    }
    
    /**
     * Задает обработчик ошибок
     * 
     * @param int $errno - содержит уровень ошибки.
     * @param string $errstr - содержит сообщение об ошибке.
     * @param string $errfile - содержит имя файла, в котором произошла ошибка.
     * @param int $errline - содержит номер строки, в которой произошла ошибка.
     * @throws ErrorException
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline){     
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
}