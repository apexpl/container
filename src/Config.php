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

        // Initialize
        $items = [];

        // Go through raw items
        foreach ($raw_items as $name => $item) { 

            // Check whether or not item is a service
            if (is_callable($item)) { 
                $this->cntr->services[$name] = $item;

            } elseif (is_string($item) && class_exists($item)) { 
                $this->cntr->services[$name] = [$item, []];

            } elseif (!is_array($item)) { 
                $items[$name] = $item;

            } elseif (!class_exists($item[0])) { 
                $items[$name] = $item;

            } else { 
                $params = $item[1] ?? [];
            $this->cntr->services[$name] = [$item[0], $params];
            }
        }

        // Return
        return $items;
    }

}


