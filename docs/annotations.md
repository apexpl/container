
# Annotation Injection

This injection method allows properties to be injected into upon a class being instantiated.  Below shows a brief example:

~~~php

use App\Store\Store;

class Product
{

    /**
     * @Inject
     * @var Store
     */
    private Store $store;


    /**
     * @Inject
     * @var product_id
     */
    private int $pid;

    /**
     * Get product id
     */
    public function getProductId()
    {
        echo "Pid Is: " . $this->pid;
    }
{
~~~

You can now load this class and have the properties automatically injected into with:

~~~php

use Apex\Container\Container;
use App\Store\Product;

// Start container
$cntr = new Container(
    config_file: /path/to/definitions.php, 
    use_annotations: true
);

// Make class
$obj = $cntr->make(Product::class, ['product_id' => 18]);
$obj->getProductId()    // Prints "Product ID: 18"
~~~

Using the above example, upon loading the class the `$store` property would be injected with an instance of the `Store` object since it's defined within the user statements.  The container would be checked if an existing item with the `Store` class name exists, and if not will create a new instance.

The `$pid` property would be injected with the `product_id` parameter we passed upon creating the instance.

Please note, properties are injected into after instantiation of the object.  This means you can not use the properties within the constructor or you will receive a property not instantiated error.


