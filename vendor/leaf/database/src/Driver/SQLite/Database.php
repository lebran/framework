<?php
/**
 * Драйвер для базы данных SQLite.
 *
 *
 * @package Database
 * @author iToktor
 * @since 1.0
 */
class Driver_SQLite_Database extends Driver_Database {
   
    /**
     * Подключение к базе данных. Вызывается автоматически при первом выполнении запроса.
     *
     * @throws Leaf_Exception
     */
    public function connect(){
	if ($this->_connection){
            return;
        }
        
	extract($this->_config['connection'] + array('database' => ''));
	try{
            $this->_connection = new SQLite3($database);
	}catch (Exception $e){
            $this->_connection = NULL;
            throw new Leaf_Exception($e->getMessage());
	}
    }

    /**
     * Отсоединение от базы данных.
     * Автоматически вызывается [Driver_Database::__destruct].
     *
     * @return  boolean
     */
    public function disconnect(){
	try{
            $status = TRUE;

            if(is_resource($this->_connection)){
		if (($status = $this->_connection->close())){
                    
                    $this->_connection->close();
                    $this->_connection = NULL;

                    parent::disconnect();
		}
            }
	}catch (Exception $e){
            $status = ! is_resource($this->_connection);
	}
	return $status;
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
    public function query($type, $sql){
	$this->_connection or $this->connect();
	
        if (($result = $this->_connection->query($sql)) === FALSE){
            throw new Leaf_Exception($this->_connection->lastErrorMsg());
        }

	$this->last_query = $sql;

	if ($type === Driver_Database::SELECT){
            return new Driver_SQLite_Result($result);
	}
	elseif ($type === Driver_Database::INSERT){
            return array(
		$this->_connection->lastInsertRowID(),
                $this->_connection->changes()
            );
	}else{
            return $this->_connection->changes();
	}
    }
    
    /**
     * Регистрирует функцию для использования в качестве SQL агрегатной функции.
     * 
     * @param string $name - название SQL фу-и, которая должна быть создана или переопределена.
     * @param mixed $step - имя фу-и, которую нужно применить к каждой строке.
     * @param mixed $final - имя фу-и, которая применится в конце для всего набора данных.
     * @param int $arguments - количество аргументов, которая принимает агрегатная функция.
     * @return boolean
     */
    public function aggregate($name, $step, $final, $arguments = -1) {
        $this->_connection or $this->connect();

	return $this->_connection->createAggregate($name, $step, $final, $arguments);
    }
    
    /**
     * Регистрирует РНР функцию для использования в качестве SQL-функции.
     * 
     * @param string $name - название SQL фу-и, которая должна быть создана или переопределена.
     * @param mixed $callback - имя фу-и, которую нужно применить к каждой строке.
     * @param int $arguments - количество аргументов, которая принимает фу-я.
     * @return boolean
     */
    public function func($name, $callback, $arguments = -1) {
        $this->_connection or $this->connect();

	return $this->_connection->createFunction($name, $callback, $arguments = -1);
    }
    
    /**
     * Регистрирует PHP функцию для использования в качестве SQL функции упорядочения.
     * 
     * @param string $name - имя SQL фу-и упорядочения, которая должна быть создана или переопределена.
     * @param mixed $callback - имя фу-и, которую нужно применить к каждой строке.
     * @return boolean
     */
    public function collation($name, $callback) {
        $this->_connection or $this->connect();

	return $this->_connection->createCollation($name, $callback);
    }
    
    /**
     * Эканирует символы в строке, которые могут привести к SQL иньекции.
     *
     * @param string $value - значение.
     * @return string
     */
    public function escape($value){
	$this->_connection or $this->connect();

	if (($value = $this->_connection->escapeString($value)) === FALSE){
            throw new Leaf_Exception($this->_connection->lastErrorMsg());
        }

	return "'$value'";
    }
}