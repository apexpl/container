<?php
declare(strict_types = 1);

namespace Apex\Container;

use Apex\Container\Container;
use Apex\Container\Exceptions\{ContainerFileNotExistsException, ContainerInvalidConfigException};

/**
 * Handles the configuration file for the Apex container for all 
 * services and other start-up data.
 */
class Config
{

    /**
     * Constructor
     */
    public function __construct(private Container $cntr) 
    {

    }

    /**
     * Load config file
     */
    public function loadFile(string $filename):array
    {

        // Ensure file exists
        if (!file_exists($filename)) { 
            throw new ContainerFileNotExistsException("Configuration file does not exist, $filename");
        }

        // Load file
        $raw_items = require($filename);
        if (!is_array($raw_items)) { 
            throw new ContainerInvalidConfigException("Configuration file does not output an array, $filename");
        } elseif (count($raw_items) == 0) { 
            throw new ContainerInvalidConfigException("Configuration file does not contain any items, $filename");
        }

        // Load raw items
        return $this->loadArray($raw_items);
    }

    /**
     * Load config array
     */
    public function loadArray(array $raw_items):array
    {

        // Go through raw items
        $items = [];
        foreach ($raw_items as $name => $item) { 

            // Add item, if not already exists
            if ($this->cntr->has($name) === true) { 
                continue;
            }
            $items[$name] = $value;
        }

        // Return
        return $items;
    }

}


