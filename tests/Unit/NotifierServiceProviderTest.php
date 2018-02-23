<?php

namespace Orchestra\Notifier\TestCase\Unit;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Illuminate\Container\Container;
use Orchestra\Notifier\NotifierServiceProvider;

class NotifierServiceProviderTest extends TestCase
{
    /**
     * Teardown the test environment.
     */
    protected function tearDown()
    {
        m::close();
    }

    /**
     * Test Orchestra\Notifier\NotifierServiceProvider::provides() method.
     *
     * @test
     */
    public function testProvidesMethod()
    {
        $stub = new NotifierServiceProvider(null);

        $this->assertEquals(['orchestra.mail', 'orchestra.notifier'], $stub->provides());
    }

    /**
     * Test Orchestra\Notifier\NotifierServiceProvider is deferred.
     *
     * @test
     */
    public function testServiceIsDeferred()
    {
        $stub = new NotifierServiceProvider(null);

        $this->assertTrue($stub->isDeferred());
    }
}
