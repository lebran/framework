<?php
namespace Leaf\Core\Utils;

use Leaf\Core\Config;

/**
 * Cодержит методы, которые помогают работать с HTML.
 *
 * @package    Core
 * @subpackage Utils
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */

class Html{
    
    /**
     * Массив с параметрами в правильной последовательности для сортировки.
     *
     * @var array 
     */
    public $attribute_order = array(
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

    protected $path;

    public static function make($path)
    {
        return new self($path);
    }

    public function __construct($path)
    {
        $this->path = trim($path, DS).DS;
    }

    public function getPath($name ,$tag)
    {
        $name = trim($name, DS);
        switch ($tag) {
            case 'link':
                return Config::get('system.base_url').$name;
            case 'img':
            case 'script':
                return Config::get('system.base_url').trim(substr($this->path, mb_strlen($_SERVER['DOCUMENT_ROOT'])), DS).DS.$name;
            case 'style':
            default:
                return $this->path.$name;
        }
    }

    /**
     * Подключение нужного стиля.
     * 
     *      Html::style(css/style.css) или Html::style(/styles/style.css).
     * 
     * @param string $name Папка хранения стиля внутри шаблона и название стиля.
     * @return string Собраная тег подключения стиля.
     */
    public function style($name, $link = true)
    {
        if ($link) {
            return '<link'.$this->attr(array('href' => $this->getPath($name, 'style'), 'rel' => 'stylesheet', 'type' => 'text/css')).' />'."\n";
        } else {
            return '<style'.$this->attr(array('type'=>'text/css')).'>'.file_get_contents($this->getPath($name, 'style')).'</style>'."\n";
        }        
    }

    /**
     * Подключение нужного скрипта.
     * 
     *      Html::style(js/script.js) или Html::script(/scripts/script.js).
     * 
     * @param string $name Папка хранения крипта внутри шаблона и название скрипта.
     * @return string Собраная тег подключения скрипта.
     */
    public function script($name, $link = true)
    {
        if ($link){
            return '<script'.$this->attr(array('src' => $this->getPath($name, 'script'), 'type' => 'text/javascript')).'></script>'."\n";
        } else {
            return '<script>'.file_get_contents($this->getPath($name, 'script')).'</script>';
        }
    }
    
    /**
     * Подключение изображения.
     * 
     *      Html::img(img/1.img) или Html::img(/images/2.PNG).
     * 
     * @param string $name Папка хранения изображения внутри шаблона и название изображения.
     * @return string Собраная тег подключения изображения.
     */
    public function img($name, $attr = array())
    {
        $attr += array('src' => $this->getPath($name, 'img'));
        return '<img '.$this->attr($attr).'/>';
    }
    
    /**
     * Вывод на страницу текста и установка на него ссылки.
     * 
     *      HTML::link('home','Домашняя страница') - будет написан текст "Домашняя страница" c ccылкой на контроллер "home".
     * 
     * @param string $link Ссылка.
     * @param string $title Выводимый на сраницу текст.
     * @return string Собраная тег подключения ссылки.
     */
    public function link($link, $title, $attr = array())
    {
        $attr += array('href' => $this->getPath($link, 'link'));
        return '<a'.$this->attr($attr).'>'.$title.'</a>';
    }
    
    /**
     * Составляет массив HTML атрибутов в строку, а также 
     * сортирует с помощью Html::$attribute_order для последовательности.
     * 
     * @param array $attr Список параметров.
     * @return string Скомпилированные параметры.
     */
    public function attr(array $attr = null)
    {
 	if (empty($attr)) {
            return '';
        }
        
	$sorted = array();
	foreach ($this->attribute_order as $key) {
            if (isset($attr[$key])) {
		$sorted[$key] = $attr[$key];
            }
	}

	$attr = $sorted + $attr;

	$compiled = '';
	foreach ($attr as $key => $value) {
            if ($value === null) {
		continue;
            }

            if (is_int($key)) {
		$key = $value;
            }
            $compiled .= ' '.$key;
            if ($value) {
                $compiled .= '="'.$value.'"';
            }
	}
        return $compiled;       
    }
}