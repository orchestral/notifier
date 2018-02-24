<?php

namespace Orchestra\Notifier\TestCase\Feature;

use Illuminate\Support\Facades\Config;
use Orchestra\Support\Facades\Notifier;

class NotifierManagerTest extends TestCase
{
    /** @test */
    public function it_can_driver_using_default()
    {
        config(['orchestra/notifier::driver' => 'laravel']);

        $this->assertInstanceOf('\Orchestra\Notifier\Handlers\Laravel', Notifier::driver());
    }

    /** @test */
    public function it_can_create_orchestra_driver()
    {
        $this->assertInstanceOf('\Orchestra\Notifier\Handlers\Orchestra', Notifier::driver('orchestra'));
    }

    /** @test */
    public function it_can_create_laravel_driver()
    {
        $this->assertInstanceOf('\Orchestra\Notifier\Handlers\Laravel', Notifier::driver('laravel'));
    }

    /** @test */
    public function it_can_set_default_driver()
    {
        Notifier::setDefaultDriver('foo');

        $this->assertSame('foo', Notifier::getDefaultDriver());
    }
}
