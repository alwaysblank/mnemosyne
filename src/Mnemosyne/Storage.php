<?php 
/**
 * An interface for gathering data from the defaults file.
 *
 * @package    Murmur\WP\Mnemosyne
 * @author     Ben Martinez-Bateman <ben@murmurcreative.com>
 * @link       https://bitbucket.org/murmurcreative/mnemosyne
 * @since      0.2.0
 */

namespace Murmur\WP\Mnemosyne;


use Exception;
use Symfony\Component\Yaml\Parser;

class Storage
{
    use Murmur\WP\Mnemosyne\Shared;

	/**
      * Absolute path to a .yaml file containing our defaults.
      * 
      * @since      0.2.0
      *
      * @var        boolean
      */
    private $storage_location = false;

    /**
     * Property that contains defaults once loaded.
     *
     * @since      0.2.0
     *
     * @var        boolean
     */
    private $defaults = false;


    public function __construct()
    {
        try {
            $this->storage_location = $this->findFile();
        } catch (Exception $storageError) {
            $this->handleException($storageError);
        }

        $this->defaults = $this->retrieveDefaults();
    }

    /**
     * Attempt to locate defaults file.
     *
     * @since      0.2.0
     *
     * @throws     Exception  (File could not be found)
     *
     * @return     string
     */
    private function findFile()
    {
        $path = apply_filters(
            'Murmur/WP/Mnemosyne/storage_location',
            'defaults.mnemosyne.yaml'
        );
        $location = locate_template($path);
        if ($location === '') :
            throw new Exception(
                sprintf(
                    "Could not find a file to load at <code>%s</code>.",
                    $path
                )
            );
            return false;
        else :
            return $location;
        endif;
    }


    /**
     * Returns parsed data from the defaults file.
     *
     * Throws an Exception if the file does not exist.
     *
     * @since      0.1.0
     *
     * @param      string      $location  Filesystem path to defaults
     *
     * @throws     Exception   (File cannot be loaded/reached)
     *
     * @return     array|bool
     */
    private function loadFile($location)
    {
        if (!file_exists($location)) :
            throw new Exception(
                sprintf(
                    "The file <code>%s</code> does not exist, or is inaccessible.",
                    $location
                )
            );
            return false;
        endif;

        $yaml = new Parser();
        return $yaml->parse(file_get_contents($location));
    }


    /**
     * Returns an array of defaults as keys and values.
     *
     * Using this method allows us to check to see if the defaults have already
     * been loaded to avoid multiple calls to the filesystem.
     *
     * @since      0.2.0
     *
     * @return     mixed|bool
     */
    private function retrieveDefaults()
    {
        // If there's no storage location, don't even try
        if (!$this->storage_location) :
            return false;
        endif;

        try {
            $defaults = $this->loadFile($this->storage_location);
        } catch (Exception $fileError) {
            $this->handleException($fileError);
        }
    }

    /**
     * Gets default value by key.
     *
     * Throws an exception if the key does not exist.
     *
     * @since      0.2.0
     *
     * @param      string       $key    Key for the default
     *
     * @throws     Exception    (Key is not a valid type)
     *
     * @return     string|bool
     */
    public function getDefault($key)
    {
        if (!isset($this->defaults[$key])) :
            throw new Exception(
                sprintf(
                    "The key <code>%s</code> does not exist.",
                    $key
                )
            );
        elseif (isset($this->defaults[$key])) :
                $value = $this->defaults[$key];
            if ($this->checkValue($value)) :
                return $value;
            else :
                    throw new Exception(
                        sprintf(
                            "The default for key <code>%s</code> is of invalid type <code>%s</code>. 
                            Must be string, int, or array.",
                            $key,
                            gettype($value)
                        )
                    );
                    return false;
            endif;
        else :
                return false;
        endif;
    }
}