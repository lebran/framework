<?php
namespace Leaf\Core\Utils;

use Leaf\Core\Utils\Arr;

/**
 * Cодержит методы, которые помогают работать с куки.
 * 
 * @package    Core
 * @subpackage Utils
 * @version    2.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Lisence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Cookie {
    
    /**
     * Параметры для кук.
     *
     * @var array
     */
    protected $params = array(
        // Время, когда срок действия cookie истекает
        'expiration' => 0,

        // Путь к директории на сервере, из которой будут доступны cookie
        'path' => '',

        // Домен, которому доступны cookie
        'domain' => null,

        //Указывает на то, что значение cookie должно передаваться от клиента по защищенному HTTPS соединению
        'secure' => false,

        // Если задано TRUE, cookie будут доступны только через HTTP протокол
        'httponly' => false
    );

    /**
     * Коструктор.
     *
     * @param array $params параметры кук
     */
    public function __construct($params = array())
    {
        $this->params = $params + $this->params;
    }

    /**
     * Возвращает значение куки по ключу или дефолтное, если куки не найден.
     *
     * @param string $name Имя куки.
     * @param mixed $default Значение, которое вернется, если куки не найден.
     * @return mixed Значение по ключу или default
     */
    public function get($name, $default = null)
    {
        return Arr::getAnnotation($name, $_COOKIE, $default);
    }

    /**
     * Устанавливает значение или масив кук по ключу.
     * 
     *      Cookie::set('test', array('1' => '1', '2' => '2'),
     *                  array('path' = > '/test/test', 'expiration' => 3600));
     * 
     * @param string $name Имя куки.
     * @param mixed $value Значение или масив.
     * @param array $params Параметры.
     * @return void
     */
    public function set($name, $value, $params = array())
    {        
        foreach ($this->params as $key => $val) {
            if (empty($params[$key])) {
                $params[$key] = $val;
            }
        }
        if ($params['expiration'] != 0) {
            $params['expiration'] += time();
        }

        $name = explode('.', $name);
        $temp_name = array_shift($name);
        $name = $temp_name.((empty($name))? '' : '['.implode('][', $name).']');

        if(!is_array($value)){
            setcookie($name , $value, $params['expiration'] , $params['path'], $params['domain'],$params['secure'], $params['httponly']);
        } else {
            foreach (Arr::asAnnotation($value, '', '][') as $n => $v) {
                setcookie($name.substr($n, 1).']' , $v, $params['expiration'] , $params['path'], $params['domain'],$params['secure'], $params['httponly']);
            }
        }
    }

    /**
     * Удаление куки по ключу.
     * 
     * @param string $name Имя куки.
     * @param array $params Параметры с которыми обьявляли куки.
     * @return void
     */
    public function delete($name, $params = array())
    {
        if (!($delete = Arr::getAnnotation($name, $_COOKIE))) {
            return;
        }

        array_walk_recursive($delete, function (&$item){
            $item = '';
        });

        $params = array_merge($params, array('expiration' => 1));
        $this->set($name, $delete, $params);
    }
}