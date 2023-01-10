<?php

/**
 * Shows examples of constructor and setter injection.
 */

use Apex\Container\{Container, Config};


// Load composer
require_once(__DIR__ . '/../vendor/autoload.php');

/**
 * Normally, these files would be auto-loaded via Composer, but due to the 
 * examples they must be manually required.
 */
require_once(__DIR__ . '/ClassConstructor.php');

// Start container
$cntr = new Container(__DIR__ . '/config/examples.php');
$cntr->markItemAsService(Config::class);

/**
 * Make the ClassConstructor class with injection.  Check 
 * the ClassConstructor.php file to see the __construct() method and what it does.
 */
$obj = $cntr->make(ClassConstructor::class);


/**
 * Example of setter injection by calling the set_example() method 
 * within the ClassConstructor class.
 */
$cntr->call([ClassConstructor::class, 'set_example'], ['name' => 'Sherlock Holmes']);


/**
 * The above re-instiantiated the class hence the lines from the constructor 
 * were printed twice.  However, you can also pass the object itself instead of just the class name.
 */
$cntr->call([$obj, 'set_example'], ['name' => 'Aristotle']);

