<?php
/**
 * Cодержит методы, которые помогают работать с HTML.
 *
 * @package Helpers
 * @author Selfless
 * @since 1.2
 */

abstract class Html{
    
    /**
     * @var array массив с параметрами в правильной последовательности для сортировки. 
     */
    public static $attribute_order = array(
		'action',
		'method',
		'type',
		'id',
		'name',
		'value',
		'href',
		'src',
		'width',
		'height',
		'cols',
		'rows',
		'size',
		'maxlength',
		'rel',
		'media',
		'accept-charset',
		'accept',
		'tabindex',
		'accesskey',
		'alt',
		'title',
		'class',
		'style',
		'selected',
		'checked',
		'readonly',
		'disabled',
	);
    
    /**
     * Выбор и установка пути.
     * 
     * @param string $name - папка и имя/имя файла на который нужно установить путь.
     * @return string 
     * @uses Config::get()
     */
    public static function href($name){
        //Проверка на допустимые символы и обрезание слэшей.
        $name = preg_replace('~^[-a-z0-9+.]++://[^/]++/?~', '', trim($name,DS));
        $base_url = Config::get('system.base_url');
        
        if(strpos($name,'.') === FALSE){
            $result = $base_url.$name;
        }else{
            $tpl = Config::get('system.template');
            $result = $base_url.'templates'.DS.$tpl.DS.$name;    
        }
 
        return $result;
    }

    /**
     * Подключение нужного стиля.
     * 
     *      Html::style(css/style.css) или Html::style(/styles/style.css).
     * 
     * @param string $name - папка хранения стиля внутри шаблона и название стиля.
     * @return string 
     */
    public static function style($name){
        $attr = array('href' => self::href($name), 'rel' => 'stylesheet', 'type' => 'text/css');
        $result = '<link'.self::attr($attr).' />'."\n";   
        return $result;
    }
    
    /**
     * Подключение нужного скрипта.
     * 
     *      Html::style(js/script.js) или Html::script(/scripts/script.js).
     * 
     * @param string $name - папка хранения крипта внутри шаблона и название скрипта.
     * @return string 
     */
    public static function script($name){
        $attr = array('src' => self::href($name), 'type' => 'text/javascript');
        $result = '<script'.self::attr($attr).'></script>'."\n";
        return $result;
    }
    
    /**
     * Подключение изображения.
     * 
     *      Html::img(img/1.img) или Html::img(/images/2.PNG).
     * 
     * @param string $name - папка хранения изображения внутри шаблона и название изображения.
     * @return string 
     */
    public static function img($name){
        $attr = array('src' => self::href($name));
        $result = '<img '.self::attr($attr).'/>' ;
        return $result;
    }
    
    /**
     * Вывод на страницу текста и установка на него ссылки.
     * 
     *      HTML::link('home','Домашняя страница') - будет написан текст "Домашняя страница" c ccылкой на контроллер "home".
     * 
     * @param string $link - ссылка.
     * @param string $title - выводимый на сраницу текст.
     * @return string 
     */
    public static function link($link, $title, $attr = array()){
        $attr += array('href' => self::href($link));
        $result = '<a'.self::attr($attr).'>'.$title.'</a>' ; 
        return $result;
    }
    
    /**
     * Составляет массив HTML атрибутов в строку, а также 
     * сортирует с помощью Html::$attribute_order для последовательности.
     * 
     * @param array $attr - список параметров.
     * @return string
     */
    public static function attr(array $attr = NULL){
 	if (empty($attr)){
            return '';
        }
        
	$sorted = array();
	foreach (self::$attribute_order as $key){
            if (isset($attr[$key])){
		// Добавляем атрибуты до сортировочного листа.
		$sorted[$key] = $attr[$key];
            }
	}

	// Совмещаем отсортированые атрибуты.
	$attr = $sorted + $attr;

	$compiled = '';
	foreach ($attr as $key => $value){
            if ($value === NULL){
		// Пропускаем атрибуты,значени которых равно NULL.
		continue;
            }

            if (is_int($key)){
		$key = $value;
            }

            // Добавляем ключ атрибута.
            $compiled .= ' '.$key;

            if ($value){
            // Добавляем значение атрибута.
            $compiled .= '="'.$value.'"';
            }
	}
        return $compiled;       
    }
}