<?php

/**
 * Core Mnemosyne class definition.
 *
 * Mnemosyne is a system that allows for hard-coded defaults to be
 * overwritten by storage-agnostic overrides. This allows for content
 * to be described in depth in a theme’s code but easily overridden
 * by a user if necessary.
 *
 * PHP version 7
 *
 *  @package  Murmur\WP\Mnemosyne
 *  @author   Squiz Pty Ltd <products@squiz.net>
 *  @link     https://bitbucket.org/murmurcreative/mnemosyne
 *  @since    0.0.1
 */

namespace Murmur\WP\Mnemosyne;

use \Exception;
use \Symfony\Component\Yaml\Parser;
use \Hipparchus\Pocketknife;

/**
 * Contains the core Mnemosyne functionality.
 *
 *  @author   Ben Martinez-Bateman <ben@murmurcreative.com>
 *  @since    0.0.1
 */
class Mnemosyne
{

    /**
     * Absolute path to a .php file containing our defaults.
     */
    private $storage_location = false;

    /**
     * Property that contains defaults once loaded.
     */
    private $defaults = false;

    /**
     * Name of the $GLOBALS key that will contain
     * loaded defaults.
     */
    private $cache_key = 'Murmur_WP_Mnemosyne_default_cache';


    /**
     * Construct a Mnemosyne.
     *
     *  @return     void
     */
    public function __construct()
    {
        try {
            $this->storage_location = $this->findStorage();
        } catch (Exception $storageError) {
            $this->handleException($storageError);
        }

        $this->defaults = $this->retrieveDefaults();
    }

    /**
     * Attempt to locate defaults file.
     *
     *  @return     string
     */
    private function findStorage()
    {
        $path = apply_filters(
            'Murmur/WP/Mnemosyne/storage_location',
            'defaults.mnemosyne.yaml'
        );
        $location = locate_template($path);
        if ($location === '') :
            throw new Exception(
                sprintf(
                    "Could not find a file to load at <code>%s</code>.\n",
                    $path
                )
            );
            return false;
        else :
            return $location;
        endif;
    }


    /**
     * Handle exceptions that get thrown by things.
     *
     * Just a wrapper, in case we want to modify exception
     * handling across the class (i.e. suppress it).
     *
     *  @param      object[Exception]   $Exception
     *  @return     string
     */
    private function handleException($Exception)
    {
        echo $Exception->getMessage();
    }


    /**
     * Returns an array of defaults as keys and values.
     *
     * Using this method allows us to check to see if the
     * defaults have already been loaded to avoid multiple
     * calls to the filesystem.
     *
     *  @return     mixed|bool
     */
    private function retrieveDefaults()
    {
        if (isset($GLOBALS[$this->cache_key])) :
            return $GLOBALS[$this->cache_key];
        elseif ($this->storage_location) :
            try {
                $defaults = $this->loadFile($this->storage_location);
            } catch (Exception $fileError) {
                $this->handleException($fileError);
            }

            return $GLOBALS[$this->cache_key] = $defaults;
        else :
            return false;
        endif;
    }


/**
 * Returns parsed data from the defaults file.
 *
 * Throws an Exception if the file does not exist.
 *
 *  @param  string
 *  @return array
 */
    private function loadFile($location)
    {
        if (!file_exists($location)) :
            throw new Exception(
                sprintf(
                    "The file <code>%s</code> does not exist, or is inaccessible.\n",
                    $location
                )
            );
        endif;

        $yaml = new Parser();
        return $yaml->parse(file_get_contents($location));
    }


    /**
     * Gets default value by key.
     *
     * Throws an exception if the key does not exist.
     *
     *  @param   string
     *  @return string|bool
     */
    private function getDefault($key)
    {
        if (!isset($this->defaults[$key])) :
            throw new Exception(
                sprintf(
                    "The key <code>%s</code> does not exist.\n",
                    $key
                )
            );
        elseif (isset($this->defaults[$key])) :
                return $this->defaults[$key];
        else :
                return false;
        endif;
    }

    /**
     * Check keys to make sure they're valid as PHP array keys.
     *
     *  @param  string $key
     *  @return boolean|string
     */
    private function checkKey($key)
    {
        return Pocketknife::safeString($key);
    }


    /**
     * Get the appropriate default or override for a key.
     *
     *  @param  string               $key
     *  @param  string|array|integer $override
     *  @param  string               $validate
     *  @return string|array|integer
     */
    public function remember($key, $override, $validate = false)
    {
        if (!is_bool($override) && !(is_string($override) || is_int($override) || is_array($override))) :
            throw new Exception(
                "The supplied override is not an acceptable type (string, int, or array).\n"
            );
        endif;

        if (!is_string($key)) :
            throw new Exception(
                "The supplied key is not a string.\n"
            );
        endif;

        if (!$this->checkKey($key)) :
            throw new Exception(
                sprintf(
                    "The key %s is not a valid key (only alphanumeric and underscores allowed).\n",
                    $key
                )
            );
        endif;

        if (is_string($validate)) :
            $test = call_user_func($validate, $override, $key);
        else :
            $test = $override;
        endif;

        if ($test) :
            return $override;
        else :
            try {
                return $this->getDefault($key);
            } catch (Exception $defaultError) {
                $this->handleException($defaultError);
            }
        endif;
    }
}
