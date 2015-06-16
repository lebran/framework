<?php
/**
 * Базовый супер-класс для соединения с бд.
 *
 *  Этот класс обеспечивает:
 *  - управление экземплярами подключеными через драйверы баз данных,
 *  - методы цитирование строк для защиты от SQL иньекций,
 *  - другие полезный функции.
 *
 * @package Database
 * @author iToktor
 * @since 1.1
 */
abstract class Driver_Database {

    /**
     * Типы запросов.
     */
    const SELECT =  1;
    const INSERT =  2;
    const UPDATE =  3;
    const DELETE =  4;

    /**
     * @var string имя экземпляра по умолчанию.
     */
    public static $default = 'default';

    /**
     * @var array экземпляры баз данных.
     */
    public static $instances = array();

	/**
	 * Создает экземпляр соединение с бд или отдает, если оно уже выполнено.
         * Если не передать имя соединения, то по умолчанию будет выполнено 'default'.
         * Настройки, а так же новые соединения нужно задавать в файле config/database.php
	 *
	 *     // Загрузка бд по умочанию
	 *     $db = Database::instance();
	 *
	 *     // Загрузка кастомного соединения
	 *     $db = Database::instance('custom', $config);
	 *
	 * @param string $name - имя экземпляра.
	 * @param array $config - массив настроек бд.
	 * @return Driver_Database
	 */
    public static function instance($name = NULL, array $config = NULL){
        if(empty(self::$instances)){
            Config::read('database', TRUE);
        }
        
        if ($name === NULL){
            $name = self::$default;
	}

	if (!isset(self::$instances[$name])){
            if ($config === NULL){
                $config = Config::get('database.'.$name);
            }

            if (!isset($config['type'])){
		throw new Leaf_Exception('В настройках '.$name.' не указан драйвер');
            }

            $driver = 'Driver_'.ucfirst($config['type']).'_Database';
            $driver = new $driver($name, $config);
            self::$instances[$name] = $driver;
	}

	return self::$instances[$name];
    }

    /**
     * @var string последний выполненый запрос.
     */
    public $last_query;

    /**
     * @var string имя подключения. 
     */
    protected $_instance;

    /**
     * @var mixed хранилище соединения с бд. 
     */
    protected $_connection;

    /**
     * @var array массив настроек. 
     */
    protected $_config;

    /**
     * Локально cохраняет имя экземпляра и конфигурацию базы данных .
     *
     * @param string $name - имя экземпляра.
     * @param array $config - массив конфигураций.
     */
    protected function __construct($name, array $config){
	$this->_instance = $name;

        $this->_config = $config;

	if (empty($this->_config['table_prefix'])){
            $this->_config['table_prefix'] = '';
	}
    }

    /**
     * Отсоединение от базы данных, если объект будет уничтожен.
     *
     *     // Уничтожение экземпляра подключения к бд
     *     unset(Driver_Database::instances[(string) $db], $db);
     */
    public function __destruct(){
    	$this->disconnect();
    }

    /**
     * Возвращает имя экземпляра базы данных.
     *
     *     echo (string)$db;
     *
     * @return string
     */
    public function __toString(){
    	return $this->_instance;
    }

    /**
     * Подключение к базе данных. Вызывается автоматически при первом выполнении запроса.
     *
     * @throws Leaf_Exception
     */
    abstract public function connect();

    /**
     * Отсоединение от базы данных.
     * Автоматически вызывается [Driver_Database::__destruct].
     *
     * @return  boolean
     */
    public function disconnect(){
	unset(Database::$instances[$this->_instance]);
	return TRUE;
    }

    /**
     * Выполнение SQL-запроса переданого типа.
     *
     *     $db->query(Database::SELECT, 'SELECT * FROM test');
     *
     * @param integer $type - тип запроса.
     * @param string $sql - запрос.
     * @return Driver_Result|array|string
     */
    abstract public function query($type, $sql);

    /**
     * Возвращает префикс таблици, определенный в текущей конфигурации.
     *
     *      $prefix = $db->table_prefix();
     *
     * @return  string
     */
    public function table_prefix(){
    	return $this->_config['table_prefix'];
    }

    /**
     * Цитирование значений для SQL - запроса. 
     *
     *      $db->quote(NULL);   // 'NULL'
     *      $db->quote(10);     // 10
     *      $db->quote(TRUE);   // '1'
     *
     * @param mixed $value - значение.
     * @return string
     */
    public function quote($value){
	if ($value === NULL){
            return 'NULL';
	}elseif ($value === TRUE){
            return "'1'";
	}elseif ($value === FALSE){
            return "'0'";
	}elseif (is_object($value)){
            return $this->quote((string)$value);
	}elseif (is_array($value)){
            return '('.implode(', ', array_map(array($this, __FUNCTION__), $value)).')';
	}elseif (is_int($value)){
            return (int) $value;
	}elseif (is_float($value)){
            return sprintf('%F', $value);
	}

	return $this->escape($value);
    }

    /**
     * Эканирует символы в строке, которые могут привести к SQL иньекции.
     *
     * @param string $value - значение.
     * @return string
     */
    abstract public function escape($value);
} 
