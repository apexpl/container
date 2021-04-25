<?php
declare(strict_types = 1);

namespace Apex\Container;

use Apex\Container\Container;


/**
 * Handles parsing of doc blocks and 
 * annotations for dependency injection.
 */
class Annotations
{

    /**
     * Constructor
     */
    public function __construct(
        private Container $cntr
    ) {

    }

    /**
     * Inject properties
     */
    public function injectProperties(object $instance, \ReflectionClass $reflect_obj, array $params = []):object
    {

        // Go through properties for annotation based injection
        $props = $reflect_obj->getProperties();
        foreach ($props as $prop) {

            // Check if doc block exists
            if (!$doc = $prop->getDocComment()) { 
                continue;
            }

            // Check doc block
            if ($item = $this->checkDocBlock($doc, $instance::class, $params)) { 
                $prop->setAccessible(true);
                $prop->setValue($instance, $item);
            }
        }

        // Return
        return $instance;
    }

    /**
     * Check doc block for injection
     */
    protected function checkDocBlock(string $docblock, string $class_name, array $params = []):mixed
    {

        // Initialize
        $is_inject = false;
        $var_name = '';
        $use = $this->cntr->use_declarations[$class_name] ?? [];
        $lines = explode("\n", $docblock);

        // Go through lines
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line == '' || $line == "/**" || $line == "*/") { 
                continue;
            }

            // Check line for injection
            if (preg_match("/\@inject/i", $line)) { 
                $is_inject = true;
            } elseif (preg_match("/\@var (.+)/", $line, $match)) { 
                $var_name = trim($match[1]);
            }
        }

        // Check params
        if (isset($params[$var_name])) { 
            return $params[$var_name];
        }

        // Check use declarations for injection
        if ($is_inject === true && isset($use[$var_name]) && $this->cntr->has($use[$var_name])) { 
            $var_name = $use[$var_name];

            // Check services container for name
            if (isset($this->services[$var_name])) { 
                $var_name = $this->services[$name][0];
            }
        }

        // Check for injection
        if ($is_inject === true && $item = $this->cntr->get($var_name)) { 
            return $item;
        }

        // Return
        return null;
    }

}


