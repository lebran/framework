<?php
namespace Easy\Debug;

use Easy\Core\Config;

$configs = Config::read('toolbar', TRUE);

if($configs['enabled']){
    //Debug::init();
}