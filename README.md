# Mnemosyne
#### Get the thing, unless there's a new thing

Mnemosyne is a system that allows for hard-coded defaults to be overwritten by storage-agnostic overrides. This allows for content to be described in depth in a theme’s code but easily overridden by a user if necessary.
 
Named for the [Greek Titan of memory](https://simple.wikipedia.org/wiki/Mnemosyne).

## Defaults

The data stored by Mnemosyne are refered to as "defaults".

### Importing

Set your defaults by creating a file called `defaults.mnemosyne.yaml`. Mnemosyne will look for this file in the root of your "stylesheet directory" (i.e. the directory returned by the function `get_stylesheet_directory()`), but if it can't find it there it will search your entire theme.

If you would like to place it somewhere else, or avoid the processing cycles needed for searching your theme, you can tell Mnemosyne where to find your file with these two filters:

  - `AlwaysBlank/WP/Mnemosyne/storage_file` This is the name of the file you want to find. It defaults to `defaults.mnemosyne.yaml`. You can name it whatever you like, but adding the extension `.mnemosyne.yaml` is recommended.
  - `AlwaysBlank/WP/Mnemosyne/storage_path` The **absolute** path to the directory that contains your file (do not include the file name). This value defaults to Boolean `false`, which causes Mnemosyne to search the stylesheet directory as described above.

> **Note**: When you pass a path to `AlwaysBlank/WP/Mnemosyne/storage_path` it assumes you are supplying an exact path, and _will not_ perform a search in that directory—it will just concatenate the file path with the file name, and attempt to import the file at that location.

If you want to modify the way in which Mnemosyne searches for your file, there is a third filter available that allows you to modify the search before it is returned:

  - `AlwaysBlank/WP/Mnemosyne/storage_finder` This exposes a Finder object from [Symfony\Finder](https://symfony.com/doc/current/components/finder.html) package. 

This is useful if you want to do something like narrow the search to particular subdirectories, or exclude a directory from the search.

### Values

Currently Mnemosyne supports basic `key:value` pairs, including values that are more complex data types, such as arrays. Keys support only alphanumeric characters and underscores.

## Functions

It is recommended that you use Mnemosyne's convenience functions instead of accessing `Mnemosyne` directly.

These functions fail silently but throw exceptions when they do. Currently these exceptions are not displayed, but are stored in the PHP $GLOBALS variable: `$GLOBALS['AlwaysBlank_WP_Mnemosyne_errors']`

### __m()

`__m($key_name, $data_source [, $validation_function])`

This function returns a value from the defaults or source, as appropriate.

`$key_name`

The key for the value we want. The key will be used to look up a default.
 
`$data_source`

The data source that should be checked for an override. If this evaluates to a falsy value (i.e. false, null, ‘’) then the default will be used. 
 
`$validation_function`

An optional callback. This is the name of a function that, if defined, will be called and passed the evaluated result of $data_source as well as the $key_name. It should return either a falsy value (ideally Boolean `false`), or the result of $data_source.

### __me()

`__me($key_name, $data_source [, $validation_function])`

This function behaves identially to `__m()`, except that it echos the value.

> **Note:** Attempting to retrieve a value that is not echoable (i.e. not a string or numeric value) will throw an exception.

### mns_dig()

`mns_dig($key_name, $data_source, ...$layers)`

This function returns something from inside a retrieved array, instead of just the entire array.

`$key`

The key for our value. Functions just like `__m()`.

`$data_source`

The source of our override. Functions just like `__m()`.

`...$layers`

A list of comma-separated array keys, dictating the path into the returned array that we want to follow.

#### Example

Let's say the value for the key `some_key` was an array that looked like this:

```php
[
  'part_one' => [
    'person' => 'Jeff',
    'animal' => 'dog'
  ],
  'part_two' => [
    'spaceship' => 'enterprise'
  ]
]
```

Here are some examples of what `mns_dig()` would return:

```php
mns_dig('some_key', $data_source, 'part_one', 'animal');
// 'dog'

mns_dig('some_key', $data_source, 'part_two');
// ['spaceship' => 'enterprise']
```

> **Note:** `mns_dig()` does _not_ support a customized validation function like `__m()` or the other functions, because of the way it takes its arguments for array navigation.

### Others

See `src/Mnemosyne/mnemosyne_functions.php` for other functions and their documentation. Includes some tools for looking at errors and checking for overrides.
