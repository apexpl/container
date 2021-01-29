<?php

/**
 * Used in conjunction with the Constructor.php file, and 
 * this class should not be executed individually.
 */

use Apex\Container\{Container, Config};

class ClassConstructor
{

    /**
     * Constructor
     */
    public function __construct(
        public Container $cntr, 
        public string $dbname
    ) {

        /**
         * Since 'dbname' is an entry within the configuration file, the 
         * $dbname variable has been injected with its value.
         */
        echo "Injected Database Name: $dbname\n\n";

        /**
         * The above use declarations includes the Apex\Container\Container class,  
         * and Container class is already set in the container, hence $cntr has been injected with that object instance.
         */
        $email = $cntr->get('email');
        echo "E-Mail From Container: $email\n\n";
    }

    /**
     * Setter example
     */
    public function set_example(string $name, array $days, Config $config)
    {

        /**
         * 'name' was passed as an additional paramter to the call() method, hence was injected.
         */
        echo "Name: $name\n\n";

        /**
         * 'days' is within the configuration file and is an array, hence got injected.
         */
        echo "Third Day: " . $days[2] . "\n\n";

        /**
         * Config::class is within the configuration file, hence gets injected into $config.
         */
    echo "Config Class Name: " . $config::class . "\n\n";

        return "ok";

    }

}


