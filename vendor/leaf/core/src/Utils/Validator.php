<?php
namespace Leaf\Core\Utils;

use Leaf\Core\Config;
use Leaf\Core\Exception;

/**
 *                                     ВАЛИДАТОР
 * Сообщение по умолчанию, а так же разделители для ошибок можно изменить в файле config/validation.php
 * 
 *                                      ПРИМЕР
 * 
 *    $test = array('n' => 'roMan', 'email' => 'itoktor@gmail.com');
 * 
 *    $valid = Validator::make($test)
 *                 ->rules('n', array('trim', 'required', 'alpha(_)', 'length(,10)'), 'name')
 *                 ->rules('email', array('trim', 'email', 'length(5,15)'))
 *                 ->messages(array('email' => 'Парниша твой ":label" не катит!!!'));
 *      
 *    if($valid->check()){
 *          $test = $val->data();   // Если валидация прошла успешно, получаем обработаные данные
 *    }else{
 *          echo $valid->as_string();   // Если возникли ошибки, выводим их в виде строки
 *    }
 *  
 *                                   СПИСОК ФУНКЦИЙ
 *           - require           - email                    - matches
 *           - integer           - ip                       - date
 *           - float             - bool                     - length
 *           - url               - alpha                    - exact_length
 *
 * 
 * @package    Core
 * @subpackage Utils
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Validator {
    
    /**
     * Массив данных на валидацию.
     *
     * @var array
     */
    protected $data = array();
        
    /**
     * Правила валидации.
     *
     * @var array Правила валидации.
     */
    protected $rules = array();
    
    /**
     * Хранилище для ошибок.
     *
     * @var array
     */
    protected $errors = array();
    
    /**
     * Настройки для ошибок(сообщения).
     *
     * @var array
     */
    protected $error_configs = NULL;
    
    /**
     * Фабрика для валидации
     * 
     * @param array $values Данные на проверку.
     * @return Validator
     */
    public static function make(array $values) {
        return new Validator($values);
    }
    
    /**
     * Сохранение данных локально, загрузка настроек для ошибок.
     * 
     * @param array $values Данные на проверку.
     * @return void
     * @uses Config::read() 
     */
    public function __construct(array $values) {
        $this->data($values);
        if (empty($this->error_configs)) {
            $this->error_configs = Config::read('validation');
        }
    }
    
    /**
     * Установка правил валидации.
     * 
     *                     Возможны 2 варианта:
     *      1) ->rules('name', array('required', 'max_length(30)'), 'user_name') 
     *      
     *      2) $rules = array(
     *              array(
     *                  'field' => 'password',
     *                  'rules' => array('required', 'alpha', integer(4,8))                       
     *              ),
     *              array(
     *                  'field' => 'confirm_password'
     *                  'rules' => array('matches(password)')
     *              )
     *         );
     * 
     *         ->rules($rules)
     *  
     * 
     * @param string|array $field Название поля или массив с правилами.
     * @param array $rules Правила валидации.
     * @param type $label Псевдоним для поля.
     * @return Validator
     * @throws Exception
     */
    public function rules($field, $rules = NULL, $label = NULL){
		
	if (is_array($field)) {
            foreach ($field as $row) {
                if (!isset($row['field']) OR ! isset($row['rules'])) {
                    throw new Exception('Правила валидации заданы не верно. Проверьте входящие данные.');
		}
                
                $label = (isset($row['lable'])) ? $row['lable'] : NULL;
		$this->rules($row['field'], $row['rules'], $label);
            }
            return $this;
        }
		
	if (!is_string($field) OR  ! is_array($rules)) {
            throw new Exception('Правила валидации заданы не верно. Проверьте входящие данные.');
        }

	$label = (empty($label))? $field : $label;
	$this->rules[] = array(
                        'field' => $field, 
                        'rules' => $rules,
                        'label' => $label
	);
        
        return $this;
    }
    
    /**
     * Проверка данных, используя заданные правила валидации.
     * 
     * @return boolean True - прошла, false - нет.
     * @throws Exception
     */
    public function check(){
        if (empty($this->rules)) {
            throw new Exception('Правила валидации не заданы.');
        }
		
	foreach ($this->rules as $row) {
            $this->checkRule($row);	
	}

        return empty($this->errors)? true: false;
    }
    
    /**
     * Проверка одного поля.
     * 
     * @param array $field Правила для текущего поля.
     * @return void
     * @throws Exception
     */
    protected function checkRule($field){
        if (!isset($this->data[$field['field']])) {
            throw new Exception('Данные '.$field['field'].' не найдены.');
        }
        
	foreach ($field['rules'] as $rule) {
            $param = null;
            $match = array();
            
            if (preg_match("/(.*?)\((.*?)\)/", $rule, $match)) {
		$rule = $match[1]; //Правило валидации
		$param = $match[2]; //Параметры
            }
                        
            if (!method_exists($this, $rule)) {
		if (function_exists($rule)) {
                    $result = $rule($this->data[$field['field']]);
			
                    if (!is_bool($result)) {
                        $this->data[$field['field']] = $result; 
                    }						
		}
            } else {
                $result = $this->$rule($this->data[$field['field']],$param);
                if ($result === false) {
                    if (empty($this->error_configs['messages'][$rule])) {
                        throw new Exception('Сообщение об ошибке для '.$rule.' не найдено');
                    }
                    $error = strtr($this->error_configs['messages'][$rule],array(':label' => $field['label']));
                    $this->errors[] = $error;
                }
			
            }
	}
    }
    
    /**
     * Если ничего не передавать работает как геттер(отправляет массив всех данных).
     * При передаче массива работает как сеттер(добавляет этот массив).
     * При передаче значение отправляет параметр с таким ключем.
     * 
     *      $validator->data('id'); // получим значение в ячейке 'id'
     * 
     * @param mixed $data Массив данных или ключ.
     * @return mixed 
     */
    public function data($data = NULL) {
        if ($data === NULL) {
            return $this->data;
        } else if(is_array($data)) {
            Arr::merge($this->data, $data);
        } else {
            return $this->data[$data];
        }
        
        return $this;
    }
    
    /**
     * Установка пользовательських сообщений об ошибках.
     * 
     *      ->messages(array('required' => 'Ай-йа-йай, а поле ":label" то пустое!!!'))
     * 
     * @param array $messages Сообщения.
     * @return Validator
     * @throws Exception
     */
    public function messages(array $messages) {
        foreach ($messages as $key => $val) {
            if (!stripos($val, ':label')) {
                throw new Exception('Сообщения заданы не верно!!!');
            } else {
                $this->error_configs['messages'][$key] = $val;
            }
        }
        
        return $this;
    }
    
    /**
     * Возвращает ошибки в виде массива. 
     * 
     * @return array Массив ошибок.
     */
    public function asArray(){
        return $this->errors;
    }
    
    /**
     * Возвращает ошибки в виде строки. 
     * Присутствует возможность задавать разделители(префикс и суфикс) 
     * 
     * @param string $prefix Разделитель справа.
     * @param string $suffix Разделитель слева.
     * @return string Ошибки в виде строки.
     */
    public function asString($prefix = NULL, $suffix = NULL) {
        if (!count($this->errors)) {
            return '';
	}
        
        if (empty($prefix)) {
            $prefix = $this->error_configs['delimiters']['prefix'];
    	}

	if (empty($suffix)){
            $suffix = $this->error_configs['delimiters']['suffix'];
    	}
	
        $str = '';
	foreach ($this->errors as $error) {
            $str .= $prefix.$error.$suffix."\n";
			
	}
		
	return $str;
    }

    /**
     * Проверяет, что значение является не пустым.
     *
     * @param string $str Значение на проверку.
     * @return boolean
     */
    protected function required($str){
        return !in_array($str, array(null, false, '', array()), true);
    }
    
    /**
     * Проверяет, что значение является корректным целым числом,
     * и, при необходимости, входит в определенный диапазон.
     * 
     *      integer(1,5) - значение в диапазоне от 1 до 5 включая 
     *      integer(1) - значение не меньше 1
     *      integer(,5) - значение не больше 5
     * 
     * @param string $str Значение на проверку.
     * @param string $params Параметры в виде строки
     * @return boolean
     */
    protected function integer($str, $params = null){
        if (!empty($params)) {
            $params = explode(',', $params);
            if (!empty($params['0'])) {
                $params += array('min_range' => $params['0']);
            }
            
            if (!empty($params['1'])) {
                $params += array('max_range' => $params['1']);
            }
        }

        return filter_var($str, FILTER_VALIDATE_INT, array('options' => $params));
    }
    
    /**
     * Проверяет, что значение является корректным числом с плавающей точкой.
     *
     *      float(.) - проверка является ли "." десятичным разделителем 
     * 
     * @param string $str Значение на проверку.
     * @param string $params Параметры в виде строки.
     * @return boolean
     */
    protected function float($str, $params = null){
        if(!empty($params)){
            $params = array('decimal' => trim($params));
        }
	return filter_var($str, FILTER_VALIDATE_FLOAT, array('options' => $params));
    }
    
    
    
    /**
     * Проверяет значение на корректность URL.
     *
     * @param string $str Значение на проверку.
     * @return boolean
     */
    protected function url($str){
	return filter_var($str, FILTER_VALIDATE_URL);
    }
	
	
    /**
     * Проверяет, что значение является корректным e-mail.
     *
     * @param string $str Значение на проверку.
     * @return boolean
     */
    protected function email($str){
	return filter_var($str, FILTER_VALIDATE_EMAIL);
    }
	
	
    /**
     * Проверяет, что значение является корректным IP-адресом.
     *
     * @param string $str Значение на проверку.
     * @return boolean
     */
    protected function ip($str){
	return filter_var($str, FILTER_VALIDATE_IP);
    }
	
    /**
     * Возвращает TRUE для значений "1", "true", "on" и "yes".
     * Иначе - FALSE.
     *
     * @param string $str Значение на проверку.
     * @return boolean
     */
    protected function bool($str){
	return filter_var($str, FILTER_VALIDATE_BOOLEAN);
    }
		
    /**
     * Проверяет значение на наличие символов кроме букв латинского алфавита,
     * а так же символов, которые можно передать в виде параметров.
     * 
     *      alpha(/, _, #) - подходят строки с латинскими буквами и "/", "_", "#"
     *
     * @param string $str Значение на проверку.
     * @param string $params Параметры в виде строки.
     * @return boolean
     */		
    protected function alpha($str, $params = NULL){
        $str = trim($str);
        if (empty($params)) {
            $params = '';
        } else {
            $params = explode(',', $params);
            foreach ($params as &$param) {
                $param = preg_quote(trim($param), '/');
            }
            $params = implode($params);
        }
	return (!preg_match("/^([a-z".$params."])+$/i", $str)) ? false : true;
    }
    
    /**
     * Проверяет значение на соответствие переданым значениям.
     * 
     *      matches(password, confirm_password) - проверка на еквивалентность значениям
     *                                            полей password, confirm_pasword
     *
     * @param string $str Значение на проверку.
     * @param string $params Параметры в виде строки.
     * @return boolean
     */		
    protected function matches($str, $params = null){
        $params = explode(',', $params);
        $str = trim($str);
        foreach ($params as $param) {
            $param = trim($param);
            if (trim($this->_data[$param]) !== $str) {
                return false;
            }
        }
	return true;
    }
	
    /**
     * Проверяет дату на корректность.
     *
     * @param string $str Значение на проверку.
     * @return boolean
     */
    protected function date($str){
	return (strtotime($str) !== false);
    }
        
    /**
     * Проверяет длину значения.
     * 
     *      length(4,5) - строка в диапазоне от 4 до 5 символов включая,
     *      length(4) - строка не меньше 4 символов,
     *      length(,5) - строка не больше 5 символов.
     *
     * @param string $str Значение на проверку.
     * @param string $params Параметры в виде строки.
     * @return boolean
     */	  
    protected function length($str, $params = null){
        if (!empty($params)) {
            $params = explode(',', $params);
            foreach ($params as &$value) {
                $value = trim($value);
            }
            $str = trim($str);
            if (!empty($params[0]) and (strlen($str) < $params[0])) {
                return false;
            }

            if (!empty($params[1]) and (strlen($str) > $params[1])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Проверяет длину значения.
     * 
     *      exact_length(4) - строка должна быть длиной 4 символа,
     *      exact_length(2,5,56) - строка должна быть длиной 2 или 5 или 56 символов.
     *
     * @param string $str Значение на проверку.
     * @param string $params Параметры в виде строки.
     * @return boolean
     */
    protected function exact_length($str, $params = null){
        if (!empty($params)) {
            $params = explode(',', $params);
            $str = trim($str);
            foreach ($params as $param) {
                if (strlen($str) === (int)(trim($param))){
                    return true;
		}
            }
            return false;
        }
        
	return true;
    }
}