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
 *  @author   Ben Martinez-Bateman <ben@murmurcreative.com>
 *  @link     https://bitbucket.org/murmurcreative/mnemosyne
 *  @since    0.1.0
 */

namespace Murmur\WP\Mnemosyne;

use Exception;
use Murmur\WP\Mnemosyne\StorageFactory;

/**
 * Contains the core Mnemosyne functionality.
 *
 *  @since    0.1.0
 */
class Mnemosyne
{
    use Murmur\WP\Mnemosyne\Shared;

    /**
     * Stores the Storage instance.
     *
     * @since      0.2.0
     */
    private $storage = false;

    /**
     * Construct a Mnemosyne.
     *
     * @since      0.2.0 Get storage from StorageFactory
     * @since      0.1.0
     * @return     void
     *
     * @param      array  $settings  Optional settings
     */
    public function __construct($settings = [])
    {

        try {
            $this->applySettings($settings);
        } catch (Exception $settingsError) {
            $this->handleException($settingsError);
        }

        $this->storage = StorageFactory::getFactory()->getStorage();
    }

    /**
     * Get the appropriate default or override for a key.
     *
     * @since      0.2.0 Use StorageFactory
     * @since      0.1.0 Method added
     *
     * @param      string                $key
     * @param      string|array|integer  $override
     * @param      string                $validate
     *
     * @throws     Exception             (checkes for keys and values)
     *
     * @return     string|array|integer
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
                return $this->storage->getDefault($key);
            } catch (Exception $defaultError) {
                $this->handleException($defaultError);
            }
        endif;
    }
}
