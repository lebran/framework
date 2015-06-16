<?php
namespace Leaf\Core;

use Leaf\Core\Http\Router;
use Leaf\Core\Http\Request;
use Leaf\Core\Http\Response;
use Leaf\Core\Http\HttpException;
use Leaf\Core\Utils\Finder;
use Leaf\Core\Config\Config;

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
class Leaf
{ 
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
    protected static $init = false;

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
        self::$init = true;

        // Загружаем файл базовых настроек
        $config = Config::read('system');        

        // Проверка включена ли система
        if (!(boolean)$config['offline']) {
            die($config['offline_message']);
        }

        // Показ ошибок
        if (!empty($config['error_reporting'])) {
            error_reporting($config['error_reporting']);
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
        Autoloader::addNamespace('Leaf\\App', APP_PATH.'src');
        
        // Инициализируем модули
        self::modules(Config::read('modules'));

        // Добавляем пути к пользовательским файлам
        if (isset($config['path']) and is_array($config['path'])) {
            Finder::addPath($config['path']);
        }

        // Устанавливаем обработаные настройки
        Config::set('system', $config);
        
        // Добавляем пути и псевдонимы классам
        $autoload = Config::read('autoload');
        Autoloader::addClasses($autoload['classes']);
        Autoloader::addAliases($autoload['aliases']);
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
        if ($modules === null) {
            return self::$modules;
	}
        
        Finder::addPath(CORE_PATH, TRUE);
        
	foreach ($modules as $name => $path) {
            if (is_dir($path)) {
                Finder::addPath($path);
                self::$modules[$name] = $path;
                Autoloader::addNamespace('Leaf\\'.ucfirst($name), $path.DS.'src');
            } else {
                throw new Exception('Неправильный путь к модулю "'.$name.'" или его не существует!!!');
            }
	}
        
        Finder::addPath(APP_PATH);
        
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

    /**
     * Хранилище для текущего запроса.
     *
     * @var Request
     */
    protected static $current;

    /**
     * Хранилище для инициализирующего запроса.
     *
     * @var Request
     */
    protected static $initial = false;

    /**
     * Инициализирует запрос
     *
     * @param string $uri Адрес запроса.
     * @return Request Обьект созданого запроса.
     */
    public static function make($uri = false)
    {
        self::init();
        self::$current = new self($uri);
        if(!self::$initial){
            self::$initial = self::$current;
        }
        return self::$current;
    }

    /**
     * Геттер для текущего запроса.
     *
     * @return Request
     */
    public static function current()
    {
        return self::$current;
    }

    /**
     * Геттер для инициализирующего запроса.
     *
     * @return Request
     */
    public static function initial()
    {
        return self::$initial;
    }

    /**
     *
     * @var type
     */
    public $request;

    /**
     *
     * @var type 
     */
    public $response;

    /**
     * Адрес запроса.
     *
     * @var string
     */
    public $uri;
    
    /**
     *
     * @param type $uri
     */
    protected function __construct($uri)
    {
        $this->uri = trim(($uri)? $uri: $_SERVER[ 'REQUEST_URI' ], '/');
        $this->request = new Request(
            Router::make(
                Config::read('routes')
            )
            ->check($this->uri)
        );
        $this->response = new Response();
    }

    /**
     * Передает работу выбраному пользователем контроллеру.
     *
     * @return Response - ссылка на ответ.
     * @throws Exception
     * @uses Response
     * @uses ReflectionClass
     */
    public function execute()
    {
        foreach (array_keys(Autoloader::getNamespaces()) as $key) {
            $controller = $this->request->getController();
            if (class_exists($key.$controller)) {
                $controller = $key.$controller;
                $class = new \ReflectionClass($controller);
                $controller = $class->newInstanceArgs(array($this->request, $this->response));
                break;
            }
        }

        $action = $this->request->getAction();
        if (empty($controller) or !method_exists($controller, 'run') or !method_exists($controller, $action)) {
            throw new HttpException('Ошибка 404');
        } else {
            return $controller->run($action);
        }
    }  


}