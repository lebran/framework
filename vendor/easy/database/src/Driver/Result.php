<?php
/**
 * Позволяет получить доступ к результатам, полученым от бд, в едином порядке.
 * А так же обеспечивает поддержку:
 *  - итератора, поэтому он может быть использован в foreach,
 *  - интерфейс Countable, можно использовать с функцией count(),
 *  - итератора SeekableIterator, доступен метод seek(),
 *  - интерфейс ArrayAccess, для доступа к обьекту как к массиву.
 *
 * @package Database   
 * @author iToktor
 * @since 1.0
 */
abstract class Driver_Result implements Countable, Iterator, SeekableIterator, ArrayAccess {
        
    /**
     * @var mixed хранилище для результата базы данных.
     */
    protected $_result;

    /**
     * @var integer количество выбраных строк. 
     */
    protected $_total_rows  = 0;
	
    /**
     * @var integer номер текущей строки.
     */
    protected $_current_row = 0;

    /**
     * Устанавливает общее количество строк и сохраняет результат на локальном уровне.
     *
     * @param mixed $result - результат запроса.
     */
    public function __construct($result){
	$this->_result = $result;
    }

    /**
     * Очищает все открытые наборы результатов.
     */
    abstract public function __destruct();

    /**
     * Возвращает результат в виде массива.
     *
     *     // Ассоциативный массив строк по "id".
     *     $rows = $result->as_array('id');
     *
     *     // Ассоциативный массив строк, "id" => "test"
     *     $rows = $result->as_array('id', 'test');
     *
     * @param string $key - колонка для ключей.
     * @param string $value - колонка для значений.
     * @return  array
     */
    public function as_array($key = NULL, $value = NULL){
	$results = array();

	if ($key === NULL AND $value === NULL){
            foreach ($this as $row){
		$results[] = $row;
            }
	}elseif ($value === NULL){
            foreach ($this as $row){
		$results[$row[$key]] = $row;
            }
        }else{
            foreach ($this as $row){
		$results[$row[$key]] = $row[$value];
            }
	}
	
        $this->rewind();
	return $results;
    }

    /**
     * Возвращает указаный столбец из текущего строки.
     *
     *     $count = Db::query('SELECT COUNT(*) as count FROM test')->execute()->get('count');
     *
     * @param string $name - нухная колонка.
     * @param mixed $default - значение, которое вернется, если колонки не существует. 
     * @return  mixed
     */
    public function get($name, $default = NULL){
	$row = $this->current();

	if(isset($row[$name])){
            return $row[$name];
        }else{
            return $default;
        }
    }

    /**
     * Наследует [Countable::count], возвращает количество строк.
     *
     *     echo count($result);
     *
     * @return integer
     */
    public function count(){
	return $this->_total_rows;
    }

    /**
     * Наследует [ArrayAccess::offsetExists], определяет, существует ли строка.
     *
     *     if (isset($result[5])){
     *         ......
     *     }
     *
     * @param integer $offset - номер строки.
     * @return boolean
     */
    public function offsetExists($offset){
        return ($offset >= 0 AND $offset < $this->_total_rows);
    }

    /**
     * Наследует [ArrayAccess::offsetGet], возвращает строки с переданым номером.
     *
     *     $row = $result[5];
     *
     * @param int $offset - номер строки.
     * @return mixed
     */
    public function offsetGet($offset){
	if ( ! $this->seek($offset)){
            return NULL;
        }
        
        return $this->current();
    }

    /**
     * Наследует [ArrayAccess::offsetSet], генерирует ошибку.
     *
     * [!!] Нельзя модифицировать результат.
     *
     * @param int $offset
     * @param mixed $value
     * @return void
     * @throws Easy_Exception
     */
    final public function offsetSet($offset, $value){
        throw new Easy_Exception('Результат только для чтения!!!');
    }

    /**
     * Наследует [ArrayAccess::offsetUnset], генерирует ошибку.
     *
     * [!!] Нельзя модифицировать результат.
     *
     * @param int $offset
     * @throws Easy_Exception
     */
    final public function offsetUnset($offset){
	throw new Easy_Exception('Результат только для чтения!!!');
    }
        
    /**
     * Наследует [Iterator::key], возвращает текущую строку.
     *
     *     echo key($result);
     *
     * @return integer
     */
    public function key(){
	return $this->_current_row;
    }

    /**
     * Наследует [Iterator::next], переходит к следующей строке.
     *
     *     next($result);
     *
     * @return $this
     */
    public function next(){
	++$this->_current_row;
	return $this;
    }

    /**
     * Наследует [Iterator::rewind], устанавливает текущую строку в ноль.
     *
     *     rewind($result);
     *
     * @return $this
     */
    public function rewind(){
	$this->_current_row = 0;
	return $this;
    }

    /**
     * Наследует [Iterator::valid], проверяет, существует ли текущая строка.
     *
     * [!!] Этот метод используется только внутри.
     *
     * @return  boolean
     */
    public function valid(){
	return $this->offsetExists($this->_current_row);
    }

}