<?php

/**
 * KPT Cache Cleaner - Comprehensive Cache Management Utility
 *
 * A utility class for clearing and managing cache across all tiers with support
 * for CLI usage, selective clearing, and detailed reporting.
 *
 * @since 8.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package KP Library
 */

// throw it under my namespace
namespace KPT;

// use composer's autloader
use Composer\Autoload\ClassLoader;
use Exception;
use RuntimeException;

// Prevent multiple executions of this script
if (defined('KPT_CACHECLEANER_LOADED')) {
    return;
}
define('KPT_CACHECLEANER_LOADED', true);

// no direct access via web, but allow CLI
if (php_sapi_name() !== 'cli') {
    die('Direct Access is not allowed!');
}

// Check if the class doesn't exist before defining it
if (!class_exists('KPT\CacheCleaner')) {

    /**
     * Cache Cleaner - Comprehensive Cache Management Utility
     *
     * Provides methods for clearing cache data across all tiers with support
     * for CLI operations, selective clearing, and detailed reporting.
     *
     * @since 8.4
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package KP Library
     */
    class CacheCleaner
    {
        /**
         * CLI entry point
         *
         * @since 8.4
         * @author Kevin Pirnie <me@kpirnie.com>
         *
         * @param array $args Command line arguments (without script name)
         * @return int Exit code
         */
        public static function cli(array $args = []): int
        {
            // get our autoloader and try to include it
            $autoloadPath = CacheCleaner::getComposerAutoloadPath();
            if ($autoloadPath && file_exists($autoloadPath)) {
                include_once $autoloadPath;
            } else {
                die('Composer autoload.php not found!');
            }

            // hold our CLI arguments
            $args = self::parseArguments();

            // try to run out cleaning
            try {
                // if we have the clear_all
                if (isset($args['clear_all']) && $args['clear_all']) {
                    // clear all caches
                    Cache::clear();

                    // close the cache connections
                    Cache::close();
                }

                // if we have the cleanup
                if (isset($args['cleanup']) && $args['cleanup']) {
                    // clear all caches
                    Cache::cleanup();
                }

                // if the clear tier argument is set
                if (isset($args['clear_tier'])) {
                    // hold our tiers, and the chosen one
                    $validTiers = CacheTierManager::getValidTiers();
                    $tier = $args['clear_tier'];

                    // if the argument is in the list of tiers
                    if (in_array($tier, $validTiers)) {
                        // clear the tiers cache
                        Cache::clearTier($tier);
                    }
                }

            // whoopsie...
            } catch (Exception $e) {
                // Return error code
                return 1;
            }

            return 0;
        }

        /**
         * Parse the arguments passed to the script
         *
         * @since 8.4
         * @author Kevin Pirnie <me@kpirnie.com>
         *
         * @return array Array of arguments passed
         */
        private static function parseArguments(): array
        {
            // setup the argv global and hold the options return
            global $argv;
            $options = [];

            // if we only have 1 argument (which is the script name)
            if (count($argv) > 1) {
                // loop over the arguments, but skip the first one
                foreach (array_slice($argv, 1) as $arg) {
                    // set the arguments to the return options
                    if ($arg === '--clear_all') {
                        $options['clear_all'] = true;
                    } elseif (strpos($arg, '--clear_tier=') === 0) {
                        $tier = substr($arg, strlen('--clear_tier='));
                        $options['clear_tier'] = $tier;
                    } elseif ($arg === '--cleanup') {
                        $options['cleanup'] = true;
                    }
                }
            }

            // return the options
            return $options;
        }

        /**
         * Get the path to composer's autoload.php file
         *
         * @since 8.4
         * @author Kevin Pirnie <me@kpirnie.com>
         *
         * @return string Path to autoload.php
         * @throws RuntimeException If autoload.php cannot be found
         */
        private static function getComposerAutoloadPath(): string
        {
            // Check if ClassLoader exists (Composer is installed)
            if (!class_exists(ClassLoader::class)) {
                // Try to find it by traversing directories
                $dir = __DIR__;
                $maxDepth = 10; // Safety limit

                // loop the path until we find the vender autoload
                while ($dir !== '/' && $maxDepth-- > 0) {
                    $autoloadPath = $dir . '/vendor/autoload.php';
                    if (file_exists($autoloadPath)) {
                        return $autoloadPath;
                    }
                    $dir = dirname($dir);
                }

                // cant find it at all... throw an exception
                throw new RuntimeException('Composer ClassLoader not found. Make sure Composer dependencies are installed.');
            }

            // we need reflection here to get composer's autoloader ;)
            $reflection = new \ReflectionClass(ClassLoader::class);
            $vendorDir = dirname($reflection->getFileName(), 2);
            $autoloadPath = $vendorDir . '/autoload.php';

            // if the file does not exist, throw an exception
            if (!file_exists($autoloadPath)) {
                throw new RuntimeException('Composer autoload.php not found at: ' . $autoloadPath);
            }

            // return the path
            return $autoloadPath;
        }
    }
}

// CLI execution if called directly
if (php_sapi_name() === 'cli' && isset($argv) && realpath($argv[0]) === realpath(__FILE__)) {
    // clean the cache
    exit(CacheCleaner::cli());
}
