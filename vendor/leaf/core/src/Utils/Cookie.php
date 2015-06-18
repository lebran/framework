<?php
namespace Leaf\Core\Utils;

use Leaf\Core\Utils\Arr;

/**
 * Вспомогательный класс для работы с cookie. Поддерживает установку массива.
 * Используется точечная аннотация для доступа как к многомерному массиву.
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
     * Параметры для cookie.
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
     * Устанавливает параметры cookie.
     *
     *      $cookie = new Cookie(Config::read('cookie'));
     *
     * @param array $params Параметры cookie.
     */
    public function __construct($params = array())
    {
        $this->params = $params + $this->params;
    }

    /**
     * Отправляет значение cookie по ключу или default, если искомое - не найдено.
     *
     *      $cookie->get('user.about.name', 'noname');
     *
     * @param string $name Имя cookie.
     * @param mixed $default Значение, которое вернется, если искомое - не найдено.
     * @return mixed Значение по ключу или default.
     */
    public function get($name, $default = null)
    {
        return Arr::getAnnotation($name, $_COOKIE, $default);
    }

    /**
     * Устанавливает значение или массив cookie по ключу.
     * 
     *      $cookie->set('global.post', $_POST, Config::read('cookie'));
     * 
     * @param string $name Имя cookie.
     * @param string|array $value Значение cookie.
     * @param array $params Параметры cookie.
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
     * Удаляет cookie по ключу с заданными параметрами.
     * 
     * @param string $name Имя куки.
     * @param array $params Параметры с которыми обьявляли cookie.
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