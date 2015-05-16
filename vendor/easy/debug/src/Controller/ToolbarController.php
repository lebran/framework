<?php
namespace Easy\Debug\Controller;

use Easy\Core\Config;
use Easy\Core\Utils\View;
use Easy\Core\Utils\Html;
use Easy\Core\Utils\Layout;
use Easy\Debug\Toolbar;
use Easy\Debug\Vr;

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
    public function first() {
        $this->template = Config::get('toolbar.template');
        parent::first();
    }

    /*
     *
     */
    public function runAction() {
        if(!Config::get('toolbar.enabled')){
            $this->render = false;
            return;
        }

        $this->layout->template = $this->template;

        $tab_keys = array();
        $tabs = Config::get('toolbar.tabs');
        foreach ($tabs as $name => $tab) {
            $configs = array('link' => $name);
            if(isset($tab['configs'])){
                $configs += $tab['configs'];
            }
            $this->{$tab['method']}($configs);
            $tab_keys[$name]['name'] = $tab['name'];
            $tab_keys[$name]['logo'] = Html::href($tab['logo']);
            $tab_keys[$name]['position'] = $tab['position'].'-tab';
        }
        $this->layout->set('tab_keys', $tab_keys);

        //$style_path = EASY_PATH.'debug'.DS.'templates'.DS.$this->template.DS.'css'.DS.'tabulous.css';
        $style_path = TPL_PATH.$this->template.DS.'css'.DS.'tabulous.css';
        $files = "<style".Html::attr(array('type'=>'text/css')).">".file_get_contents($style_path)."</style>\n";
        $files .= Html::script('js'.DS.'jquery-2.1.3.min.js');
        $files .= Html::script('js'.DS.'tabulous.js');
        $this->layout->set('files', $files);
    }

    public function messages(array $configs){
        $msgs = array();
        foreach (Toolbar::$msgs as $msg) {
            if(is_array($msg)){
                $msgs[] = array('header' => 'Array', 'body' => Vr::dump($msg));
            }else{
                $msgs[]['header'] = Vr::dump($msg);
            }
        }
        $this->layout->tabs[$configs['link']] = View::make('messages')->set('messages', $msgs)->render();
    }

    public function files(array $configs){
        $files = (array)get_included_files();
        $this->layout->tabs[$configs['link']] = View::make('files')->set('files', $files)->render();
    }
}
