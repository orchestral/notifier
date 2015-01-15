<?php namespace Orchestra\Notifier\TestCase;

use Mockery as m;
use Illuminate\Support\Fluent;
use Orchestra\Notifier\LaravelNotifier;

class LaravelNotifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * Test Orchestra\Notifier\LaravelNotifier::send() method without
     * queue.
     *
     * @test
     */
    public function testSendMethodSucceed()
    {
        $mailer = m::mock('\Illuminate\Mail\Mailer')->makePartial();
        $user = m::mock('\Orchestra\Notifier\RecipientInterface');

        $subject = 'foobar';
        $view    = 'foo.bar';
        $data    = array();
        $message = new Fluent(compact('subject', 'view', 'data'));

        $user->shouldReceive('getRecipientEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getRecipientName')->once()->andReturn('Administrator');

        $mailer->shouldReceive('send')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer) {
                    $c($mailer);

                    return array('hello@orchestraplatform.com');
                })
            ->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with($subject)->andReturnNull();

        $stub = new LaravelNotifier($mailer);
        $receipt = $stub->send($user, $message);

        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $receipt);
        $this->assertTrue($receipt->sent());
    }

    /**
     * Test Orchestra\Notifier\LaravelNotifier::send() method with callback
     *
     * @test
     */
    public function testSendMethodWithCallback()
    {
        $mailer = m::mock('\Illuminate\Mail\Mailer')->makePartial();
        $user = m::mock('\Orchestra\Notifier\RecipientInterface');

        $view = 'foo.bar';
        $data = array();
        $message = new Fluent(compact('view', 'data'));

        $callback = function ($mail) {
            $mail->subject('foobar!!');
        };

        $user->shouldReceive('getRecipientEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getRecipientName')->once()->andReturn('Administrator');

        $mailer->shouldReceive('send')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer) {
                    $c($mailer);

                    return array('hello@orchestraplatform.com');
                })
            ->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with('foobar!!')->andReturnNull();

        $stub = new LaravelNotifier($mailer);

        $this->assertTrue($stub->send($user, $message, $callback));
    }

    /**
     * Test Orchestra\Notifier\LaravelNotifier::send() method failed.
     *
     * @test
     */
    public function testSendMethodFailed()
    {
        $mailer = m::mock('\Illuminate\Mail\Mailer')->makePartial();
        $user = m::mock('\Orchestra\Notifier\RecipientInterface');

        $subject = 'foobar';
        $view    = 'foo.bar';
        $data    = array();
        $message = new Fluent(compact('subject', 'view', 'data'));

        $user->shouldReceive('getRecipientEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getRecipientName')->once()->andReturn('Administrator');

        $mailer->shouldReceive('send')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer) {
                    $c($mailer);

                    return $mailer;
                })
            ->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with($subject)->andReturnNull()
            ->shouldReceive('failures')->once()->andReturn(array('hello@orchestraplatform.com'));

        $stub = new LaravelNotifier($mailer);
        $receipt = $stub->send($user, $message);

        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $receipt);
        $this->assertFalse($receipt->sent());
    }
}
