<?php
/**
 *  Мини-шаблонизатор: передает данные в шаблон и рендерит их.
 * 
 *                             ПРИМЕР
 *      
 *      // Создаем массив данных 
 *      $data = array( 'name' => 'Roman', 'age' => '27' );
 *      
 *      $view = View::make('index') // Выбираем вид
 *                  ->set($data)      // Устанавливаем 
 *                  ->set('n', 'Ann') // значения
 *                  ->render();       // Рендерим
 *                       
 *                      (Запрос внутри запроса)
 *      // Делаем запрос к контроллеру coments действию show.
 *      // Добавляем данные в соответствующую ячейку в обертке
 *      $this->layout->coments = Request::make('/coments/show')
 *                                     ->execute() 
 *                                     ->body();
 * 
 *      // Кидаем наш вид в главный шаблон под псевдонимом content
 *      $this->layout->content = $view;
 * 
 * @package Base
 * @author iToktor
 * @since 1.2.5
 */
class View {
    /**
     * @var string полный путь к виду.
     */
    protected $_view;
    	
    /**
     * @var array хранилище для переменных вида.
     */
    protected $_vars = array() ;
    
    /**
     * Фабрика для видов 
     * 
     * @param string $view - имя вида.
     * @param  $path - путь.
     * @return View
     */
    public static function make($view = NULL, $path = NULL){
        return new View($view, $path);
    }

    /**
     * Подготовка полного пути для файла вида.
     * 
     * @param string $view - имя вида.
     * @param string $path - путь.
     * @uses Config::get()
     * @uses Request::current()
     */
    public function __construct($view, $path) {
        
        $template = Config::get('system.template');
        
        if(empty($view)) {
            $view = Request::current()->controller().DS.Request::current()->action() ;
	}
        
        if(empty($path)){
            $this->_view = TPL_PATH.$template.DS.'view'.DS.trim($view, DS).'.php';
        }else{
            $this->_view = trim($path, DS).DS.trim($view, DS).'.php';
        }
    }
		
    /**
     * Назначение переменных вида
     * @param mixed $var
     *		1) если string, тогда имя переменной в виде,
     *		2) если array, то массив array( 'var' => 'value' )
     * @param mixed $value - значение переменной вида для случая (1)
     * @return View 
     * @uses Arr::merge()
     */
    public function set( $var, $value = NULL ) {
        if( is_array( $var ) ) { 
            Arr::merge($this->_vars, $var);
        } else {
            $this->_vars[ $var ] = $value ;
	}
        
        return $this;
    }
    
    
		
    /**
     * Рендеринг Вида
     * 
     * @return string
     * @uses Config::get()
     */
    public function render() {			
        $prefix = Config::get('system.view_prefix');
        if(empty($prefix)){
            extract( $this->_vars) ;
        }else{
            extract( $this->_vars, EXTR_PREFIX_ALL, $prefix) ;
        }
        
        ob_start();
        include $this->_view;
	$view = ob_get_clean();
               
        return $view;		
    }
    
    /**
     * Сеттер
     * 
     * @param string $name - имя переменной 
     * @param string $value - значение
     */
    public function __set($name, $value) {
        $this->_vars[$name] = $value;   
    }
    
    /**
     * Геттер
     * 
     * @param string $name - имя переменной 
     */
    public function &__get($name) {
        return $this->_vars[$name];
    }
    
    /**
     * Удаление несуществующих переменных.
     * 
     * @param string $name - имя удаляемой ячейки.
     */
    public function __unset($name) {
        unset($this->_vars[$name]);
    }
    
}