<?php

namespace Orchestra\Notifier\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Mockery as m;
use Orchestra\Support\Facades\Notifier;

class NotifierManagerTest extends TestCase
{
    /** @test */
    public function it_can_driver_using_default()
    {
        config(['orchestra/notifier::driver' => 'laravel']);

        $this->app->instance('orchestra.platform.memory', $memory = m::mock('Orchestra\Contracts\Memory\Provider'));

        $memory->shouldReceive('get')->with('email', [])->andReturn([
                'driver' => 'smtp',
                'host' => 'smtp.mailgun.org',
                'port' => 587,
                'from' => [
                    'address' => 'hello@orchestraplatform.com',
                    'name' => 'Orchestra Platform',
                ],
            ])
            ->shouldReceive('get')->with('email.driver', 'mail')->twice()->andReturn('smtp')
            ->shouldReceive('get')->with('email.from')->once()->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $this->assertInstanceOf('Orchestra\Notifier\Handlers\Laravel', Notifier::driver());
    }

    /** @test */
    public function it_can_create_orchestra_driver()
    {
        $this->assertInstanceOf('Orchestra\Notifier\Handlers\Orchestra', Notifier::driver('orchestra'));
    }

    /** @test */
    public function it_can_create_laravel_driver()
    {
        $this->app->instance('orchestra.platform.memory', $memory = m::mock('Orchestra\Contracts\Memory\Provider'));

        $memory->shouldReceive('get')->with('email', [])->andReturn([
                'driver' => 'smtp',
                'host' => 'smtp.mailgun.org',
                'port' => 587,
                'from' => [
                    'address' => 'hello@orchestraplatform.com',
                    'name' => 'Orchestra Platform',
                ],
            ])
            ->shouldReceive('get')->with('email.driver', 'mail')->twice()->andReturn('smtp')
            ->shouldReceive('get')->with('email.from')->once()->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $this->assertInstanceOf('Orchestra\Notifier\Handlers\Laravel', Notifier::driver('laravel'));
    }

    /** @test */
    public function it_can_set_default_driver()
    {
        Notifier::setDefaultDriver('foo');

        $this->assertSame('foo', Notifier::getDefaultDriver());
    }
}
