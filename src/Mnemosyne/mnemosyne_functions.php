<?php

use \Exception;
use \Murmur\WP\Mnemosyne\Mnemosyne;

/**
 * Return the value for the key.
 *
 * @see     Murmur\WP\Mnemosyne::remember()
 */
function __m($key, $override, $validation = false)
{
    $Mnemosyne = new Mnemosyne;
    try {
        $value = $Mnemosyne->remember($key, $override, $validation = false);
    } catch (Exception $mError) {
        $GLOBALS['Murmur_WP_Mnemosyne_errors'][] = $mError->getMessage();
        $value = null;
    }
    return $value;
}

/**
 * Echo the value for the key.
 *
 * @see     Murmur\WP\Mnemosyne\__m()
 */
function __me($key, $override, $validation = false)
{
    try {
        $value = __m($key, $override, $validation = false);
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
