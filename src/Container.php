<?php
declare(strict_types = 1);

namespace Apex\Container;

use Apex\Container\Services;
use Apex\Container\Interfaces\ApexContainerInterface;
use Psr\Container\ContainerInterface;
use Apex\Container\Exceptions\{ContainerClassNotExistsException, ContainerFileNotExistsException, ContainerParamTypeMismatchException, ContainerInjectionParamNotFoundException, ContainerInvalidConfigException};


/**
 * Lightweight dependancy injection container that is used within Apex.
 */
class Container extends Services implements ContainerInterface, ApexContainerInterface
{

    /**
     * Constructor
     */
    public function __construct(
        protected string $config_file = '', 
        bool $use_autowiring = true,
        bool $use_attributes = true,  
        bool $use_annotations = false
    ) {

        // Set properties
        $this->use_autowiring = $use_autowiring;
        $this->use_annotations = $use_annotations;
        $this->use_attributes = $use_attributes;

        // Build container, if config file defined
        if ($config_file != '') { 
            $this->buildContainer($config_file);
        }

        // ADd container as item
        $this->set(ContainerInterface::class, $this);
        $this->addAlias(__CLASS__, ContainerInterface::class, false);
        $this->addAlias(ApexContainerInterface::class, ContainerInterface::class, false);
    }

    /**
     * Get an item
     */
    public function get(string $name)
    {

        // Initialize
        $item = null;
        $params = [];

        // Check aliases
        if (isset($this->aliases[$name])) { 
            $name = $this->aliases[$name];
        }

        // Check if item exists in container
        if (isset($this->items[$name])) { 
            return $this->items[$name];
        }

        // Check for service
        if (isset($this->services[$name])) { 
            $svc = $this->services[$name];

            // Check if callable
            if (is_callable($svc)) { 
                $item = call_user_func($svc);

            } elseif (is_array($svc) && class_exists($svc[0])) { 
                $params = $svc[1] ?? [];
                $item = $this->make($svc[0], $params);

            } elseif (is_string($svc) && class_exists($svc)) { 
                $item = $this->make($svc);
            }

            // Set item, if needed
            if ($item !== null) { 
                $this->set($name, $item);
            }

        // Check if class exists
        } elseif (class_exists($name) && !enum_exists($name)) { 
            $item = $this->make($name);
        }

        // Return
        return $item;
    }

