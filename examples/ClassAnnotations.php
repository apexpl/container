<?php

/**
 * Used in conjunction with the Annotations.php file, and 
 * this class should not be executed individually.
 */

use Apex\Container\{Container, Config};

class ClassAnnotations
{

    /**
     * @Inject
     * @var order_id
     */
    public int $order_id;

    /**
     * @Inject
     * @var Container
     */
    public Container $cntr;

    /**
     * @Inject
     * @var Config
     */
    public Config $config;

    /**
     * Example method.  Due to annotation injection, unable to put into constructor as the 
     * properties are injected into after the object is instantiated.
     */
    public function example()
    {

        /**
         * Variable set into container during execution
         */
        echo "Order ID: $this->order_id\n\n";

        /**
         * Container was injected into $cntr
         */
        echo "DB Name from Configuration: " . $this->cntr->get('dbname') . "\n\n";

        /**
         * Config class is within configuration file, hence was 
         * instiantiated and injected.
         */
        echo "Config Class: " . $this->config::class . "\n\n";

    }

}


