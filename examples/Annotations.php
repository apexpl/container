<?php

/**
 * Shows example of annoation based injection.  Upon running this file, 
 * look at the ClassAnnotations.php file to see what's happening.
 */

use Apex\Container\Container;

// Load composer
require_once(__DIR__ . '/../vendor/autoload.php');

/**
 * Normally, these files would be auto-loaded via Composer, but due to the 
 * examples they must be manually required.
 */
require_once(__DIR__ . '/ClassAnnotations.php');


// Start container
$cntr = new Container(
    config_file: __DIR__ . '/config/examples.php', 
    use_annotations: true
);

// Set an order id
$cntr->set('order_id', 18553);

/**
 * Make the ClassAnnotations class with injection.  Check 
 * the ClassAnnotations.php file to see the __construct() method and what it does.
 */
$obj = $cntr->make(ClassAnnotations::class);
$obj->example();


