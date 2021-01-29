<?php
declare(strict_types = 1);

namespace Apex\Container;

use Apex\Container\Exceptions\{ContainerClassNotExistsException, ContainerFileNotExistsException, ContainerParamTypeMismatchException, ContainerInjectionParamNotFoundException, ContainerInvalidConfigException};


/**
 * Lightweight DI container that is used within Apex.
 */
class Container
{

    // Properties
    public array $services = [];
    public array $use_declarations = [];
    private array $items = [];

    /**
     * Constructor
     */
    public function __construct(
        private string $config_file = '', 
        private bool $use_autowiring = true,
        private $use_attributes = false,  
        private bool $use_annotations = false
    ) {

        // Build container, if config file defined
        if ($config_file != '') { 
            $this->buildContainer($config_file);
        }

    }

    /**
     * Build container from config file
     */
    public function buildContainer(string $config_file):void
    {

        // Purge any existing services
        $this->services = [];

        // Load config file
        $config = new Config($this);
        $this->items = $config->loadFile($config_file);

        // Check config options
        foreach (['use_autowiring', 'use_attributes', 'use_annotations'] as $var) { 
            if ($value = $this->get($var) && is_bool($value)) { 
                $this->$var = $value;
            }
        }

        // Set this instance in container
        $this->set(__CLASS__, $this);
    }

    /**
     * Get an item
     */
    public function get(string $name):mixed
    {

        // Initialize
        $item = null;
        $params = [];

        // Check if item exists in container
        if (isset($this->items[$name])) { 
            return $this->items[$name];
        }

    // Check for service
        if (isset($this->services[$name])) { 
            $svc = $this->services[$name];

            // Check if callable
            if (is_callable($svc)) { 
                $this->items[$name] = call_user_func($svc);
                return $this->items[$name];
            } else {
                return $this->makeset($this->services[$name][0], $this->services[$name][1]);
            }

        // Check for class name
        } elseif (class_exists($name)) { 
            return $this->makeset($name);
        }

        // Not found
        return null;
    }

    /**
     * Has - Check if item is available.
     */
    public function has(string $name):bool
    {
        return isset($this->items[$name]) || class_exists($name) ? true : false;
    }

    /**
     * Set item in container
     */
    public function set(string $name, mixed $item):void
    {
        $this->items[$name] =& $item;
    }

    /**
     * Make new item
     */
    public function make(string $name, array $params = []):mixed
    {

        // Get class name
        if (!$class_name = $this->getClassName($name)) { 
            throw new ContainerClassNotExistsException("Unable to determine class name for item, $name");
        }

        // Instantiate class
        $obj = new \ReflectionClass($class_name);

        // Get use declarations, if needed
        if ($this->use_autowiring === true && !isset($this->use_declarations[$class_name])) { 
            $this->use_declarations[$class_name] = $this->getUseDeclarations($obj->getFilename());
        }

        // Get injection params for constructor
        if ($method = $obj->getConstructor()) { 
            $inject_params = $this->getInjectionParams($method, $params);
        } else { 
            $inject_params = [];
        }

        // Instiantiate object with injected params
        $instance = $obj->NewInstanceArgs($inject_params);

        // Inject properties, if using annotations
        if ($this->use_annotations === true) { 
            $annotation_client = new Annotations($this);
            $annotation_client->injectProperties($instance, $obj, $params);
        }

        // Inject properties, if using attributes
        if ($this->use_attributes === true) { 
            $attribute_client = new Attributes($this);
            $attribute_client->injectProperties($instance, $obj, $params);
        }

        // Return
        return $instance;
    }

    /**
     * Make item, and set it into container as item.
     */
    public function makeset(string $name, array $params = [])
    {

        // Make the item
        $item = $this->make($name, $params);

        // Set item in container
        $this->set($name, $item);

        // Return
        return $item;
    }

