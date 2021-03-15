<?php
declare(strict_types = 1);

use Apex\Container\{Container, Annotations, Config};
use Apex\Container\Exceptions\{ContainerFileNotExistsException, ContainerInvalidConfigException};
use PHPUnit\Framework\TestCase;


/**
 * Configuration tests
 */
class config_test extends TestCase
{

    /**
     * Build simple container
     */
    public function test_simple()
    {

        // Load container
        $cntr = new Container(__DIR__ .'/../examples/config/sample.php');
        $cntr->markItemAsService(Config::class);
        $cntr->markItemAsService('anno');
        $this->assertEquals('my_database', $cntr->get('dbname'));
        $this->assertEquals('password', $cntr->get('dbpass'));

        $intervals = $cntr->get('intervals');
        $this->assertIsArray($intervals);
        $this->assertCount(5, $intervals);
        $this->assertContains('month', $intervals);

        $order_id = $cntr->call('getOrderId');
        $this->assertEquals(52, $order_id);

        // Ensure closure is returned
        $closure = $cntr->get('getOrderId');
        $this->assertTrue(is_callable($closure));

        $anno = $cntr->get('anno');
        $this->assertEquals(Annotations::class, $anno::class);
    }

    /**
     * buildContainer
     */
    public function test_build_container()
    {

        $cntr = new Container();
        $cntr->buildContainer(__DIR__ .'/../examples/config/sample.php');
        $cntr->markItemAsService(Config::class);
        $this->assertEquals('my_database', $cntr->get('dbname'));
        $this->assertEquals('password', $cntr->get('dbpass'));

    }

    /**
     * ContainerInvalidConfigException 
     */
    public function test_zero_raw_items_exception()
    {
        $this->expectException(ContainerInvalidConfigException::class);
        $cntr = new Container(__DIR__ . '/files/blank_config.php');
    }

    /**
     * ContainerInvalidConfigException 
     */
    public function test_no_array_exception()
    {
        $this->expectException(ContainerInvalidConfigException::class);
        $cntr = new Container(__DIR__ . '/files/noarray_config.php');
    }

    /**
     * File not exists exception
     */
    public function test_file_not_exists_exception()
    {
        $this->expectException(ContainerFileNotExistsException::class);
        $cntr = new Container();
        $cntr->buildContainer('some_junk_file_not_exists.php');
    }


}


