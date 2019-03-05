<?php

namespace Orchestra\Notifier\TestCase\Unit\Handlers;

use Mockery as m;
use Orchestra\Notifier\Message;
use Orchestra\Notifier\Receipt;
use PHPUnit\Framework\TestCase;
use Orchestra\Notifier\Handlers\Orchestra;

class OrchestraTest extends TestCase
{
    /**
     * Teardown the test environment.
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /** @test */
    public function it_can_send_email()
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

    /** @test */
    public function it_can_send_email_using_callback()
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

    /** @test */
    public function it_can_send_email_using_queue()
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

    /** @test */
    public function it_fails_to_send_email()
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
