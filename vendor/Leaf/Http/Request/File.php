<?php
namespace Leaf\Http\Request;

/**
 * Wrapper for files sent via the form.
 *
 * @package    Http
 * @subpackage Request
 * @version    2.1
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class File
{
    /**
     * The name of file.
     *
     * @var string
     */
    protected $name;

    /**
     * The temporary name of file.
     *
     * @var string
     */
    protected $tmp;

    /**
     * The size of file.
     *
     * @var int
     */
    protected $size;

    /**
     * The mime type of file.
     *
     * @var string
     */
    protected $type;

    /**
     * Initialisation.
     *
     * @param array $file File parameters.
     */
    public function __construct(array $file)
    {
        $this->name = $file['name'];
        $this->tmp  = $file['tmp_name'];
        $this->size = $file['size'];
        $this->type = $file['type'];
    }

    /**
     * Returns the file size of the uploaded file.
     *
     * @return string Size of file.
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Returns the real name of the uploaded file.
     *
     * @return string Name of file.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the temporal name of the uploaded file
     *
     * @return string Temp name of file.
     */
    public function getTempName()
    {
        return $this->tmp;
    }

    /**
     * Returns the mime type reported by the browser.
     *
     * @return string Type of file.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Checks whether the file has been uploaded via Post.
     *
     * @return bool True if uploaded, then - false.
     */
    public function isUploadedFile()
    {
        return is_string($this->getTempName()) && is_uploaded_file($this->getTempName());
    }

    /**
     * Moves the temporary file to a destination within the application.
     *
     * @param string Directory for file.
     *
     * @return bool Status.
     */
    public function moveTo($directory)
    {
        return move_uploaded_file($this->tmp, $directory);
    }
}