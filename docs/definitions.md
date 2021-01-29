
# Definitions File

An optional definitions file can be passed to the container during instantiation, or via the `buildContainer()` method to initialize the container with various items and services.  This is a PHP file that simply returns an associative array.

Within the definitions file you may define variables, arrays, callables / closures, et al.  You may also define a two element array, and while not technically a callable, is interpreted as one by the container.  The first element is the class name, and the second is an optional array of parameters to pass to the constructor upon instantiation.  When the item is retrived from the container, an object of the class will be instantiated and returned.

Below is an example definitions file:

~~~
&lt;?php

use Psr\Log\LoggerInterface;
use Monolog\Logger;
use App\Store\Interfaces\ProductInterface;
use App\Store\Product;
use App\Store\Store;

return [
    'admin_email' => 'me@domain.com', 
    'dbinfo' => [
        'name' => 'mydb', 
        'user' => 'someuser', 
        'password' => 'secrety', 
        'host' => 'localhost', 
        'port' => 3306
    ], 

    LoggerInterface::class => function() { return new Logger('myapp'); }, 
    ProductInterface::class => [Product::class, ['supplier_id' => 62]], 
    'store' => Store::class, 

        'use_attributes' => tru, 
        'use_annotations' => false
];
~~~

The above `ProductInterface::class` entry, although not technically a callable, is somewhat treated as one by the container.  Upon loading the definitions file, the container will see it's an array with the first element being a class name that exists.  It will be added as a service, and an object of that class instantiated upon retriving the item from the container.  The second element in this definition is optional, and an array of parameters that will be passed to the constructor.

Same goes for the `store` entry in the above example.  Since it is a string, and is a class name that exists, the container will mark it as a service and return a new instance of the class when it is retrieved from the container.

Please note, if the items `use_autowiring`, `use_attributes` or `use_annotations` are present in the definitions file and are booleans, their respective options will be set in the container.


