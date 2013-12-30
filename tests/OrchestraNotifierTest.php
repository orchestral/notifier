<?php namespace Orchestra\Notifier\TestCase;

use Mockery as m;
use Orchestra\Notifier\OrchestraNotifier;

class OrchestraNotifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * Test Orchestra\Notifier\OrchestraNotifier::send() method without
     * queue.
     *
     * @test
     */
    public function testSendMethodWithoutQueue()
    {
        $mailer = m::mock('\Orchestra\Notifier\Mailer[push]');
        $user = m::mock('\Orchestra\Notifier\UserProviderInterface');
        $subject = 'foobar';
        $view = 'foo.bar';
        $data = array();

        $user->shouldReceive('getNotifierEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getNotifierName')->once()->andReturn('Administrator');

        $mailer->shouldReceive('push')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer) {
                    $c($mailer);

                    return array('hello@orchestraplatform.com');
                })
            ->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with($subject)->andReturnNull();

        $stub = new OrchestraNotifier($mailer);

        $this->assertTrue($stub->send($user, $subject, $view, $data));
    }

    /**
     * Test Orchestra\Notifier\OrchestraNotifier::send() method with callback
     *
     * @test
     */
    public function testSendMethodWithCallback()
    {
        $mailer = m::mock('\Orchestra\Notifier\Mailer[push]');
        $user = m::mock('\Orchestra\Notifier\UserProviderInterface');
        $view = 'foo.bar';
        $data = array();

        $callback = function ($mail) {
            $mail->subject('foobar!!');
        };

        $user->shouldReceive('getNotifierEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getNotifierName')->once()->andReturn('Administrator');

        $mailer->shouldReceive('push')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer) {
                    $c($mailer);

                    return array('hello@orchestraplatform.com');
                })
            ->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with('foobar!!')->andReturnNull();

        $stub = new OrchestraNotifier($mailer);

        $this->assertTrue($stub->send($user, null, $view, $data, $callback));
    }

    /**
     * Test Orchestra\Notifier\OrchestraNotifier::send() method using
     * queue.
     *
     * @test
     */
    public function testSendMethodUsingQueue()
    {
        $mailer = m::mock('\Orchestra\Notifier\Mailer[push]');
        $memory = m::mock('\Orchestra\Memory\Provider[get]');
        $user = m::mock('\Orchestra\Notifier\UserProviderInterface');
        $subject = 'foobar';
        $view = 'foo.bar';
        $data = array();

        $user->shouldReceive('getNotifierEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getNotifierName')->once()->andReturn('Administrator');

        $memory->shouldReceive('get')->once()->with('email.queue', false)->andReturn(true);
        $mailer->shouldReceive('push')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer) {
                    $c($mailer);

                    return array();
                })
            ->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with($subject)->andReturnNull();

        $stub = new OrchestraNotifier($mailer);
        $stub->attach($memory);

        $this->assertTrue($stub->send($user, $subject, $view, $data));
    }

    /**
     * Test Orchestra\Notifier\OrchestraNotifier::send() method failed.
     *
     * @test
     */
    public function testSendMethodFailed()
    {
        $mailer = m::mock('\Orchestra\Notifier\Mailer[push]');
        $user = m::mock('\Orchestra\Notifier\UserProviderInterface');
        $subject = 'foobar';
        $view = 'foo.bar';
        $data = array();

        $user->shouldReceive('getNotifierEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getNotifierName')->once()->andReturn('Administrator');

        $mailer->shouldReceive('push')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer) {
                    $c($mailer);

                    return array();
                })
            ->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with($subject)->andReturnNull();

        $stub = new OrchestraNotifier($mailer);

        $this->assertFalse($stub->send($user, $subject, $view, $data));
    }
}
