<?php

namespace Orchestra\Notifier\Tests\Feature;

class NotifierServiceProviderTest extends TestCase
{
    /** @test */
    public function it_can_register_services()
    {
        $this->assertInstanceOf('Orchestra\Notifier\NotifierManager', $this->app['orchestra.notifier']);
    }
}
