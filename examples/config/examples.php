<?php
namespace Apex\Container\Examples;

use Apex\Container\Config;

/**
 * Config file used for the example scripts within this 
 * directory, and nothing more.
 */
return [
    'dbname' => 'container_db', 
    'email' => 'matt.dizak@gmail.com', 
    'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'], 
    Config::class => \Apex\Container\Config::class
];

