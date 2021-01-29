<?php

/**
 * Examples of basic operations with the container, such as 
 * get / set both variables and objects.
 */

use Apex\Container\{Container, Annotations, Config};

// Load composer
require_once(__DIR__ . '/../vendor/autoload.php');

// Start container
$cntr = new Container(__DIR__ . '/config/examples.php');

// Get dbname
$dbname = $cntr->get('dbname');
echo "DB Name: $dbname\n";

// Try e-mail
if (!$email = $cntr->get('email')) { 
    echo "No e-mail exists.\n";
}

// Set e-mail
$cntr->set('email', 'jhon@domain.com');

// Get e-mail now
$email = $cntr->get('email');
echo "E-Mail: $email\n";

// Get object defined in config file
$obj = $cntr->get(Config::class);
echo "Class Name: " . $obj::class . "\n";


// Set an object
$anno_obj = new Annotations($cntr);
$cntr->set('anno', $anno_obj);

// Retrive the newly set object
$obj = $cntr->get('anno');
echo "Got recently set object of class: " . $obj::class . "\n";

