<?php
namespace Easy\Core\Config;

/**
 *  
 * @package    Core\Config
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
interface ConfigInterface 
{            
    /**
     * 
     * @param type $name
     * @param type $set
     */
    public static function read($name);
    
}
