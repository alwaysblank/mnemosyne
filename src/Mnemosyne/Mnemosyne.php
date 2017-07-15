<?php

/**
 * Core Mnemosyne class definition.
 *
 * Mnemosyne is a system that allows for hard-coded defaults to be
 * overwritten by storage-agnostic overrides. This allows for content
 * to be described in depth in a theme’s code but easily overridden
 * by a user if necessary.
 *
 * PHP version 5.6
 *
 *  @package  AlwaysBlank\WP\Mnemosyne
 *  @author   Ben Martinez-Bateman <ben@alwaysblank.org>
 *  @link     https://github.com/alwaysblank/mnemosyne
 *  @since    0.1.3
 */

namespace AlwaysBlank\WP\Mnemosyne;

use Exception;
use Symfony\Component\Yaml\Parser;
use Hipparchus\Pocketknife;

/**
 * Contains the core Mnemosyne functionality.
 *
 *  @since    0.1.0
 */
class Mnemosyne
{

    /**
     * Absolute path to a .php file containing our defaults.
     *  @since    0.1.0
     */
    private $storage_location = false;

    /**
     * Property that contains defaults once loaded.
     *
     *  @since    0.1.0
     */
    private $defaults = false;

    /**
     * Name of the $GLOBALS key that will contain
     * loaded defaults.
     */
    private $cache_key = 'AlwaysBlank_WP_Mnemosyne_default_cache';

    /**
     * Set to bool true to throw Exceptions instead of
     * handling them internally.
     */
    private $emit_exceptions = false;


    /**
     * Construct a Mnemosyne.
     *
     *  @since    0.1.0
     *  @return     void
     */
    public function __construct($settings = [])
    {
        try {
            $this->storage_location = $this->findStorage();
        } catch (Exception $storageError) {
            $this->handleException($storageError);
        }

        $this->defaults = $this->retrieveDefaults();

        try {
            $this->applySettings($settings);
        } catch (Exception $settingsError) {
            $this->handleException($settingsError);
        }
    }

    /**
     * Apply any settings that we've passed to the class on
     * instantiation.
     *
     *  @since      0.1.1
     *  @param      array   $settings
     *  @return     void
     */
    private function applySettings($settings)
    {
        foreach ($settings as $name => $setting) :
            if (property_exists($this, $name)) :
                $this->{$name} = $setting;
            else :
                throw new Exception(
                    sprintf("Cannot set %s, it does not exist.", $name)
                );
            endif;
        endforeach;
    }

    /**
     * Attempt to locate defaults file.
     *
     *  @since      0.1.0
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
     * Handle exceptions that get thrown by things.
     *
     * Just a wrapper, in case we want to modify exception
     * handling across the class (i.e. suppress it).
     *
     *  @since      0.1.0
     *  @param      object[Exception]   $Exception
     *  @return     string
     */
    private function handleException($Exception)
    {
        if ($this->emit_exceptions) :
            throw $Exception;
        else :
            echo $Exception->getMessage() . '<br>';
        endif;
    }


    /**
     * Returns an array of defaults as keys and values.
     *
     * Using this method allows us to check to see if the
     * defaults have already been loaded to avoid multiple
     * calls to the filesystem.
     *
     *  @since      0.1.0
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
 *  @since      0.1.0
 *  @param      string
 *  @return     array
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
        endif;

        $yaml = new Parser();
        return $yaml->parse(file_get_contents($location));
    }


    /**
     * Gets default value by key.
     *
     * Throws an exception if the key does not exist.
     *
     *  @since      0.1.0
     *  @param      string
     *  @return     string|bool
     */
    private function getDefault($key)
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

    /**
     * Check keys to make sure they're valid as PHP array keys.
     *
     *  @since      0.1.0
     *  @param      string $key
     *  @return     boolean|string
     */
    private function checkKey($key)
    {
        return Pocketknife::safeString($key);
    }

    /**
     * Check values to make sure they're valid for our purposes.
     *
     * Passing a boolean value to this function will always return
     * bool true, even if the value is bool false, because boolean
     * is a value valid.
     *
     *  @since      0.1.1
     *  @param      mixed   $value
     *  @return     bool
     */
    private function checkValue($value)
    {
        if (is_string($value) || is_int($value) || is_array($value) || is_bool($value)) :
            return true;
        else :
            return false;
        endif;
    }


    /**
     * Get the appropriate default or override for a key.
     *
     *  @since      0.1.0
     *  @param      string               $key
     *  @param      string|array|integer $override
     *  @param      string               $validate
     *  @return     string|array|integer
     */
    public function remember($key, $override, $validate = false)
    {
        try {
            if (!is_string($key)) :
                throw new Exception(
                    sprintf(
                        "The supplied key is of type <code>%s</code>. Must be a string.",
                        gettype($key)
                    )
                );
            endif;

            if (!$this->checkKey($key)) :
                throw new Exception(
                    sprintf(
                        "The key <code>%s</code> is not a valid key (only alphanumeric and underscores allowed).",
                        $key
                    )
                );
            endif;
        } catch (Exception $keyError) {
            $this->handleException($keyError);
            return;
        }

        try {
            if (!$this->checkValue($override)) :
                throw new Exception(
                    sprintf(
                        "The override for key <code>%s</code> is of invalid type <code>%s</code>. 
                        Must be string, int, or array.",
                        $key,
                        gettype($override)
                    )
                );
            endif;
        } catch (Exception $overrideError) {
            $this->handleException($overrideError);
            return;
        }

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
