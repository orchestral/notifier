<?php namespace Orchestra\Notifier\TestCase;

use Mockery as m;
use Illuminate\Support\Fluent;
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
        $mailer = m::mock('\Orchestra\Notifier\Mailer')->makePartial();
        $user = m::mock('\Orchestra\Notifier\RecipientInterface');

        $subject = 'foobar';
        $view    = 'foo.bar';
        $data    = array();
        $message = new Fluent(compact('subject', 'view', 'data'));

        $user->shouldReceive('getRecipientEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getRecipientName')->once()->andReturn('Administrator');

        $mailer->shouldReceive('push')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer) {
                    $c($mailer);

                    return array('hello@orchestraplatform.com');
                })
            ->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with($subject)->andReturnNull();

        $stub = new OrchestraNotifier($mailer);

        $this->assertTrue($stub->send($user, $message));
    }

    /**
     * Test Orchestra\Notifier\OrchestraNotifier::send() method with callback
     *
     * @test
     */
    public function testSendMethodWithCallback()
    {
        $mailer = m::mock('\Orchestra\Notifier\Mailer')->makePartial();
        $user = m::mock('\Orchestra\Notifier\RecipientInterface');

        $view = 'foo.bar';
        $data = array();
        $message = new Fluent(compact('view', 'data'));

        $callback = function ($mail) {
            $mail->subject('foobar!!');
        };

        $user->shouldReceive('getRecipientEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getRecipientName')->once()->andReturn('Administrator');

        $mailer->shouldReceive('push')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer) {
                    $c($mailer);

                    return array('hello@orchestraplatform.com');
                })
            ->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with('foobar!!')->andReturnNull();

        $stub = new OrchestraNotifier($mailer);

        $this->assertTrue($stub->send($user, $message, $callback));
    }

    /**
     * Test Orchestra\Notifier\OrchestraNotifier::send() method using
     * queue.
     *
     * @test
     */
    public function testSendMethodUsingQueue()
    {
        $mailer = m::mock('\Orchestra\Notifier\Mailer')->makePartial();
        $memory = m::mock('\Orchestra\Memory\Provider')->makePartial();
        $user = m::mock('\Orchestra\Notifier\RecipientInterface');

        $subject = 'foobar';
        $view    = 'foo.bar';
        $data    = array();
        $message = new Fluent(compact('subject', 'view', 'data'));

        $user->shouldReceive('getRecipientEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getRecipientName')->once()->andReturn('Administrator');

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

        $this->assertTrue($stub->send($user, $message));
    }

    /**
     * Test Orchestra\Notifier\OrchestraNotifier::send() method failed.
     *
     * @test
     */
    public function testSendMethodFailed()
    {
        $mailer = m::mock('\Orchestra\Notifier\Mailer')->makePartial();
        $user = m::mock('\Orchestra\Notifier\RecipientInterface');

        $subject = 'foobar';
        $view    = 'foo.bar';
        $data    = array();
        $message = new Fluent(compact('subject', 'view', 'data'));

        $user->shouldReceive('getRecipientEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getRecipientName')->once()->andReturn('Administrator');

        $mailer->shouldReceive('push')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer) {
                    $c($mailer);

                    return array();
                })
            ->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with($subject)->andReturnNull();

        $stub = new OrchestraNotifier($mailer);

        $this->assertFalse($stub->send($user, $message));
    }
}
