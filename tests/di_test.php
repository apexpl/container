<?php
declare(strict_types = 1);

use Apex\Container\{Di, Annotations};
use PHPUnit\Framework\TestCase;


/**
 * Di class tests
 */
class di_test extends TestCase
{

    /**
     * Build simple container
     */
    public function test_simple()
    {

        // Load container
        Di::buildContainer(__DIR__ .'/../examples/config/sample.php');
        Di::markItemAsService('anno');
        $this->assertEquals('my_database', Di::get('dbname'));
        $this->assertEquals('password', Di::get('dbpass'));

        $intervals = Di::get('intervals');
        $this->assertIsArray($intervals);
        $this->assertCount(5, $intervals);
        $this->assertContains('month', $intervals);

        $order_id = Di::call('getOrderId');
        $this->assertEquals(52, $order_id);

        // Check closure
        $closure = Di::get('getOrderId');
        $this->assertTrue(is_callable($closure));
        $anno = Di::get('anno');
        $this->assertEquals(Annotations::class, $anno::class);
    }

}


