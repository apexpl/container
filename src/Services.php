<?php
declare(strict_types = 1);

namespace Apex\Container;

use Apex\Container\Exceptions\ContainerInvalidConfigException;


/**
 * Services class
 */
class Services
{

    // Properties
    protected array $services = [];
    protected array $items = [];
    protected array $aliases = [];
    protected string $fail_reason = '';
        public array $use_declarations = [];

    /**
     * Build container from config file
     */
    public function buildContainer(string $config_file = '', array $items = []):void
    {

        // Initialize
        $config = new Config($this);

        // Load array of items, if present
        if (count($items) > 0) { 
            $new_items = $config->loadArray($items);
        } else { 
            $new_items = $config->loadFile($config_file);
        }
        $this->items = array_merge($new_items, $this->items);

        // Check config options
        foreach (['use_autowiring', 'use_attributes', 'use_annotations'] as $var) { 
            if ($value = $this->get($var) && is_bool($value)) { 
                $this->$var = $value;
            }
        }

    }

    /**
     * Get fail reason
     */
    public function getFailReason():string
    {
        return $this->fail_reason;
    }

    /**
     * Mark item as service
     */
    public function markItemAsService(string $name):bool
    {

        // Ensure item exists, and not already service
        if (isset($this->services[$name])) { 
            $this->fail_reason = "Item is already marked as a service, $name";
            return false;
        } elseif (!isset($this->items[$name])) { 
            $this->fail_reason = "Item does not exist in container, $name";
            return false;
        } elseif (isset($this->items[$name]) && is_object($this->items[$name] && !is_callable($this->items[$name]))) { 
            $this->fail_reason = "Item already exists and is instantiated, $name";
            return false;
        }

        // Set service
        $svc = $this->items[$name];
            if (is_callable($svc)) { 
            $this->services[$name] = $svc;
        } elseif (is_array($svc) && class_exists($svc[0])) { 
            $params = $svc[1] ?? [];
            $this->services[$name] = [$svc[0], $params];
        } elseif (is_string($svc) && class_exists($svc)) { 
            $this->services[$name] = [$svc, []];
        } else {
            $this->fail_reason = "Unable to instantiate the item, " . $this->items[$name][0] . " for the service, $name.  Removing item from container.";
            unset($this->items[$name]);
            return false;
            //throw new ContainerInvalidConfigException("Unable to mark item '$name' as service, as it can not be called or instantiated.");
        }
        unset($this->items[$name]);

        // Return
        return true;
    }

    /**
     * Unmark item as service
     */
    public function unmarkItemAsService(string $name):bool
    {

        // Check if service
        if (!isset($this->services[$name])) { 
            return false;
        }

        // Unmark item
        $this->items[$name] = $this->services[$name];
        unset($this->services[$name]);

        // Return
        return true;
    }

    /**
     * Add alias
     */
    public function addAlias(string $alias, string $value, bool $alias_class = true):void
    {
        $this->aliases[$alias] = $value;

        if ($alias_class === true) { 
            class_alias($value, $alias);
        }
    }

    /**
     * Remove alias
     */
    public function removeAlias(string $alias):void
    {
        unset($this->aliases[$alias]);
    }

}


