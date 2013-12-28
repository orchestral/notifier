<?php namespace Orchestra\Notifier\TestCase;

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
     * Test Orchestra\Notifier\NotifierManager::createOrchestraDriver()
     * method.
     *
     * @test
     */
    public function testCreateOrchestraDriverMethod()
    {
        $app = new Container;

        $app['config'] = $config = m::mock('\Illuminate\Config\Repository');
        $app['orchestra.mail'] = $mailer = m::mock('\Orchestra\Notifier\Mailer');
        $app['orchestra.memory'] = $memory = m::mock('\Orchestra\Memory\Drivers\Driver');

        $config->shouldReceive('get')->once()
            ->with('orchestra/notifier::driver', 'orchestra')->andReturn('orchestra');
        $memory->shouldReceive('makeOrFallback')->once()->andReturn($memory);

        $stub = new NotifierManager($app);

        $this->assertInstanceOf('\Orchestra\Notifier\OrchestraNotifier', $stub->driver());
    }
}
