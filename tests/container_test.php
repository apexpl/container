<?php
declare(strict_types = 1);

use Apex\Container\{Container, Config};
use PHPUnit\Framework\TestCase;


/**
 * Container class tests
 */
class container_test extends TestCase
{

    /**
     * Get / Set
     */
    public function test_getset()
    {

        $cntr = new Container();
        $this->assertFalse($cntr->has('email'));
        $this->assertNull($cntr->get('email'));

        $cntr->set('email', 'john@domain.com');
        $email = $cntr->get('email');
        $this->assertNotNull($email);
        $this->assertEquals('john@domain.com', $email);
        $this->assertTrue($cntr->has('email'));
    }

    /**
     * Test make
     */
    public function test_make()
    {

        require_once(__DIR__ . '/../examples/ClassConstructor.php');

        $cntr = new Container(__DIR__ . '/../examples/config/examples.php');
        $cntr->markItemAsService(Config::class);
        $obj = $cntr->make(ClassConstructor::class);
        $this->assertEquals('container_db', $obj->dbname);
        $this->assertInstanceOf(Container::class, $obj->cntr);

        $res = $cntr->call([$obj, 'set_example'], ['name' => 'Aristotle']);
        $this->assertEquals('ok', $res);
    }

    /**
     * Test call
     */
    public function test_call()
    {

        $cntr = new Container(__DIR__ . '/../examples/config/examples.php');
        $cntr->markItemAsService(Config::class);
        $res = $cntr->call([ClassConstructor::class, 'set_example'], ['name' => 'Sherlock Holmes']);
        $this->assertEquals('ok', $res);
    }

}



