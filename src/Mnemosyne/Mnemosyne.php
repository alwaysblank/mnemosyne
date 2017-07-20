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
use Symfony\Component\Finder\Finder;
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
     * Filename of the file that contains our defaults.
     */
    private $storage_file = 'defaults.mnemosyne.yaml';

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
     *  @since    0.1.4
     *  @return     void
     */
    public function __construct($settings = [])
    {
        try {
            $this->storage_location = $this->findStorage($this->storage_file);
        } catch (Exception $storageError) {
            $this->handleException($storageError);
        }

        $this->defaults = $this->loadDefaults();

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
     *  @since      0.1.4
     *  @return     string
     */
    private function findStorage($filename)
    {
        $file = array();

        // File name for the file we want to find.
        $file['name'] = apply_filters(
            'AlwaysBlank/WP/Mnemosyne/storage_file',
            $filename
        );

        // File path *not including file name* of the file we
        // want to find.
        $file['path'] = apply_filters(
            'AlwaysBlank/WP/Mnemosyne/storage_path',
            false
        );

        if ($file['path'] === false) :
          $finder = new Finder();

          $finder_search_location = get_stylesheet_directory();

          // get_stylesheet_directory() is used here to allow
          // for proper use in child themes. Note that it may
          // cause odd results if your theme adjusts what
          // get_styleshet_directory() returns (i.e. roots/sage).
          $finder->files()->in($finder_search_location)->name($file['name']);

          $filtered_finder = apply_filters( 'AlwaysBlank/WP/Mnemosyne/storage_finder', $finder );

          $finder_results = iterator_to_array($filtered_finder, false);

          // No files found.
          if (count($finder_results) < 1) :
            throw new Exception(
                sprintf(
                    "Could not find <code>%s</code>. It does not appear to exist in <code>%s</code>.",
                    $file['name'],
                    $finder_search_location
                )
            );

            return false;

          elseif (count($finder_results) > 1) :
            $file_location_list = null;

            foreach ($filtered_finder as $each_file) :
              $file_location_list .= "<li>{$each_file->getRelativePathname()}</li>";
            endforeach;
            $file_location_list = "<ol>$file_location_list</ol>";

            // More than one file found.
            throw new Exception(
                sprintf(
                    "Found more than one instance of <code>%s</code>. %s",
                    $file['name'],
                    $file_location_list
                )
            );

            return false;

          // Good file found.
          elseif (count($finder_results) === 1) :
            $location = $finder_results[0]->getRealPath();

          // Should never arrive here.
          else :
            throw new Exception(
                sprintf(
                    "Something went wrong with <code>%s</code>. I'm not sure what.",
                    $file['name']
                )
            );

            return false;
          endif;

        // We passed in a file path, so trust it.
        else :
          $location = trailingslashit($file['path']) . $file['name'];
        endif;

        // Make double sure the file exists.
        $test = file_exists($location);

        if (!$test) :
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


    private function validateStorage($file)
    {
        $parsed = $this->loadFile($file);

        try {
            $parsed = $this->loadFile($file);
        } catch (Exception $validateError) {
            $this->handleException($validateError);
        }

        if (is_array($parsed) && !is_empty($parsed)) :
            return $file;
        else :
          throw new Exception(
                sprintf(
                    "The file <code>%s</code> contained nothing, or its content could not be loaded.",
                    $file
                )
            );
            return false; 
        endif; 
    }

    /**
     * Loads storage file.
     * 
     * @since   0.1.4
     */
    private function loadStorage($file)
    {
        if ($parsed = $this->validateStorage($file)) :
            $combined = $this->defaults + $parsed;
            $this->defaults = $combined;

            if (json_encode($combined) === json_encode($this->defaults)) :
                return true;
            endif;
        endif;

        return false;
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
     *  @since      0.1.4
     *  @return     mixed|bool
     */
    private function loadDefaults()
    {
        if (isset($GLOBALS[$this->cache_key])) :
            return $GLOBALS[$this->cache_key];
        else :
            try {
                $defaults = $this->loadFile($this->storage_location);
            } catch (Exception $fileError) {
                $this->handleException($fileError);
            }

            return $GLOBALS[$this->cache_key] = $defaults;
        endif;

        return false;
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
