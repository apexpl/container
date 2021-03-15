<?php
declare(strict_types = 1);

use Apex\Container\{Container, Config};
use PHPUnit\Framework\TestCase;


/**
 * Container class tests
 */
class attributes_test extends TestCase
{


    /**
     * Test attribute injection
     */
    public function test_attributes()
    {

        require_once(__DIR__ . '/../examples/ClassAttributes.php');

        $cntr = new Container(
            config_file: __DIR__ . '/../examples/config/examples.php', 
            use_attributes: true
        );
        $cntr->markItemAsService(Config::class);

        $obj = $cntr->make(ClassAttributes::class, ['order_id' => 89]);
        $this->assertEquals(89, $obj->order_id);
        $this->assertInstanceOf(Container::class, $obj->cntr);
        $this->assertInstanceOf(Config::class, $obj->config);

    }

}



