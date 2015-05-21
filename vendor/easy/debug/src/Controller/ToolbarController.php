<?php
namespace Easy\Debug\Controller;

use Easy\Debug\Vr;
use Easy\Core\Config;
use Easy\Core\Utils\Html;
use Easy\Core\Utils\Layout;
use Easy\Core\Http\Request;

/**
 *
 *
 * @package    Debug
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class ToolbarController extends Layout
{
    /**
     *
     * @var type
     */
    protected static $msgs = array();

    /**
     *
     * @var type
     */
    protected static $configs;

    /**
     *
     * @param type $msg
     */
    public static function msg($msg) {
        self::$msgs[] = $msg;
    }

    /**
     *
     * @return type
     */
    public static function render(){
        self::$configs = Config::read('toolbar');
        if(!self::$configs['enabled']){
            return;
        }
        return Request::make('toolbar'.DS.'run')->execute()->body();
    }

    /**
     *
     * @var type 
     */
    public $html;

    /**
     *
     */
    public function first() {
        $this->template = self::$configs['template'];
        $this->template_path = EASY_PATH.'debug';
        parent::first();
    }

    /*
     *
     */
    public function runAction() {
        $this->html = new Html($this->layout->getLayoutPath());

        $this->layout->set('html', $this->html);

        $tab_keys = array();
        foreach (self::$configs['tabs'] as $name => $tab) {
            $configs = array('link' => $name);
            if(isset($tab['configs'])){
                $configs += $tab['configs'];
            }
            $this->{$tab['method']}($configs);
            $tab_keys[$name]['name'] = $tab['name'];
            $tab_keys[$name]['logo'] = $tab['logo'];
            $tab_keys[$name]['position'] = $tab['position'].'-tab';
        }
        $this->layout->set('tab_keys', $tab_keys);

        $files = $this->html->styleContent('css'.DS.'tabulous.css');
        $files .= $this->html->script('js'.DS.'jquery-2.1.3.min.js');
        $files .= $this->html->script('js'.DS.'tabulous.js');
        $this->layout->set('files', $files);
    }

    /**
     *
     * @param array $configs
     */
    public function messages(array $configs){
        $msgs = array();
        foreach (self::$msgs as $msg) {
            if(is_array($msg) or is_object($msg)){
                $msgs[] = array('header' => strstr(Vr::dump($msg), '{', true), 'body' => strstr(Vr::dump($msg), '{'));
            }else{
                $msgs[]['header'] = Vr::dump($msg);
            }
        }
        $this->layout->tabs[$configs['link']] = $this->layout->partial('views'.DS.'messages', array('messages' => $msgs));
    }

    /**
     *
     * @param array $configs
     */
    public function files(array $configs){
        $files = (array)get_included_files();
        $this->layout->tabs[$configs['link']] = $this->layout->partial('views'.DS.'files', array('files' => $files));
    }
}
