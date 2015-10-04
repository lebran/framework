<?php
namespace Lebran\Mvc;

use Lebran\Utils\Storage;
use Lebran\Mvc\View\Exception;
use Lebran\Mvc\View\Extension\ExtensionInterface;

/**
 * Lebran\Mvc\View is a native PHP template system that's fast and easy to extend.
 * It's inspired by compiled template engines (Twig, Blade, ...).
 *
 *                              Examples
 *  <code>
 *      $view = new Lebran\Mvc\View();
 *      $view->addExtension(new Lebran\Mvc\View\Blocks())
 *              ->addFolder('main', '/templates/main')
 *              ->addFolder('new', '/templates/new')
 *              ->enableShortTags()
 *              ->render('new::news');
 *
 *      // templates/new/news.php
 *
 *      <?php $layout('main::index')?>
 *
 *      <?php $block('data')?>
 *          Some text.
 *      <?php $endblock()?>
 *
 *      // templates/main/index.php
 *
 *      <?php $output('data', 'Some default text')?>
 *
 *  </code>
 *
 * @package    Mvc
 * @subpackage View
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
    protected $extensions = [];

    /**
     * @var array Storage for extension methods.
     */
    protected $methods = [];

    /**
     * @var string The name of last rendered template.
     */
    protected $template;

    /**
     * @var string Paths to the view folders.
     */
    protected $folders = [];

    /**
     * @var array Stack for parent files.
     */
    protected $layouts = [];

    /**
     * @var string Child content.
     */
    protected $content = '';

    /**
     * Initialisation. Prepare extensions.
     *
     * @param array $extensions An array of extensions.
     * @param array $data       An array of data.
     */
    public function __construct(array $extensions = [], array $data = [])
    {
        parent::__construct($data);
        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }
    }

    /**
     * Adds extensions.
     *
     * @param ExtensionInterface $extension Extension object.
     *
     * @return object View object.
     */
    public function addExtension(ExtensionInterface $extension)
    {
        $this->extensions[$extension->getName()] = $extension;
        foreach ($extension->getMethods() as $key => $value) {
            $this->methods[$key] = [$extension, $value];
        }
        return $this;
    }

    /**
     * Adds folder for views.
     *
     * @param string $name   The name of folder.
     * @param string $folder Path to folder.
     *
     * @return object View object.
     */
    public function addFolder($name, $folder)
    {
        $this->folders[$name] = rtrim(trim($folder), '/').'/';
        return $this;
    }

    /**
     * Generates path for view.
     *
     * @return string Resolved path.
     * @throws \Lebran\Mvc\View\Exception
     */
    protected function path()
    {
        $parts = explode('::', $this->template);
        if (count($parts) === 1) {
            foreach ($this->folders as $folder) {
                if (is_file($folder.$parts[0].'.php')) {
                    return $folder.$parts[0].'.php';
                }
            }
        } elseif (count($parts) === 2 && is_file($this->folders[$parts[0]].$parts[1].'.php')) {
            return $this->folders[$parts[0]].$parts[1].'.php';
        }

        throw new Exception(
            'The template name "'.$this->template.'" is not valid. '.
            'Do not use the folder namespace separator "::" more than once.'
        );
    }

    /**
     * Render the template and layout.
     *
     * @param string $template The name of template.
     * @param array  $data     An array of data.
     *
     * @return string Rendered template.
     * @throws \Lebran\Mvc\View\Exception
     */
    public function render($template, array $data = [])
    {
        $this->template = $template;
        $this->storage  = array_merge_recursive($this->storage, $data);

        extract($this->storage);
        ob_start();

        include $this->path();

        if (0 === count($this->layouts)) {
            return ob_get_clean();
        } else {
            $this->content = ob_get_clean();
            return $this->render(array_pop($this->layouts));
        }
    }

    /**
     * Magic method used to call extension functions.
     *
     * @param string $method     The name of method.
     * @param array  $parameters The params of method.
     *
     * @return mixed Method response.
     * @throws \Lebran\Mvc\View\Exception
     */
    public function __call($method, $parameters)
    {
        if (array_key_exists($method, $this->methods)) {
            return call_user_func_array($this->methods[$method], $parameters);
        } else {
            throw new Exception('The extension method "'.$method.'" not found.');
        }
    }

    /**
     * Magic method used to get extension.
     *
     * @param string $name The name of extension.
     *
     * @return object Extension object.
     * @throws \Lebran\Mvc\View\Exception
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->extensions)) {
            return $this->extensions[$name];
        } else {
            throw new Exception('The extension "'.$name.'" not found.');
        }
    }


    /**
     * Set the template's layout.
     *
     * @param string $layout The name of layout
     *
     * @return void
     */
    protected function layout($layout)
    {
        $this->layouts[] = $layout;
    }

    /**
     * Prints child content.
     *
     * @return void
     */
    protected function content()
    {
        echo $this->content;
    }

    /**
     * Include view.
     *
     * @param string $view The name of view.
     *
     * @return void
     * @throws \Lebran\Mvc\View\Exception
     */
    protected function import($view)
    {
        $this->template = $view;
        extract($this->storage);
        include $this->path();
    }
}