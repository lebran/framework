<?php
namespace Easy\Debug;

use Easy\Core\Config;
use Easy\Core\Utils\View;
use Easy\Core\Utils\Html;
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
class Toolbar
{
    /**
     *
     * @var type
     */
    public static $msgs = array();

    /**
     *
     * @param type $msg
     */
    public static function msg($msg) {
        self::$msgs[] = $msg;
    }

    public static function render(){
        return Request::make('toolbar'.DS.'run')->execute()->body();
    }

    /*
    public $layout;

    public $tpl_path;

    public $files_path;

    public function __construct()
    {  
        $this->tpl_path = EASY_PATH.'debug'.DS.'templates'.DS.self::$configs['template'];
        $this->files_path = Config::get('system.base_url').'vendor/easy/'.'debug'.DS.'templates'.DS.self::$configs['template'];
        $this->layout = View::make('index', $this->tpl_path);
    }

    public function run()
    {
        $tab_keys = array();
        foreach (self::$configs['tabs'] as $name => $tab) {
            $configs = array('link' => $name);
            if(isset($tab['configs'])){
                $configs += $tab['configs'];
            }
            $this->{$tab['method']}($configs);
            $tab_keys[$name]['name'] = $tab['name'];
            $tab_keys[$name]['logo'] = '';//Html::href($tab['logo']);
            $tab_keys[$name]['position'] = $tab['position'].'-tab';
        }
        $this->layout->set('tab_keys', $tab_keys);

        $files = '<style'.Html::attr(array('type'=>'text/css')).">".file_get_contents($this->tpl_path.DS.'css'.DS.'tabulous.css').'</style>'."\n";
        $files .= '<script'.Html::attr(array('src' => $this->files_path.DS.'js'.DS.'jquery-2.1.3.min.js', 'type' => 'text/javascript')).'></script>'."\n";
        $files .= '<script'.Html::attr(array('src' => $this->files_path.DS.'js'.DS.'tabulous.js', 'type' => 'text/javascript')).'></script>'."\n";

        $this->layout->set('files', $files);
        print_r($this->layout->render());
        //return $this->layout->tabs;
    }

    public function messages(array $configs){
        $msgs = array();
        foreach (self::$msgs as $msg) {
            if(is_array($msg)){
                $msgs[] = array('header' => 'Array', 'body' => Vr::dump($msg));
            }else{
                $msgs[]['header'] = Vr::dump($msg);
            }
        }
        $this->layout->tabs[$configs['link']] = View::make('messages', $this->tpl_path.DS.'view')->set('messages', $msgs)->render();
    }
    
    public function files(array $configs){
        $files = (array)get_included_files();
        $this->layout->tabs[$configs['link']] = View::make('files', $this->tpl_path.DS.'view')->set('files', $files)->render();
    }*/
}
