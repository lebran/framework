<?php
namespace Lebran\Mvc\View\Extension;

/**
 * This class provides easy way include assets (styles, scripts and images).
 *
 * @package    Mvc
 * @subpackage View
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Assets implements ExtensionInterface
{
    /**
     * @var array Preferred order of attributes.
     */
    protected static $attributes = [
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
    ];

    /**
     * @var string  Path to assets folder.
     */
    public $folder;

    /**
     * Gets the name of extension.
     *
     * @return string Extension name.
     */
    public function getName()
    {
        return 'assets';
    }

    /**
     * Gets array: alias => method name.
     *
     * @return array An array of methods name.
     */
    public function getMethods()
    {
        return [
            'css'  => 'style',
            'js'   => 'script',
            'img'  => 'image',
            'attr' => 'attr'
        ];
    }

    /**
     * Initialisation. Sets folder.
     *
     * @param string $folder Path to folder.
     */
    public function __construct($folder = '')
    {
        $this->folder = '/'.trim(trim($folder), '/').'/';
    }

    /**
     * Creates a style sheet link element.
     *
     * <code>
     *      <?=$this->style('css/style.css')?>
     * </code>
     *
     * @param string $name The relative path to the file from a folder.
     *
     * @return string Style sheet link
     */
    public function style($name)
    {
        $attr = ['href' => $this->folder.$name, 'rel' => 'stylesheet', 'type' => 'text/css'];
        return '<link'.$this->attr($attr).'/>'.PHP_EOL;
    }

    /**
     * Creates a script link.
     *
     *      <?=$this->script('js/validator.js')?>
     *
     * @param string $name The relative path to the file from a folder.
     *
     * @return string Script link.
     */
    public function script($name)
    {
        $attr = ['src' => $this->folder.$name, 'type' => 'text/javascript'];
        return '<script'.$this->attr($attr).'></script>'.PHP_EOL;
    }

    /**
     * Creates a image link.
     *
     *      <?=$this->img('images/logo.png', array('alt' => 'logo'))?>
     *
     * @param string $name The relative path to the file from a folder.
     * @param array  $attr An array of attributes for image.
     *
     * @return string Image link.
     */
    public function image($name, $attr = [])
    {
        $attr += ['src' => $this->folder.$name];
        return '<img '.$this->attr($attr).'/>';
    }

    /**
     * Compiles an array of HTML attributes into an attribute string.
     *
     * @param array $attr An array of parameters.
     *
     * @return string Compiled parameters.
     */
    public function attr(array $attr = null)
    {
        if (empty($attr)) {
            return '';
        }

        $sorted = array();
        foreach (self::$attributes as $key) {
            if (isset($attr[$key])) {
                $sorted[$key] = $attr[$key];
            }
        }
        $attr     = $sorted + $attr;
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