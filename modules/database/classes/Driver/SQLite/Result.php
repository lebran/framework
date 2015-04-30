<?php

/**
 * Позволяет получить доступ к результатам, полученым от бд SQLite.
 *
 * @package Database
 * @author iToktor
 * @since 1.0
 */
class Driver_SQLite_Result extends Driver_Result {
    
    /**
     * Устанавливает общее количество строк и сохраняет результат на локальном уровне.
     *
     * @param mixed $result - результат запроса.
     */
    public function __construct($result){
	parent::__construct($result);

	$this->_total_rows = $this->_num_rows();
    }
    
    /**
     * Возвращает количество выбраных строк.
     * 
     * @return integer 
     */
    protected function _num_rows() {
        $nrows = 0;
        $this->_result->reset();
        while($this->_result->fetchArray()){
            $nrows++;
        }    
        $this->_result->reset();
        
        return $nrows;
    }
    
    /**
     * Очищает все открытые наборы результатов.
     */
    public function __destruct(){
	if (is_resource($this->_result)){
            $this->_result->finalize();
	}
    }
    
    /**
     * Переключает указатель на выбраную строку.
     * 
     * @param integer $offset - номер строки.
     * @return boolean
     */
    public function seek($offset){
	if($this->offsetExists($offset)){
            for($this->_result->reset(), $nrows = 0; $nrows < $offset;$nrows++){
                $this->_result->fetchArray();
            }
            
            $this->_current_row = $offset;
            return TRUE;
	}else{
            return FALSE;
	}
    }

    /**
     * Наследует [Iterator::current], возвращает текущий набор данных.
     *  
     *      $arr = current($result);
     * 
     * @return array
     */
    public function current(){
	if ($this->_current_row >= $this->_total_rows){
            return NULL;
        }
	
        return $this->_result->fetchArray(SQLITE3_ASSOC);	
    }
}
