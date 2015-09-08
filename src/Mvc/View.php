<?php
namespace Lebran\Mvc;

use \Lebran\Utils\Storage;

/**
 * Lebran\Di it's a component that implements Dependency Injection/Service Location patterns.
 * Supports string, object, array and anonymous function definition. Allows using the array syntax.
 *
 *                              Examples
 *  <code>
 *
 *  </code>
 *
 * @package    Di
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class View extends Storage
{
    /**
     * @var array Storage for extensions.
     */
    protected $extensions = array();

    /**
     * @var array Storage for extension methods.
     */
    protected $methods = array();

    /**
     * @var string The name of template.
     */
    protected $template;

    /**
     * @var string Path to the view directory.
     */
    protected $directory;

    /**
     * @var array Stack for parent files.
     */
    public $parent = array();

    /**
     * @var array Stack for parent files.
     */
    public $content = '';

    /**
     * Initialisation. Prepare extensions.
     *
     * @param array $extensions An array of extensions.
     */
    public function __construct(array $extensions = array())
    {
        foreach ($extensions as $extension) {
            $this->extensions[$extension->getName()] = $extension;
            foreach ($extension->getMethods() as $key => $value) {
                $this->methods[$key] = [$extension, $value];
            }
        }
    }

    /**
     *
     * @param $directory
     *
     * @return $this
     */
    public function registerDirectories($directory)
    {
        $this->directory = rtrim(trim($directory), '/').'/';
        return $this;
    }

    /**
     *
     *
     * @param string $template
     * @param array  $params
     *
     * @return string
     */
    public function render($template, array $params = [])
    {
        $this->template = $template;
        $this->storage  = array_merge_recursive($this->storage, $params);

        extract($this->storage);
        ob_start();

        include $this->directory.$this->template.'.php';

        if(0 === count($this->parent)){
            return ob_get_clean();
        } else {
            $this->content = ob_get_clean();
            return $this->render(array_pop($this->parent));
        }
    }

    /**
     *
     *
     * @param string $method
     * @param array $parameters
     */
    public function __call($method, $parameters)
    {
        if (array_key_exists($method, $this->methods)) {
            call_user_func_array($this->methods[$method], $parameters);
        }
    }

    /**
     *
     * @return object View object.
     */
    public function enableShortTags()
    {

        foreach ($this->methods as $name => $value) {
            $this->set(
                $name,
                function (...$parameters) use ($value) {
                    return call_user_func_array($value, $parameters);
                }
            );
        }

        $methods = ['extend', 'import', 'content'];
        $_this   = $this;

        foreach ($methods as $method) {
            $this->set(
                $method,
                function (...$parameters) use ($_this, $method) {
                    return call_user_func_array([$_this, $method], $parameters);
                }
            );
        }

        return $this;
    }

    /**
     *
     *
     * @param string $parent
     *
     * @return void
     */
    protected function extend($parent)
    {
        $this->parent[] = $parent;
    }

    protected function content()
    {
        echo $this->content;
    }

    /**
     * @param string $filename
     *
     * @return void
     */
    protected function import($filename)
    {
        extract($this->storage, EXTR_SKIP);
        include $this->directory.$filename.'.php';
    }
}