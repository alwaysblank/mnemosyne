<?php 

/**
 * Shared tools used across Mnemosyne.
 *
 * These are collected here so we can unify how we do things like check values
 * or keys for validity no matter where we are.
 *
 * @package    Murmur\WP\Mnemosyne
 * @author     Ben Martinez-Bateman <ben@murmurcreative.com>
 * @link       https://bitbucket.org/murmurcreative/mnemosyne
 * @since      0.2.0
 */

namespace Murmur\WP\Mnemosyne;

use Exception;
use Hipparchus\Pocketknife;

trait Shared
{

    /**
     * Set to bool true to throw Exceptions instead of
     * handling them internally.
     * 
     * @since 	0.2.0
     */
    private $emit_exceptions = false;	

    /**
     * Handle exceptions that get thrown by things.
     *
     * Just a wrapper, in case we want to modify exception handling across the
     * class (i.e. suppress it).
     *
     * @since      0.2.0
     *
     * @param      object[Exception]  $Exception
     * @return     string|void
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
     * Check keys to make sure they're valid as PHP array keys.
     *
     * @since      0.2.0
     *
     * @param      string          $key
     *
     * @return     boolean|string
     */
    private function checkKey($key)
    {
        return Pocketknife::safeString($key);
    }

    /**
     * Check values to make sure they're valid for our purposes.
     *
     * Passing a boolean value to this function will always return bool true,
     * even if the value is bool false, because boolean is a value valid.
     *
     * @since      0.2.0
     *
     * @param      mixed  $value
     *
     * @return     bool
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
     * Apply any settings that we've passed to the class on instantiation.
     *
     * @since      0.1.1
     *
     * @param      array      $settings
     * @return     void
     *
     * @throws     Exception  (Setting doesn't exist)
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
}