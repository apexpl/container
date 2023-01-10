<?php
declare(strict_types = 1);

use Apex\Container\{Container, Config};
use PHPUnit\Framework\TestCase;


/**
 * Aliases test
 */
class aliases_test extends TestCase
{

    /**
     * Test
     */
    public function test_aliases()
    {

        $cntr = new Container();
        $cntr->set('full_name', 'Matt Dizak');
        $this->assertEquals('Matt Dizak', $cntr->get('full_name'));

        $cntr->addAlias('nm', 'full_name');
        $this->assertEquals('Matt Dizak', $cntr->get('nm'));

        $cntr->removeAlias('nm');
        $this->assertNull($cntr->get('nm'));
    }

}



