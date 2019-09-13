<?php

namespace Orchestra\Notifier\Tests\Unit;

use Mockery as m;
use Orchestra\Notifier\NotifierServiceProvider;
use PHPUnit\Framework\TestCase;

class NotifierServiceProviderTest extends TestCase
{
    /**
     * Teardown the test environment.
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /** @test */
    public function it_deferred_registering_the_services()
    {
        $stub = new NotifierServiceProvider(null);

        $this->assertTrue($stub->isDeferred());
    }

    /** @test */
    public function it_can_provides_expected_services()
    {
        $stub = new NotifierServiceProvider(null);

        $this->assertEquals(['orchestra.postal', 'orchestra.notifier'], $stub->provides());
    }
}
