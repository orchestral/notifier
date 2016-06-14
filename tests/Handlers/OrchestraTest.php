<?php

namespace Orchestra\Notifier\Handlers\TestCase;

use Mockery as m;
use Orchestra\Notifier\Message;
use Orchestra\Notifier\Receipt;
use Orchestra\Notifier\Handlers\Orchestra;

class OrchestraTest extends \PHPUnit_Framework_TestCase
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
        $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer');
        $notifier = m::mock('\Orchestra\Notifier\Mailer')->makePartial();
        $message = m::mock('\Illuminate\Mail\Message');
        $user = m::mock('\Orchestra\Contracts\Notification\Recipient');

        $subject = 'foobar';
        $view = 'foo.bar';
        $data = [];

        $user->shouldReceive('getRecipientEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getRecipientName')->once()->andReturn('Administrator');
        $message->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with($subject)->andReturnNull();
        $mailer->shouldReceive('failures')->once()->andReturn([]);
        $notifier->shouldReceive('push')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer, $message) {
                    $c($message);

                    return new Receipt($mailer, false);
                });

        $stub = new Orchestra($notifier);
        $receipt = $stub->send($user, new Message(compact('subject', 'view', 'data')));

        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $receipt);
        $this->assertTrue($receipt->sent());
    }

    /**
     * Test Orchestra\Notifier\OrchestraNotifier::send() method with callback.
     *
     * @test
     */
    public function testSendMethodWithCallback()
    {
        $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer');
        $message = m::mock('\Illuminate\Mail\Message');
        $notifier = m::mock('\Orchestra\Notifier\Mailer')->makePartial();
        $user = m::mock('\Orchestra\Contracts\Notification\Recipient');

        $view = 'foo.bar';
        $data = [];

        $callback = function ($mail) {
            $mail->subject('foobar!!');
        };

        $user->shouldReceive('getRecipientEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getRecipientName')->once()->andReturn('Administrator');
        $message->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with('foobar!!')->andReturnNull();
        $mailer->shouldReceive('failures')->once()->andReturn([]);
        $notifier->shouldReceive('push')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer, $message) {
                    $c($message);

                    return new Receipt($mailer, false);
                });

        $stub = new Orchestra($notifier);
        $receipt = $stub->send($user, new Message(compact('view', 'data')), $callback);

        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $receipt);
        $this->assertTrue($receipt->sent());
    }

    /**
     * Test Orchestra\Notifier\OrchestraNotifier::send() method using
     * queue.
     *
     * @test
     */
    public function testSendMethodUsingQueue()
    {
        $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer');
        $message = m::mock('\Illuminate\Mail\Message');
        $notifier = m::mock('\Orchestra\Notifier\Mailer')->makePartial();
        $memory = m::mock('\Orchestra\Contracts\Memory\Provider');
        $user = m::mock('\Orchestra\Contracts\Notification\Recipient');

        $subject = 'foobar';
        $view = 'foo.bar';
        $data = [];

        $user->shouldReceive('getRecipientEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getRecipientName')->once()->andReturn('Administrator');
        $memory->shouldReceive('get')->once()->with('email.queue', false)->andReturn(true)
            ->shouldReceive('get')->once()->with('email.driver')->andReturn('mail');
        $message->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with($subject)->andReturnNull();
        $mailer->shouldReceive('failures')->never();
        $notifier->shouldReceive('push')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer, $message) {
                    $c($message);

                    return new Receipt($mailer, true);
                });

        $stub = new Orchestra($notifier);
        $stub->attach($memory);
        $receipt = $stub->send($user, new Message(compact('subject', 'view', 'data')));

        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $receipt);
        $this->assertTrue($receipt->sent());
    }

    /**
     * Test Orchestra\Notifier\OrchestraNotifier::send() method failed.
     *
     * @test
     */
    public function testSendMethodFailed()
    {
        $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer');
        $message = m::mock('\Illuminate\Mail\Message');
        $notifier = m::mock('\Orchestra\Notifier\Mailer')->makePartial();
        $user = m::mock('\Orchestra\Contracts\Notification\Recipient');

        $subject = 'foobar';
        $view = 'foo.bar';
        $data = [];

        $user->shouldReceive('getRecipientEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getRecipientName')->once()->andReturn('Administrator');
        $message->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with($subject)->andReturnNull();
        $mailer->shouldReceive('failures')->once()->andReturn(['hello@orchestraplatform.com']);
        $notifier->shouldReceive('push')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer, $message) {
                    $c($message);

                    return new Receipt($mailer, false);
                });

        $stub = new Orchestra($notifier);
        $receipt = $stub->send($user, new Message(compact('subject', 'view', 'data')));

        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $receipt);
        $this->assertFalse($receipt->sent());
    }
}
