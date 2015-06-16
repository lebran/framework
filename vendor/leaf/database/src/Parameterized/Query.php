<?php

/**
 * Класс-обвертка для SQL запросов.
 * Реализует запросы с подготовленными параметрами.
 * 
 *                          ПРИМЕР
 * 
 *      $sql = 'SELECT * FROM table WHERE a = :b'
 *      Db::query($sql)         // Запрос
 *      ->param(':b' => $test)  // Подготовка параметра,
 *                              // который будет переконвертирован в
 *                              // строку, для защиты от SQL иньекций.  
 *      ->execute('default');   // Отправка запроса с помощью 
 *                              // выбранного подключения (default)
 *
 * @package Database
 * @author iToktor
 * @since 1.0
 */
class Parameterized_Query {

    /**
     * @var string запрос.
     */
    protected $_sql;

    /**
     * @var array параметры SQL запроса.
     */
    protected $_param = array();

    /**
     * Создание SQL запроса.
     *
     * @param string $sql - запрос.
     */
    public function __construct($sql){
	$this->_sql = $sql;
    }

    /**
     * Возвращает строку SQL запроса.
     *
     * @return  string
     */
    public function __toString(){
	try{
            return $this->compile(Database::instance());
	}catch (Exception $e){
            throw new Leaf_Exception($e);
	}
    }

    /**
     * Установка значения параметра в запросе.
     *
     * @param string $param - ключ параметра для замены.
     * @param mixed $value - значение, которым заменит.
     * @return $this
     */
    public function param($param, $value = NULL){
        if(is_array($param)){
            Arr::merge($this->_param, $param);
        }else{
            $this->_param[$param] = $value;
        }
        
	return $this;
    }

	/**
	 * Компиляция SQL запроса. 
         * Заменяет все параметры на их приведенные значения.
	 *
	 * @param Driver_Database $db - экземпляр или имя экземпляра базы данных.
	 * @return  string
	 */
    public function compile(Driver_Database $db = NULL){
        if (!is_object($db)){
            $db = Driver_Database::instance($db);
	}

	$sql = $this->_sql;

	if(!empty($this->_param)){
            $values = array_map(array($db, 'quote'), $this->_param);
            $sql = trim(strtr($sql, $values));
	}

	return $sql;
    }
    
    /**
     * Определения типа SQL запроса
     * (SELECT, INSERT, UPDATE, DELETE).
     * 
     * @param string $sql - запрос.
     * @return integer|bool
     */
    protected function _type($sql) {
        if(stripos($sql, 'select') === 0){
            return Driver_Database::SELECT;
        }else if(stripos($sql, 'insert') === 0){
            return Driver_Database::INSERT;
        }else if(stripos($sql, 'update') === 0){
            return Driver_Database::UPDATE;
        }else if(stripos($sql, 'delete') === 0){
            return Driver_Database::DELETE;
        }else{
            return FALSE;
        }
    }
    
    /**
     * Выполнение текущего запроса с переданым соединением базы данных.
     *
     * @param Database_Driver $db - экземпляр или имя экземпляра базы данных.
     * @return  mixed
     */
    public function execute(Database_Driver $db = NULL){
	if (!is_object($db)){
            $db =  Driver_Database::instance($db);
	}

	$sql = $this->compile($db);
        $type = $this->_type($sql);
        $result = $db->query($type, $sql);

	return $result;
    }
}
