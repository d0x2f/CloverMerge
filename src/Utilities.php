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
