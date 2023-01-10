<?php
declare(strict_types = 1);

namespace Apex\Container;

use Apex\Container\Container;
use Apex\Container\Interfaces\ApexContainerInterface;


/**
 * Wrapper class that provides access to all container methods statically, 
 * meant to help save from passing a container object from class to class, 
 * method to metohd.
 */
class Di
{

    // Container instantiation properties
        private static string $config_file = '';
    private static bool $use_autowiring = true;
    private static bool $use_attributes = false;
    private static bool $use_annotations = false;

    // Container instance
    private static ?ApexContainerInterface $instance = null;

    /**
     * Calls a method of the instance.
     */
    public static function __callstatic($method, $params) 
    {

        // Ensure we have an instance defined
        if (!self::$instance) {
            self::$instance = new Container(
                self::$config_file, 
                self::$use_autowiring,
                self::$use_attributes, 
                self::$use_annotations
            );
        }

        // Call method, and return 
        return self::$instance->$method(...$params);
    }

    /**
     * Set instance
     */
    public static function setContainer(ApexContainerInterface $cntr):void
    {
        self::$instance = $cntr;
    }

    /**
     * Get instance
     */
    public static function getContainer():?ApexContainerInterface
    {
        return self::$instance;
    }

}

