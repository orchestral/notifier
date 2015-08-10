<?php namespace Orchestra\Notifier\Handlers\TestCase;

use Mockery as m;
use Orchestra\Notifier\Message;
use Orchestra\Notifier\Handlers\Laravel;

class LaravelTest extends \PHPUnit_Framework_TestCase
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
        $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer');
        $message = m::mock('\Illuminate\Mail\Message');
        $user = m::mock('\Orchestra\Contracts\Notification\Recipient');

        $subject = 'foobar';
        $view = 'foo.bar';
        $data = [];

        $user->shouldReceive('getRecipientEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getRecipientName')->once()->andReturn('Administrator');
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

        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $receipt);
        $this->assertTrue($receipt->sent());
    }

    /**
     * Test Orchestra\Notifier\LaravelNotifier::send() method with callback.
     *
     * @test
     */
    public function testSendMethodWithCallback()
    {
        $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer');
        $message = m::mock('\Illuminate\Mail\Message');
        $user = m::mock('\Orchestra\Contracts\Notification\Recipient');

        $subject = 'foobar';
        $view = 'foo.bar';
        $data = [];

        $callback = function ($mail) {
            $mail->subject('foobar!!');
        };

        $user->shouldReceive('getRecipientEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getRecipientName')->once()->andReturn('Administrator');

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

        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $receipt);
        $this->assertTrue($receipt->sent());
    }

    /**
     * Test Orchestra\Notifier\LaravelNotifier::send() method failed.
     *
     * @test
     */
    public function testSendMethodFailed()
    {
        $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer');
        $message = m::mock('\Illuminate\Mail\Message');
        $user = m::mock('\Orchestra\Contracts\Notification\Recipient');

        $subject = 'foobar';
        $view = 'foo.bar';
        $data = [];

        $user->shouldReceive('getRecipientEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getRecipientName')->once()->andReturn('Administrator');
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

        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $receipt);
        $this->assertFalse($receipt->sent());
    }
}
