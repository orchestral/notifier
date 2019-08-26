<?php

namespace Orchestra\Notifier\Tests\Unit\Handlers;

use Mockery as m;
use Orchestra\Notifier\Message;
use PHPUnit\Framework\TestCase;
use Orchestra\Notifier\Handlers\Laravel;

class LaravelTest extends TestCase
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
        $mailer = m::mock('Illuminate\Contracts\Mail\Mailer');
        $message = m::mock('Illuminate\Mail\Message');
        $user = m::mock('Orchestra\Contracts\Notification\Recipient', [
            'getRecipientEmail' => 'hello@orchestraplatform.com',
            'getRecipientName' => 'Administrator',
        ]);

        $subject = 'foobar';
        $view = 'foo.bar';
        $data = [];

        $mailer->shouldReceive('send')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer, $message) {
                    $c($message);

                    return $mailer;
                })
            ->shouldReceive('failures')->once()->andReturn([]);
        $message->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with($subject)->andReturnNull();

        $stub = new Laravel($mailer);
        $receipt = $stub->send($user, new Message(compact('subject', 'view', 'data')));

        $this->assertInstanceOf('Orchestra\Notifier\Receipt', $receipt);
        $this->assertTrue($receipt->sent());
    }

    /** @test */
    public function it_can_send_email_using_callback()
    {
        $mailer = m::mock('Illuminate\Contracts\Mail\Mailer');
        $message = m::mock('Illuminate\Mail\Message');
        $user = m::mock('Orchestra\Contracts\Notification\Recipient', [
            'getRecipientEmail' => 'hello@orchestraplatform.com',
            'getRecipientName' => 'Administrator',
        ]);

        $subject = 'foobar';
        $view = 'foo.bar';
        $data = [];

        $callback = function ($mail) {
            $mail->subject('foobar!!');
        };

        $mailer->shouldReceive('send')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer, $message) {
                    $c($message);

                    return $mailer;
                })
            ->shouldReceive('failures')->once()->andReturn([]);
        $message->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with($subject)->andReturnNull()
            ->shouldReceive('subject')->once()->with('foobar!!')->andReturnNull();

        $stub = new Laravel($mailer);

        $receipt = $stub->send($user, new Message(compact('subject', 'view', 'data')), $callback);

        $this->assertInstanceOf('Orchestra\Notifier\Receipt', $receipt);
        $this->assertTrue($receipt->sent());
    }

    /** @test */
    public function it_fails_to_send_email()
    {
        $mailer = m::mock('Illuminate\Contracts\Mail\Mailer');
        $message = m::mock('Illuminate\Mail\Message');
        $user = m::mock('Orchestra\Contracts\Notification\Recipient', [
            'getRecipientEmail' => 'hello@orchestraplatform.com',
            'getRecipientName' => 'Administrator',
        ]);

        $subject = 'foobar';
        $view = 'foo.bar';
        $data = [];

        $mailer->shouldReceive('send')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer, $message) {
                    $c($message);

                    return $mailer;
                })
            ->shouldReceive('failures')->once()->andReturn(['hello@orchestraplatform.com']);
        $message->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with($subject)->andReturnNull();

        $stub = new Laravel($mailer);
        $receipt = $stub->send($user, new Message(compact('subject', 'view', 'data')));

        $this->assertInstanceOf('Orchestra\Notifier\Receipt', $receipt);
        $this->assertFalse($receipt->sent());
    }
}
