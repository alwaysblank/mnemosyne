<?php 

/**
 * Singleton factory defintion.
 *
 * @package    Murmur\WP\Mnemosyne
 * @author     Ben Martinez-Bateman <ben@murmurcreative.com>
 * @link       https://bitbucket.org/murmurcreative/mnemosyne
 * @since      0.2.0
 */

namespace Murmur\WP\Mnemosyne;

use Murmur\WP\Mnemosyne\Storage;


/**
 * Singleton factory for managing Storage objects (and consequently preventing
 * repeated attempts to access the file system).
 *
 * @since      0.2.0
 */
class StorageFactory
{
    /**
     * Stores the factory instance.
     *
     * @since      0.2.0
     */
    private static $factory;

    /**
     * Stores the Storage instance.
     *
     * @since      0.2.0
     */
    private $storage;

    /**
     * Return the factory.
     *
     * @return     object  A StorageFactory instance.
     */
    public static getFactory()
    {
        if (!self::$factory)
            self::$factory = new StorageFactory();
        return self::$factory;
    }


    /**
     * Gets the storage.
     *
     * @return     object  A Storage instance.
     * @since      0.2.0
     */
    public function getStorage()
    {
        if (!$this->storage)
            $this->storage = new Storage();
        return $this->storage;
    }
}