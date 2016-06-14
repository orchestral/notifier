<?php

namespace Orchestra\Notifier\TestCase;

use Mockery as m;
use Illuminate\Container\Container;
use Orchestra\Notifier\NotifierManager;

class NotifierManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * Test Orchestra\Notifier\NotifierManager::createDefaultDriver()
     * method.
     *
     * @test
     */
    public function testCreateDefaultDriverMethod()
    {
        $app = new Container();

        $app['config'] = $config = m::mock('\Illuminate\Contracts\Config\Repository');
        $app['mailer'] = $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer');

        $config->shouldReceive('get')->once()
            ->with('orchestra/notifier::driver', 'laravel')->andReturn('laravel');

        $stub = new NotifierManager($app);

        $this->assertInstanceOf('\Orchestra\Notifier\Handlers\Laravel', $stub->driver());
    }

    /**
     * Test Orchestra\Notifier\NotifierManager::createOrchestraDriver()
     * method.
     *
     * @test
     */
    public function testCreateOrchestraDriverMethod()
    {
        $app = new Container();

        $app['orchestra.mail'] = $mailer = m::mock('\Orchestra\Notifier\Mailer');
        $app['orchestra.memory'] = $memory = m::mock('\Orchestra\Memory\MemoryManager');

        $memory->shouldReceive('makeOrFallback')->once()->andReturn(m::mock('\Orchestra\Contracts\Memory\Provider'));

        $stub = new NotifierManager($app);

        $this->assertInstanceOf('\Orchestra\Notifier\Handlers\Orchestra', $stub->driver('orchestra'));
    }

    /**
     * Test Orchestra\Notifier\NotifierManager::createLaravelDriver()
     * method.
     *
     * @test
     */
    public function testCreateLaravelDriverMethod()
    {
        $app = new Container();

        $app['mailer'] = $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer');

        $stub = new NotifierManager($app);

        $this->assertInstanceOf('\Orchestra\Notifier\Handlers\Laravel', $stub->driver('laravel'));
    }

    /**
     * Test Orchestra\Notifier\NotifierManager::setDefaultDriver()
     * method.
     *
     * @test
     */
    public function testSetDefaultDriverMethod()
    {
        $app = new Container();

        $app['config'] = $config = m::mock('\Illuminate\Contracts\Config\Repository');

        $config->shouldReceive('set')->once()
            ->with('orchestra/notifier::driver', 'foo')->andReturnNull();

        $stub = new NotifierManager($app);

        $stub->setDefaultDriver('foo');
    }
}
