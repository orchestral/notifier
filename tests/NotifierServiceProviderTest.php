<?php namespace Orchestra\Notifier\TestCase;

use Mockery as m;
use Illuminate\Container\Container;
use Orchestra\Notifier\NotifierServiceProvider;

class NotifierServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * Test Orchestra\Notifier\NotifierServiceProvider::register() method.
     *
     * @test
     */
    public function testRegisterMethod()
    {
        $app = m::mock('\Illuminate\Contracts\Container\Container');

        $app->shouldReceive('singleton')->once()->with('orchestra.mail', m::type('Closure'))
                ->andReturnUsing(function ($n, $c) use ($app) {
                    return $c($app);
                })
            ->shouldReceive('singleton')->once()->with('orchestra.notifier', m::type('Closure'))
                ->andReturnUsing(function ($n, $c) use ($app) {
                    return $c($app);
                });

        $stub = new NotifierServiceProvider($app);
        $stub->register();
    }

    /**
     * Test Orchestra\Notifier\NotifierServiceProvider::boot() method.
     *
     * @test
     */
    public function testBootMethod()
    {
        $path = realpath(__DIR__.'/../');
        $app = new Container();

        $app['path.base'] = '/var/laravel';
        $app['config'] = $config = m::mock('\Orchestra\Contracts\Config\PackageRepository');
        $app['mailer'] = $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer');
        $app['orchestra.mail'] = $orchestraMailer = m::mock('\Orchestra\Notifier\Mailer');
        $swiftMailer = m::mock('\Swift_Mailer')->makePartial();
        $plugin = m::type('\Orchestra\Notifier\Plugins\CssInliner');

        $mailer->shouldReceive('getSwiftMailer')->once()->andReturn($swiftMailer);
        $orchestraMailer->shouldReceive('getSwiftMailer')->once()->andReturn($swiftMailer);
        $swiftMailer->shouldReceive('registerPlugin')->twice()->with($plugin)->andReturnNull();

        $config->shouldReceive('package')->once()
                ->with('orchestra/notifier', "{$path}/resources/config", 'orchestra/notifier')
                ->andReturnNull();

        $stub = new NotifierServiceProvider($app);

        $stub->boot();
    }

    /**
     * Test Orchestra\Notifier\NotifierServiceProvider::provides() method.
     *
     * @test
     */
    public function testProvidesMethod()
    {
        $app = new Container();
        $stub = new NotifierServiceProvider($app);

        $this->assertEquals(['orchestra.mail', 'orchestra.notifier'], $stub->provides());
    }

    /**
     * Test Orchestra\Notifier\NotifierServiceProvider is deferred.
     *
     * @test
     */
    public function testServiceIsDeferred()
    {
        $app = new Container();
        $stub = new NotifierServiceProvider($app);

        $this->assertTrue($stub->isDeferred());
    }
}
