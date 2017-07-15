# Mnemosyne
#### Get the thing, unless there's a new thing

Mnemosyne is a system that allows for hard-coded defaults to be overwritten by storage-agnostic overrides. This allows for content to be described in depth in a theme’s code but easily overridden by a user if necessary.
 
Named for the [Greek Titan of memory](https://simple.wikipedia.org/wiki/Mnemosyne).

## Usage

It is recommended that you use Mnemosyne's convenience functions instead of accessing `Mnemosyne` directly.

These functions fail silently but throw exceptions when they do. Currently these exceptions are not displayed, but are stored in the PHP $GLOBALS variable: `$GLOBALS['AlwaysBlank_WP_Mnemosyne_errors']`

### Defaults

Set your defaults by creating a file called `defaults.mnemosyne.yaml` in `[theme]/resources/` (assuming you are using Sage 9.x). Currently Mnemosyne supports basic `key:value` pairs, including values that are more complex data types, such as arrays. Keys support only alphanumeric characters and underscores.

If you want to place your defaults.yaml file somewhere else, or name it something else, you can use the filter `AlwaysBlank/WP/Mnemosyne/storage_location` to pass in a new location. Keep in mind that this value is passed to WordPress's `locate_template()` to get its location.

### __m()

`__m($key_name, $data_source [, $validation_function])`

This function returns a value from the defaults or source, as appropriate.

`$key_name`

The key for the value we want. They key will be used to look up a default.
 
`$data_source`

The data source that should be checked for an override. If this evaluates to a falsy value (i.e. false, null, ‘’) then the default will be used. 
 
`$validation_function`

An optional callback. This is the name of a function that, if defined, will be called and passed the evaluated result of $data_source as well as the $key_name. It should return either a falsy value (ideally bool false), or the result of $data_source.

### __me()

`__me($key_name, $data_source [, $validation_function])`

This function behaves identially to `__m()`, except that it echos the value.

**Note:** Attempting to retrieve a value that is not echoable (i.e. not a string or numeric value) will throw an exception.

### Others

See `src/Mnemosyne/mnemosyne_functions.php` for other functions and their documentation. Includes some tools for looking at errors and checking for overrides.