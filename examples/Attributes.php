<?php

/**
 * Shows example of attributes based injection.  Upon running this file, 
 * look at the ClassAttributes.php file to see what's happening.
 */

use Apex\Container\Container;

// Load composer
require_once(__DIR__ . '/../vendor/autoload.php');

/**
 * Normally, these files would be auto-loaded via Composer, but due to the 
 * examples they must be manually required.
 */
require_once(__DIR__ . '/ClassAttributes.php');


// Start container
$cntr = new Container(
    config_file: __DIR__ . '/config/examples.php', 
    use_attributes: true
);

// Set an order id
$cntr->set('order_id', 18553);

/**
 * Make the ClassAttributes class with injection.  Check 
 * the ClassAttributes.php file to see the __construct() method and what it does.
 */
$obj = $cntr->make(ClassAttributes::class);
$obj->example();


