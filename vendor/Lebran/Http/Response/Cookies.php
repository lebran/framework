<?php
namespace Lebran\Http\Response;

use Lebran\Di\InjectableInterface;

/**
 * The helper class for a cookies. Supports setting of the array.
 * It is used notation dot to access multidimensional arrays.
 *
 * @package    Http
 * @subpackage Response
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Cookies implements InjectableInterface
{
    /**
     * Store for di container.
     *
     * @var
     */
    protected $di;

    /**
     * Store cookies.
     *
     * @var array
     */
    protected $bag = [];

    /**
     * Registered in response or not.
     *
     * @var bool
     */
    protected $registered = false;

    /**
     * Parameters for cookies.
     *
     * @var array
     */
    protected $params = [
        // The time when the term of the cookie expires
        'expiration' => 0,
        // The path to the directory on the server from which the cookie will be available
        'path'       => '',
        // Domain, which is available cookie
        'domain'     => null,
        // It indicates that the cookie should be transferred from the customer via a secure HTTPS connection
        'secure'     => false,
        // If set to true, cookie will only be available via Http protocol
        'httponly'   => false
    ];

    /**
     * Initialisation.
     *
     *      $cookies = new Cookies(['secure' => true]));
     *
     * @param array $params Cookies parameters.
     */
    public function __construct($params = [])
    {
        array_walk_recursive($_COOKIE, 'trim');
        $this->params = $params + $this->params;
    }

    /**
     * Sets the dependency injection container.
     *
     * @param object $di Container object.
     *
     * @return void
     */
    public function setDi($di)
    {
        $this->di = $di;
    }

    /**
     * Returns the dependency injection container.
     *
     * @return object Container object.
     */
    public function getDi()
    {
        return $this->di;
    }

    /**
     * Gets the value of cookies by key or default, if not found.
     * Use spot annotation.
     *
     *      $cookies->get('user.about.name', 'no-name');
     *
     * @param string $name    Cookie(s) name.
     * @param mixed  $default The value to return if required not found.
     *
     * @return mixed Value by key or default.
     */
    public function get($name, $default = null)
    {
        $segments = explode('.', $name);
        $group    = $_COOKIE;

        foreach ($segments as $segment => $value) {
            if (isset($group[$value]) && is_array($group)) {
                if ($segment == count($segments) - 1) {
                    return $group[$value];
                } else {
                    $group = $group[$value];
                }
            } else {
                return $default;
            }
        }
    }

    /**
     * Set the cookie(s) value or an array of key.
     *
     *      $cookie->set('global.post', $_POST, ['expiration' => 32000]);
     *
     * @param string $name   Cookie(s) name.
     * @param mixed  $value  Cookie(s) value.
     * @param array  $params Cookie(s) parameters.
     *
     * @return object Cookies object.
     * @throws \Lebran\Http\Response\Exception
     */
    public function set($name, $value, $params = [])
    {
        foreach ($this->params as $key => $val) {
            if (empty($params[$key])) {
                $params[$key] = $val;
            }
        }
        if ($params['expiration'] != 0) {
            $params['expiration'] += time();
        }

        $temp_name = explode('.', trim($name));
        $name      = array_shift($temp_name);
        if (!empty($temp_name)) {
            $name = $name.'['.implode('][', $temp_name).']';
        }

        if (is_array($value)) {
            $value = $this->setHelper($name, $value);
            array_walk(
                $value,
                function (&$item) use ($params) {
                    $item = ['value' => $item] + $params;
                }
            );
        } else {
            $value = [$name => ['value' => $value] + $params];
        }

        $this->bag = array_merge($this->bag, $value);

        if (!$this->registered) {
            if (!is_object($this->di) && !$this->di->has('response')) {
                throw new Exception('A dependency injection object is required to access the "response" service');
            }
            $this->di->get('response')->setCookies($this);
            $this->registered = true;
        }

        return $this;
    }

    /**
     * Removes a cookie(s) on a key with the specified parameters.
     *
     * @param string $name   Cookie(s) name.
     * @param array  $params Parameters which announces cookie(s).
     *
     * @return object Cookies object.
     */
    public function delete($name, $params = [])
    {
        if ((($delete = $this->get($name)) !== null)) {
            if (is_array($delete)) {
                array_walk_recursive(
                    $delete,
                    function (&$item) {
                        $item = '';
                    }
                );
            } else {
                $delete = '';
            }

            $params = array_merge($params, ['expiration' => 1]);
            $this->set($name, $delete, $params);
        }

        return $this;
    }

    /**
     * Send Cookies from bag.
     *
     * @return object Cookies object.
     */
    public function send()
    {
        foreach ($this->bag as $name => $cookie) {
            setcookie(
                $name,
                $cookie['value'],
                $cookie['expiration'],
                $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httponly']
            );
        }

        return $this;
    }

    /**
     * Recursively converts into a single array.
     *
     *      $this->setHelper('test', $test);
     *
     *      Incoming array:
     *          [
     *              "level11" => "value1",
     *              "level12" => [
     *                  "level21" => "value2"
     *              ]
     *          ]
     *
     *      Outgoing array:
     *          [
     *              "test[level11]" => "value1",
     *              "test[level12][level21]" => "value2",
     *          ]
     *
     * @param array  $array Convertible array.
     * @param string $name  The name that will be added at the beginning.
     *
     * @return array Processed array.
     */
    final protected function setHelper($name, array $array)
    {
        $temp = [];
        foreach ($array as $key => $value) {
            $new_name = strval(($name !== '')?$name.'['.$key.']':$key);
            is_array($value)?$temp += $this->setHelper($new_name, $value):$temp[$new_name] = $value;
        }
        return $temp;
    }
}