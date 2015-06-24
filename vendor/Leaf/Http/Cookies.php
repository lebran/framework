<?php
namespace Leaf\Http;

use Leaf\Utils\ArrayObject;

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
class Cookies extends ArrayObject
{
    /**
     * Параметры для cookies.
     *
     * @var array
     */
    protected $params = array(
        // Время, когда срок действия cookie истекает
        'expiration' => 0,
        // Путь к директории на сервере, из которой будут доступны cookie
        'path'       => '',
        // Домен, которому доступны cookie
        'domain'     => null,
        //Указывает на то, что значение cookie должно передаваться от клиента по защищенному HTTPS соединению
        'secure'     => false,
        // Если задано TRUE, cookie будут доступны только через HTTP протокол
        'httponly'   => false
    );

    /**
     * Устанавливает параметры cookies.
     *
     *      $cookies = new Cookies($params));
     *
     * @param array $params Параметры cookie.
     */
    public function __construct($params = array())
    {
        $this->array = &$_COOKIE;
        $this->params = $params + $this->params;
    }

    /**
     * Отправляет значение cookies по ключу или default, если искомое - не найдено.
     *
     *      $cookies->get('user.about.name', 'noname');
     *
     * @param string $name    Имя cookie.
     * @param mixed  $default Значение, которое вернется, если искомое - не найдено.
     *
     * @return mixed Значение по ключу или default.
     */
    public function get($name, $default = null)
    {
        return ($this->offsetExists($name) === true)? $this->offsetGet($name) : $default;
    }

    /**
     * Устанавливает значение или массив cookie по ключу.
     *
     *      $cookie->set('global.post', $_POST);
     *
     * @param string       $name   Имя cookies.
     * @param string|array $value  Значение cookies.
     * @param array        $params Параметры cookies.
     *
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

        $name      = explode('.', $name);
        $temp_name = array_shift($name);
        $name      = $temp_name.((empty($name))?'':'['.implode('][', $name).']');

        if (!is_array($value)) {
            setcookie(
                $name,
                $value,
                $params['expiration'],
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        } else {
            foreach ($this->asAnnotation($value, '', '][') as $n => $val) {
                setcookie(
                    $name.'['.$n.']',
                    $val,
                    $params['expiration'],
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
        }
    }

    /**
     * Удаляет cookie по ключу с заданными параметрами.
     *
     * @param string $name   Имя куки.
     * @param array  $params Параметры с которыми обьявляли cookie.
     *
     * @return void
     */
    public function delete($name, $params = array())
    {
        if ((($delete = $this->offsetGet($name)) === false)) {
            return;
        }

        if(is_array($delete)){
            array_walk_recursive(
                $delete,
                function (&$item) {
                    $item = '';
                }
            );
        } else {
            $delete = '';
        }
        
        $params = array_merge($params, array('expiration' => 1));
        $this->set($name, $delete, $params);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->delete($offset);
    }
}