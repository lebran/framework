<?php

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
 * @package Base
 * @author iToktor
 * @since 1.2
 */
abstract class Easy_Core{ 
     
    /**
     * @var array пути для поиска файлов.
     */
    protected static $_path = array();

    /**
     * @var array запущеные модули. 
     */
    protected static $_modules = array();
    
    /**
     * @var boolean показует был ли запущен init().
     */
    protected static $_init = FALSE;

    /**
     * Инициализация ядра, установка первоначальных настроек.
     * 
     * @uses Config::get() 
     * @uses Config::read() 
     */
    public static function init() {
        //Метод может быть запущен 1 раз
        if (self::$_init){
            return;
	}
        self::$_init = TRUE;
        
        // Добавляем путь к главным файлам системы
        self::include_path(array(CORE_PATH, APP_PATH), TRUE);
             
        // Временно регистрируем автозагрузчик
        spl_autoload_register(array('Easy_Core', 'autoload'));
        
        // Загружаем файл базовых настроек
        $config = Config::read('system');
        
        // Устанавливаем автозагрузчик
        if(!empty($config['autoload'])){ 
            spl_autoload_unregister(array('Easy_Core', 'autoload'));
            spl_autoload_register($config['autoload']);
        }        
        
        // Устанавливаем обработчик исключений
        if(empty($config['exception_handler'])){
            $config['exception_handler'] = array('Easy_Core', 'exception_handler');
        } 
        set_exception_handler($config['exception_handler']);

        // Устанавливаем обработчик ошибок
        if(empty($config['error_handler'])){
            $config['error_handler'] = array('Easy_Core', 'error_handler');
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
        
        // Проверка на наличие префикса для переменнх шаблона
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

        // Инициализируем модули
        self::modules(Config::read('modules'));
               
        // Добавляем пути к пользовательским файлам
        if(isset($config['include_path']) and is_array($config['include_path'])){
            self::include_path($config['include_path']);
        }
        
        // Устанавливаем обработаные настройки
        Config::set('system', $config);
    }    
    
    /**
     * Авто-загрузчик классов.
     * 
     * @param string $class - имя класса. 
     */
    public static function autoload($class){
        $file = str_replace('_', DS, $class);
        require_once self::find_file('classes',$file);
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
    public static function include_path($path = NULL, $delete = FALSE) {
        if(empty($path)){    
            return self::$_path;
        }else if($delete === TRUE){
            if(is_array($path)){    
                self::$_path = $path;
            }else{
                self::$_path = array($path);
            }
        }else{
            if(is_array($path)){
                self::$_path = Arr::merge($path, self::$_path);
            }else{
                array_unshift(self::$_path, $path);
            }
        }
    }
        
    /**
     * Ищет файл по заданым параметрам.
     * 
     *      Easy_Core::find_file('images', 'header', TRUE, 'jpeg');
     *      Пути подходящие под эти параметры:
     *          - core/images/header.jpeg,    
     *          - modules/test/images/header.jpeg. // Если инициализизирован модуль "Test"
     * 
     * @param string $subfolder - под-папка в которой искать.
     * @param string $name - имя файла.
     * @param bool $return_all - возвращать все найденые файли или первый(по умочанию - первый).
     * @param string $extension - тип файла.
     * @return mixed полный путь на найденый файл(ы) или False.
     */
    public static function find_file($subfolder, $name,$return_all = FALSE, $extension = 'php') {
        $fname = $name.'.'.$extension;
        $found_files = array();
        foreach (self::include_path() as $folder){
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
    public static function modules(array $modules = NULL){
        if ($modules === NULL){
            return self::$_modules;
	}
        
        self::include_path(CORE_PATH, TRUE);
        
	foreach ($modules as $name => $path){
            if (is_dir($path)){
                self::include_path($path); 
                self::$_modules[$name] = $path;
            }else{
                throw new Easy_Exception('Неправильный путь к модулю '.$name.' или его не существует!!!');
            }
	}
        
        self::include_path(APP_PATH);
        
	foreach (self::$_modules as $path)	{
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
    public static function exception_handler(Exception $e){
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
    public static function error_handler($errno, $errstr, $errfile, $errline){     
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
}