    /**
     * Call a method -- setter injection
     */
    public function call(callable | array $callable, array $params = []):mixed
    {

        // Instantiate reflection class
        $obj = is_callable($callable) ? $callable[0] : $this->make($callable[0]);
        $instance = new \ReflectionClass($obj::class);

        // Get method and injection params
    $method = $instance->getMethod($callable[1]);
    $inject_params = $this->getInjectionParams($method, $params);

    // Call and return
    return $method->invokeArgs($obj, $inject_params);
    }

    /**
     * Get injection params
     */
    private function getInjectionParams(\ReflectionMethod $method, array $params = []):array
    {

        // Initialize
        $inject_params = [];
        $method_params = $method->getParameters();

        // Go through params
        foreach ($method_params as $param) { 

            // Get param info
            $name = $param->getName();
            $type = $param?->getType();

            // If passed parameters have matching name
            if (isset($params[$name])) {

                // Compare, and ensure types match
                if (!$this->compareParamType($params[$name], $type)) { 
                    throw new ContainerParamTypeMismatchException("Parameter type mismatch during injection.  Within method '" . $method->getName() . "' the parameter '$name' is expecting type '" . $type?->getName() . "'");
                }

                // Add to injected params
                $inject_params[$name] = $params[$name];
                continue;
            }

            // Check container for type
            $type = $type?->getName();
            if ($type !== null && $value = $this->get($type)) { 
                $inject_params[$name] = $value;

            } elseif (isset($this->items[$name])) { 
                $inject_params[$name] = $this->items[$name];

            } elseif ($param->isDefaultValueAvailable() === true) { 
                $inject_params[$name] = $param->getDefaultValue();

            } elseif ($param->isOptional() === true) { 
                $inject_params[$name] = null;

            // Unable to find value for required injection param
            } else {    
                throw new ContainerInjectionParamNotFoundException("Unable to determine injection parameter for '$name' within method '" . $method->getName() . "'");
            }
        }

        // Return
        return $inject_params;
    }

    /**
     * Compare variable against a type.
     */
    private function compareParamType(mixed $item, ?\ReflectionType $chk):bool
    {

        // Ensure valid type
        $chk_type = $chk?->getName();
        if ($chk_type === null || $chk_type == '') { 
            return true;
        }

        // Check for standard type
        if (in_array($chk_type, ['int', 'float', 'bool', 'string', 'array', 'array', 'object'])) { 
            $type = getType($item);

            if (
                ($type == $chk_type) || 
                ($chk_type == 'float' && $type == 'double') || 
                ($chk_type == 'int' && $type == 'integer') || 
                ($chk_type == 'bool' && $type == 'boolean')
            ) { 
                return true;
            }

            // Does not match
            return false;
        }

        // Check instanceof
        if ($item instanceof $chk_type) { 
            return true;

        // Check interfaces implemented
        } elseif (in_array($chk_type, array_values(class_implements($item)))) { 
            return true;
        }

        // Type does not match
        return false;
    }

    /**
     * Get class name of an item.
     */
    private function getClassName(string $name):?string
    {

        // Initialize
        $class_name = null;

        // Check defind services, and class name
        if (isset($this->services[$name])) { 
            $class_name = $this->services[$name][0];
        } elseif (class_exists($name)) { 
            $class_name = $name;
        }

        // Return
        return $class_name;

    }

    /**
     * Get use statements in file
     */
    private function getUseDeclarations(string $filename):array
    {

        // Check file exists
        if (!file_exists($filename)) { 
            throw new ContainerFileNotExistsException("Trying to get use declarations of non-existent file, $filename");
        }

        // Initialize
        $declarations = [];
        $lines = file($filename);

        // Go through lines
        foreach ($lines as $line) { 

            // Check line
            if (preg_match("/^(class|interface|trait|final|function)/", $line)) { 
                break;

            } elseif (Preg_match("/^use ((.*)\\\\(.+)\;|(.*)\;)/i", $line, $match)) {
                $last_name = $match[4] ?? $match[3];
                $class_name = isset($match[4]) ? '' : $match[2] . "\\";

                $names = explode(",", trim($last_name, '{}'));
                foreach ($names as $short_name) { 
                    $declarations[trim($short_name)] = $class_name . trim($short_name);
                }
            }

        }

        // Return
        return $declarations;
    }


}



