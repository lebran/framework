<?php
namespace Leaf\Core\Utils;

/**
 * Вспомогательный класс для работы с  HTML.
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

    /**
     * Путь к папке шаблона.
     *
     * @var string
     */
    protected $path;

    /**
     * Устанавливает путь к папке шаблона.
     *
     *      $html = new Html($view->getLayoutPath());
     *
     * @param string $path Путь к папке шаблона.
     */
    public function __construct($path)
    {
        $this->path = trim($path, DS).DS;
    }

    /**
     * Отправляет путь в зависимости от тега.
     *
     *      $html->getPath('main.css', 'style');
     *
     * @param string $name Имя файла, ссылка.
     * @param string $tag Тег для которого отправлять путь.
     * @return string Путь в зависимости от тега.
     */
    public function getPath($name ,$tag)
    {
        $name = trim(trim($name, DS), '/');
        switch ($tag) {
            case 'link':
                return '/'.$name;
            case 'img':
            case 'script':
                return trim(substr($this->path, mb_strlen($_SERVER['DOCUMENT_ROOT'])), DS).DS.$name;
            case 'style':
            default:
                return $this->path.$name;
        }
    }

    /**
     * Отправляет строку подключения стиля или блок со вставленными стилями из файла.
     * 
     *      $html->style('css/style.css', false);
     * 
     * @param string $name Относительный путь к файлу стиля от пути к шаблону.
     * @param bool $link Отправлять строку или блок со вставленными стилями из файла?
     * @return string Строка подключения или блок со вставленными стилями из файла.
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
     * Отправляет строку подключения скрипта или блок со вставленным скриптом из файла.
     *
     *      $html->script('js/validator.js');
     *
     * @param string $name Относительный путь к файлу скрипта от пути к шаблону.
     * @param bool $link Отправлять строку или блок со вставленным скриптом из файла?
     * @return string Строка подключения или блок со вставленным скриптом из файла.
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
     * Отправляет строку подключения изображения.
     * 
     *      $html->img('images/logo.png', array('alt' => 'logo'));
     * 
     * @param string $name Относительный путь к файлу изображения от пути к шаблону.
     * @param array $attr Аттрибуты изображения.
     * @return string Строка подключения изображения.
     */
    public function img($name, $attr = array())
    {
        $attr += array('src' => $this->getPath($name, 'img'));
        return '<img '.$this->attr($attr).'/>';
    }
    
    /**
     * Отправляет тег ссылки(<a></a>).
     * 
     *      $html->link('home','Домашняя страница', array('class' => 'mylink'));
     * 
     * @param string $link Ссылка.
     * @param string $title Название ссылки.
     * @param array $attr Аттрибуты ссылки.
     * @return string Собраный тег ссылки.
     */
    public function link($link, $title, $attr = array())
    {
        $attr += array('href' => $this->getPath($link, 'link'));
        return '<a'.$this->attr($attr).'>'.$title.'</a>';
    }
    
    /**
     * Составляет массив HTML атрибутов в строку, а также 
     * сортирует с помощью $attribute_order для последовательности.
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