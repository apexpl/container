<?php

/**
 * Examples of basic operations using the Di wrapper class 
 * that allows the container methods to be accessed statically.
 */

use Apex\Container\{Di, Config};

// Load composer
require_once(__DIR__ . '/../vendor/autoload.php');

// Start container
Di::buildContainer(__DIR__ . '/config/examples.php');

// Get dbname
$dbname = Di::get('dbname');
echo "DB Name: $dbname\n";

// Try e-mail
if (!$email = Di::get('email')) { 
    echo "No e-mail exists.\n";
}

// Set e-mail
Di::set('email', 'jhon@domain.com');

// Get e-mail now
$email = Di::get('email');
echo "E-Mail: $email\n";

// Get object defined in config file
$obj = Di::get(Config::class);
echo "Class Name: " . $obj::class . "\n";


