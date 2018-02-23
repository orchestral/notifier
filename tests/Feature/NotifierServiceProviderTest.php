<?php

namespace Orchestra\Notifier\TestCase\Feature;

class NotifierServiceProviderTest extends TestCase
{
    /**
     * Test Orchestra\Notifier\NotifierServiceProvider::register() method.
     *
     * @test
     */
    public function testRegisterMethod()
    {
        $this->assertInstanceOf('\Orchestra\Notifier\NotifierManager', $this->app['orchestra.notifier']);
    }
}
