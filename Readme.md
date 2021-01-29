
# Apex Container

A lightweight, straight forward dependency injection container that simply works, and works well.  Supports config file and all standard injection methods -- constructor, setter, annotation, plus also attributes.  Also includes a `Di` wrapper class that allows container methods to be accessed statically for greater simplicity and efficiency.

## Installation

Install via Composer with:

> `composer require apex/container`

## Basic Usage

Supports the standard container methods -- `has, get, set, make, call`,

~~~php

use Apex\Container\Container;

$cntr = new Container('/path/to/definitions.php');

// Set and get
$cntr->set('full_name', 'John Doe');
// Get service
$logger = $cntr->get(LoggerInterface::class);
$logger->info("Here I am");

// Make and set
$user = $cntr->makeset(User::class, ['id' => 311]);

// Call via method setter injection
$cntr->call([$user, 'updateStatus'], ['status' => 'inactive']);
~~~


## Constructor

Constructor takes the following parameters, none of which are required.

Parameter | Type | Description
------------- |------------- |------------- 
`$config_file` | string | Location of the definitions file which defines starting items and services.  Should be a PHP file that returns an associative array.  Please see the <a href="https://github.com/apexpl/container/blob/master/docs/definitions.md">Definitions File</a> page for details.  If defind in the constructor, will be automatically loaded.
`$use_autowiring` | bool | Whether or not to enable auto-wiring.  If true, the `use` declarations of all files will be loaded, and checked for injection parameters.  Defaults to true.
`$use_attributes` | bool | If true, will scan attributes in all files for injection properties.  Please see the <a href="https://github.com/apexpl/container/blob/master/docs/attributes.md">Attributes Injection</a> page for details.  Defaults to false.
`$use_annotations` | bool | If true, will scan the properties and doc blocks of all files for injected properties.  Please see the <a href="https://github.com/apexpl/container/blob/master/docs/annotations.md">Annotation Injection</a> page for details.  Defaults to false.


## Methods

The following base methods are supported, as per all containers.

Method | Description
------------- |-------------
`has(string $name):bool` | Check whether or not item is available in container.
`get(string $name):mixed` | Gets an item from the container.  If does not already exist, will try to make and set the item.  Returns null on non-existent item.
`set(string $name, mixed $item):void` | Sets an item in the container.
`make(string $name, array $params = []):mixed` | Creates new instance of the class with provided parameters.
`makeset(string $name, array $params = []):mixed` | Creates new instance of the class with provided parameters, and also sets class name into container for future use.
`call(callable | array $callable, array $params = []):mixed` | Used for method / setter injection, and will call given method with injected parameters.  Callable may also be a two element array, the first element being a class name and second being an array of constructor parameters.  
`buildContainer(string $config_file):void` | Build container with the specified definitions file.  Useful if you didn't pass a definitions file to the constructor, or if you're using the `Di` class to access the container statically.


## Singleton Di Class

This package includes the `Di` class which acts as a singleton and allows container methods to be accessed statically, instead of passing the container object from class to class, method to method.  This is done to provide efficiency and simplicity, and also assumes you're only managing one DI container per-request.

Modify the properties within the `/src/Di.php` file as they are used as the constructor parameters for the container.  Example usage is below.

~~~php

namespace myApp;

use Apex\Container\Di;
use myApp\Users\User;

// Get logger
$logger = Di::get(LoggerInterface::Class);

// Make user object
Di::make(User::class, ['id' => 52]);

// Set and get variable
Di::set('full_name', 'John Doe');
$name = Di::get('full_name');   // John Doe
~~~


## Follow Apex

Loads of good things coming in the near future including new quality open source packages, more advanced articles / tutorials that go over down to earth useful topics, et al.  Stay informed by joining the <a href="https://apexpl.io/">mailing list</a> on our web site, or follow along on Twitter at <a href="https://twitter.com/ApexPlatform">@ApexPlatform</a>.




