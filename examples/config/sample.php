<?php

use Apex\Cluster\Annotations;

/**
 * Below is a sample configuration file which you may either pass to the constructor or load 
 * via the Container::buildContainer() method.
 *
 * Supports variables / arrays, callables of all types, and two element arrays as callables / pointers to services.  With 
 * these first element is location of the class name, and second element is an array of parameters to pass to the constructor.  See below for an example.
 *
 * For full explanation of configuration file, please view the /docs/config.md file.
 */
return [

    // Basic variables, such as database info
    'dbname' => 'my_database',  
    'dbpass' => 'password', 

    // Arrays
    'intervals' => ['day', 'week', 'month', 'quarter', 'year'], 

    // Closure
    'shop' => function() { return new CoolShop(51, 'some_arg'); }, 
    'getOrderId' => function() { return 52; }, 

    // Service, no params
    'anno' => \Apex\Container\Annotations::class, 

    // Service, with params
    InstantiatorInterface::class => [Doctrine\Instantiator\Instantiator::class, []]

];