    /**
     * Has - Check if item is available.
     */
    public function has(string $name):bool
    {
        return isset($this->items[$name]) || isset($this->services[$name]) ? true : false;
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
        if (!list($class_name, $tmp_params) = $this->getClassName($name)) { 
            throw new ContainerClassNotExistsException("Unable to determine class name for item, $name");
        } elseif ($tmp_params !== null && count($params) == 0) {
            $params = $tmp_params;
        }

        // Check for closure
        if ($class_name == 'closure') { 
            return $this->call($this->items[$name], $params);
        }

        // Instantiate class
        $obj = new \ReflectionClass($class_name);

        // Get use declarations, if needed
        if ($this->use_autowiring === true && $obj->getFilename() && !isset($this->use_declarations[$class_name])) { 
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
    public function makeset(string $name, array $params = []):mixed
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
    public function call(callable | array | string $callable, array $params = []):mixed
    {

        // Check container for item
        if (is_string($callable) && isset($this->items[$callable]) && is_callable($this->items[$callable])) { 
            $callable = $this->items[$callable];
        } elseif (is_string($callable)) { 
            throw new ContainerClassNotExistsException("No callable exists in the container with the name, $callable");
        }

        // Instantiate reflection method
        if (is_callable($callable) && !is_array($callable)) { 
            $method = new \ReflectionFunction($callable);
        } else {
            $obj = is_object($callable[0]) ? $callable[0] : $this->make($callable[0], $params);
            $method = new \ReflectionMethod($obj, $callable[1]);
        }

        // Get injection params
        $inject_params = $this->getInjectionParams($method, $params);

        // Call and return
        if ($method::class == 'ReflectionFunction') { 
            return $method->invokeArgs($inject_params);
        } else { 
            return $method->invokeArgs($obj, $inject_params);
        }
    }

    /**
     * Get injection params
     */
    private function getInjectionParams(\ReflectionMethod | \ReflectionFunction $method, array $params = []):array
    {

        // Initialize
        $inject_params = [];
        $method_params = $method->getParameters();

        // Get declaring class
        if ($method::class == 'ReflectionFunction') { 
            $class = 'closure';
        } else { 
            $class = $method->getDeclaringClass()->getName();
        }

        // Go through params
        foreach ($method_params as $param) { 

            // Get param info
            $name = $param->getName();
            $type = $param?->getType();

            // If passed parameters have matching name
            if (array_key_exists($name, $params)) { 

                // Compare, and ensure types match
                if (!$this->compareParamType($params[$name], $type)) {
                $type_name = method_exists($type, 'getName') ? $type?->getName() : 'union type';
                    throw new ContainerParamTypeMismatchException("Parameter type mismatch during injection.  Within method '" . $class . '::' . $method->getName() . "' the parameter '$name' is expecting type, $type_name");
                }

                // Check for enum
                if ($type::class != 'ReflectionUnionType') {
                    $chk_type = $type?->getName();
                    if ($chk_type !== null && enum_exists($chk_type) && is_scalar($params[$name])) {
                        $params[$name] = $chk_type::from($params[$name]);
                    }
                }

                // Add to injected params
                $inject_params[$name] = $params[$name];
                continue;
            }

            // Get types
        $has_value=false;
            $types = $type !== null && $type::class == 'ReflectionUnionType' ? $type->getTypes() : [$type];
            foreach ($types as $tmp_type) {

                list($found, $value) = $this->getIndividualParam($param, $tmp_type);
                if ($found === false) {
                    continue;
                }

                // Found value for param
                $inject_params[$name] = $value;
                $has_value = true;
                break;
            }

            // Check for no value
            if ($has_value === false) {
                $method_name = $method->getDeclaringClass()->getName() . '::' . $method->getName();
                throw new ContainerInjectionParamNotFoundException("Unable to determine injection parameter for '$name' within method '$method_name'");
            }

        }

        // Return
        return $inject_params;
    }

    /**
     * Get individual injection param
     */
    private function getIndividualParam(\ReflectionParameter $param, ?\ReflectionType $type):array
    {

        // Initialize
        $type = $type?->getName();
        $name = $param->getName();

        // Check for value
        $value = null; $found = true;
        if (isset($this->items[$name])) { 
            $value = $this->get($name);
        } elseif ($type == 'DateTime') { 
            $value = $param->allowsNull() === true ? null : new \DateTime();
        } elseif ($type !== null && ($val = $this->get($type)) !== null) { 
            $value = $val;
        } elseif ($param->isDefaultValueAvailable() === true) { 
            $value = $param->getDefaultValue();
        } elseif ($param->isOptional() === true || $param->allowsNull() === true) { 
            $value = null;
        } else {    
            $found = false;
        }

        // Return
        return [$found, $value];
    }


    /**
     * Compare variable against a type.
     */
    private function compareParamType(mixed $item, ?\ReflectionType $chk):bool
    {

        // Set for reflection union type
        if ($chk::class == 'ReflectionUnionType') {

            foreach ($chk->getTypes() as $union_type) {
                if ($this->compareParamType($item, $union_type) === true) {
                    return true;
                }
            }
            return false;
        }

        // Ensure valid type
        $chk_type = $chk?->getName();
        if ($chk_type === null || $chk_type == '') { 
            return true;

        // Check for null
        } elseif ($chk->allowsNull() === true && $item === null) { 
            return true;

        // Check for enum
        } elseif (enum_exists($chk_type)) {
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
    private function getClassName(string $name):?array
    {

        // Initialize
        $class_name = null;
        $params = null;

        // Check defind services, and class name
        if (isset($this->services[$name]) && is_callable($this->services[$name])) { 
            $class_name = 'closure';
        } elseif (isset($this->services[$name])) { 
            $class_name = $this->services[$name][0];
        } else if (isset($this->items[$name]) && is_array($this->items[$name]) && class_exists($this->items[$name][0])) {
            $class_name = $this->items[$name][0];
            $params = $this->items[$name][1];
        } else if (isset($this->items[$name]) && is_string($this->items[$name]) && class_exists($this->items[$name])) {
            $class_name = $this->items[$name];
        } elseif (class_exists($name)) { 
            $class_name = $name;
        }

        // Return
        return [$class_name, $params];

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


