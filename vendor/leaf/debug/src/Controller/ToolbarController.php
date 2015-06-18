<?php
namespace Leaf\Debug\Controller;

use Leaf\Core\Leaf;
use Leaf\Debug\Variable;
use Leaf\Core\Mvc\Layout;
use Leaf\Core\Utils\Html;
use Leaf\Core\Config\Config;

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
        return Leaf::make('toolbar/run')->execute()->getBody();
    }

    /**
     *
     */
    public function first() {
        $this->template = self::$configs['template'];
        $this->template_path = LEAF_PATH.'debug';
        parent::first();
    }

    /*
     *
     */
    public function runAction() {
        $this->layout->set('html', new Html($this->layout->getLayoutPath()));

        foreach (self::$configs['tabs'] as $name => $tab) {
            $configs = array('link' => $name);
            if(isset($tab['configs'])){
                $configs += $tab['configs'];
            }

            $tab_keys['name'] = $tab['name'];
            $tab_keys['logo'] = $tab['logo'];
            $tab_keys['position'] = $tab['position'];

            $this->layout->set('tab_keys.'.$name ,$tab_keys);
            
            $this->{$tab['method']}($configs);
        }

        $files = $this->layout->html->style('css'.DS.'tabulous.css', false);
        $files .= $this->layout->html->script('js'.DS.'jquery-2.1.3.min.js');
        $files .= $this->layout->html->script('js'.DS.'jquery-ui.min.js');
        $files .= $this->layout->html->script('js'.DS.'jquery.cookie.js');
        $files .= $this->layout->html->script('js'.DS.'tabulous.js');
        $this->layout->set('files', $files);

        $this->layout->set('memory', round(LEAF_START_MEM/1048576,2).' MB');
        $this->layout->set('time', round((microtime(true) - LEAF_START_TIME), 4). ' s');
    }

    /**
     *
     * @param array $configs
     */
    public function messages(array $configs){
        $msgs = array();
        foreach (self::$msgs as $msg) {
            if(is_array($msg) or is_object($msg)){
                $msgs[] = array('header' => strstr(Variable::dump($msg), '{', true), 'body' => strstr(Variable::dump($msg), '{'));
            }else{
                $msgs[]['header'] = Variable::dump($msg);
            }
        }
        
        if (isset($configs['count']) and $configs['count'] == true and count($msgs)) {
            $this->layout->set('tab_keys.'.$configs['link'].'.info' ,count($msgs));
        }

        $this->layout->partial('views'.DS.'messages', array('messages' => $msgs), 'tabs.'.$configs['link']);
    }

    /**
     *
     * @param array $configs
     */
    public function files(array $configs){
        $files = (array)get_included_files();
        $files = array_map(
            function($file){
                return substr($file, mb_strlen($_SERVER['DOCUMENT_ROOT']) + 1);
            }
        , $files);

        if (isset($configs['count']) and $configs['count'] == true) {
            $this->layout->set('tab_keys.'.$configs['link'].'.info', count($files));
        }
        
        $this->layout->partial('views'.DS.'files', array('files' => $files), 'tabs.'.$configs['link']);
    }

    /**
     *
     * @param array $configs
     */
    public function globals(array $configs){
        $globals = array();
        foreach ($configs['globals'] as $global) {
            $global = '_'.strtoupper($global);
            if (isset($GLOBALS[$global]) and empty($globals[$global])) {
                $globals[substr($global, 1)] = strstr(Variable::dump($GLOBALS[$global]), '{');
            }
        }
        
        $this->layout->partial('views'.DS.'globals', array('globals' => $globals), 'tabs.'.$configs['link']);
    }
}
