<?php
declare(strict_types = 1);

use Apex\Container\{Container, Config};
use PHPUnit\Framework\TestCase;


/**
 * Container class tests
 */
class annotations_test extends TestCase
{


    /**
     * Test annotation injection
     */
    public function test_annotations()
    {

        require_once(__DIR__ . '/../examples/ClassAnnotations.php');

        $cntr = new Container(
            config_file: __DIR__ . '/../examples/config/examples.php', 
            use_annotations: true
        );
        $cntr->markItemAsService(Config::class);

        $obj = $cntr->make(ClassAnnotations::class, ['order_id' => 89]);
        $this->assertEquals(89, $obj->order_id);
        $this->assertInstanceOf(Container::class, $obj->cntr);
        $this->assertInstanceOf(Config::class, $obj->config);

    }

}


