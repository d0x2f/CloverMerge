<?php

namespace d0x2f\CloverMerge;

/**
 * Utility functions.
 */
class Utilities
{
    /**
     * Verify that each file in the given set exists.
     * Awaiting ds release to be able to use \Ds\Set.
     * https://github.com/php-ds/extension/commit/ae9ce662360e9f93b4b6c7abb78b938672be1abc
     *
     * @param \Ds\Vector $paths
     * @return boolean
     */
    public static function filesExist(\Ds\Vector $paths) : bool
    {
        return $paths->reduce(
            function (bool $carry, string $path) : bool {
                return $carry && is_file($path);
            },
            true
        );
    }

    /**
     * Check that the given XML element has the given attributes.
     *
     * @param \SimpleXMLElement $element
     * @param array $attribute_names
     * @return boolean
     */
    public static function xmlHasAttributes(
        \SimpleXMLElement $element,
        array $attribute_names
    ) : bool {
        $attributes = $element->attributes();
        if (is_null($attributes)) {
            return false;
        }
        $keys = array_keys(iterator_to_array($attributes));
        return count(array_intersect($keys, $attribute_names)) === count($attribute_names);
    }

    /**
     * Info log.
     *
     * @param string $message
     * @return void
     */
    public static function logInfo(string $message) : void
    {
        echo "INFO: {$message}\n";
    }

    /**
     * Warning log.
     *
     * @param string $message
     * @return void
     */
    public static function logWarning(string $message) : void
    {
        echo "WARNING: {$message}\n";
    }
}
