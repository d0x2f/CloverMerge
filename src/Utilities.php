<?php

namespace d0x2f\CloverMerge;

/**
 * Utility functions.
 */
class Utilities
{
    /**
     * Verify that each file in the given set exists.
     *
     * @param \Ds\Set $paths
     * @return boolean
     */
    public static function filesExist(\Ds\Set $paths) : bool
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
