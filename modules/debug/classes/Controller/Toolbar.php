<?php

/**
 * Description of Debug
 *
 * @author Roma
 */
class Controller_Toolbar extends Controller_Layout{
    
    public function first() {
        $this->template = Config::get('toolbar.template');
        parent::first();
    }
    
    /*
     * 
     */
    public function run_action() {
        if(!Config::get('toolbar.enabled')){
            $this->render = FALSE;
            return;
        }
        
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
        
        
        Easy_Core::include_path(TPL_PATH.$this->template.DS);
        $style_path = Easy_Core::find_file('css', 'tabulous', FALSE, 'css');
        $files = "<style".Html::attr(array('type'=>'text/css')).">"
                .file_get_contents($style_path)
                ."</style>\n";
        $files .= Html::script('js'.DS.'jquery-2.1.3.min.js');
        $files .= Html::script('js'.DS.'tabulous.js');
        $this->layout->set('files', $files);
    }
    
    public function messages(array $configs){
        foreach (Debug::get_msgs() as $msg) {
            if(is_array($msg)){
                $msgs[] = array('header' => 'Array', 'body' => substr(Debug_Var::dump($msg), 11));
            }else{
                $msgs[]['header'] = Debug_Var::dump($msg);
            }
        }
        $this->layout->tabs[$configs['link']] = View::make('messages')->set('messages', $msgs)->render();
    }
    
    public function files(array $configs){
        $files = (array)get_included_files();
        $this->layout->tabs[$configs['link']] = View::make('files')->set('files', $files)->render();
    }
}
