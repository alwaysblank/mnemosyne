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
 *  @since    0.1.0
 */

use \Exception;
use \Murmur\WP\Mnemosyne\Mnemosyne;

/**
 * Return the value for the key.
 *
 *  @since    0.1.1
 *  @see     Murmur\WP\Mnemosyne::remember()
 *  @return   mixed
 */
function mns_get_value($key, $override, $validation = false)
{
    $Mnemosyne = new Mnemosyne;
    try {
        $value = $Mnemosyne->remember($key, $override, $validation);
    } catch (Exception $mError) {
        $GLOBALS['Murmur_WP_Mnemosyne_errors'][] = $mError->getMessage();
        $value = null;
    }
    return $value;
}

/**
 * i18n-y alias for mns_get_value().
 * 
 *  @since    0.1.0
 *  @see      Murmur\WP\Mnemosyne\mns_get_value()
 *  @return   mixed
 */
function __m($key, $override, $validation = false)
{
    return mns_get_value($key, $override, $validation);
}

/**
 * i18n-y alias for echoing mns_get_value().
 *
 *  @since    0.1.0
 *  @see      Murmur\WP\Mnemosyne\mns_get_value()
 *  @return   void
 */
function __me($key, $override, $validation = false)
{
    try {
        $value = mns_get_value($key, $override, $validation = false);
        if (!(is_string($value) || is_numeric($value))) :
            throw new Exception(
                sprintf(
                    "The value for key <code>%s</code> cannot be converted to a string.\n",
                    $key
                )
            );
        else :
            echo $value;
        endif;
    } catch (Exception $echoError) {
        $GLOBALS['Murmur_WP_Mnemosyne_errors'][] = $echoError->getMessage();
    }
}

/**
 * Get the default value without checking for an override.
 * 
 * In some rare cases, you may want to get the default value without,
 * checking to see if an override is in place (for instance, to check
 * whether or not an override exists by comparison). This function
 * bypasses the override check.
 * 
 *  @since    0.1.1
 *  @param      string  $key
 *  @return     mixed
 */
function mns_get_default($key)
{
    return mns_get_value($key, false);
}

/**
 * Check to see if a key is being overriden.
 * 
 *  @since    0.1.1
 *  @see      Murmur\WP\Mnemosyne\mns_get_value()
 *  @return   bool
 */
function mns_is_overriden($key, $override, $validation = false)
{
    $value = mns_get_value($key, $override, $validation);
    $default = mns_get_default($key);

    if (($value != $default) && ($value != null)) :
        return true;
    else :
        return false;
    endif;
}