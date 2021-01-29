<?php
declare(strict_types = 1);

namespace Apex\Container;

use Apex\Container\Container;


/**
 * Handles attribute injection for properties and methods.
 */
class Attributes
{


    /**
     * Constructor
     */
    public function __construct(private Container $cntr)
    {

    }

    /**
     * Inject properties
     */
    public function injectProperties(object $instance, \ReflectionClass $reflect_obj, array $params = []):void
    {

        // Go through all properties
        $props = $reflect_obj->getProperties();
        foreach ($props as $prop) { 

            // Go through attributes
            $attributes = $prop->getAttributes();
            foreach ($attributes as $attr) { 

                // Inject, if we have an item
                if ($item = $this->checkAttribute($attr, $instance::class, $params)) { 
                    $prop->setAccessible(true);
                    $prop->setValue($instance, $item);
                }
            }
        }

    }

    /**
     * Check attribute for injection item
     */
    private function checkAttribute(\ReflectionAttribute $attr, string $class_name, array $params = []):mixed
    {
        // Check for Inject attribute
        if ($attr->getName() != 'Inject') { 
            return null;
        }

        // Get var_name
        $var_name = $attr->getArguments()[0] ?? '';
        if ($var_name == '') { 
            return null;
        }

        // Get use declarations
        $use = $this->cntr->use_declarations[$class_name] ?? [];

        // Check params
        if (isset($params[$var_name])) { 
            return $params[$var_name];
        }

        // Check use declarations
        if (isset($use[$var_name]) && $this->cntr->has($use[$var_name])) { 
            $var_name = $use[$var_name];

            // Check services container for name
            if (isset($this->services[$var_name])) { 
                $var_name = $this->services[$name][0];
            }
        }


        // Inject, if possible
        return $this->cntr->get($var_name);
    }


}



