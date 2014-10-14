<?php namespace Orchestra\Notifier\TestCase;

use Mockery as m;
use Orchestra\Notifier\Message;
use Orchestra\Notifier\OrchestraNotifier;
use Orchestra\Notifier\Receipt;

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
        $mailer = m::mock('\Illuminate\Mail\Mailer');
        $notifier = m::mock('\Orchestra\Notifier\Mailer')->makePartial();
        $user = m::mock('\Orchestra\Contracts\Notification\Recipient');

        $subject = 'foobar';
        $view    = 'foo.bar';
        $data    = array();
        $message = new Message(compact('subject', 'view', 'data'));

        $user->shouldReceive('getRecipientEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getRecipientName')->once()->andReturn('Administrator');

        $mailer->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with($subject)->andReturnNull()
            ->shouldReceive('failures')->once()->andReturn(array());

        $notifier->shouldReceive('push')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer) {
                    $c($mailer);

                    return new Receipt($mailer, false);
                });

        $stub = new OrchestraNotifier($notifier);
        $receipt = $stub->send($user, $message);

        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $receipt);
        $this->assertTrue($receipt->sent());
    }

    /**
     * Test Orchestra\Notifier\OrchestraNotifier::send() method with callback
     *
     * @test
     */
    public function testSendMethodWithCallback()
    {
        $mailer = m::mock('\Illuminate\Mail\Mailer');
        $notifier = m::mock('\Orchestra\Notifier\Mailer')->makePartial();
        $user = m::mock('\Orchestra\Contracts\Notification\Recipient');

        $view = 'foo.bar';
        $data = array();
        $message = new Message(compact('view', 'data'));

        $callback = function ($mail) {
            $mail->subject('foobar!!');
        };

        $user->shouldReceive('getRecipientEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getRecipientName')->once()->andReturn('Administrator');

        $mailer->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with('foobar!!')->andReturnNull()
            ->shouldReceive('failures')->once()->andReturn(array());

        $notifier->shouldReceive('push')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer) {
                    $c($mailer);

                    return new Receipt($mailer, false);
                });

        $stub = new OrchestraNotifier($notifier);
        $receipt = $stub->send($user, $message, $callback);

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
        $mailer = m::mock('\Illuminate\Mail\Mailer');
        $notifier = m::mock('\Orchestra\Notifier\Mailer')->makePartial();
        $memory = m::mock('\Orchestra\Memory\Provider')->makePartial();
        $user = m::mock('\Orchestra\Contracts\Notification\Recipient');

        $subject = 'foobar';
        $view    = 'foo.bar';
        $data    = array();
        $message = new Message(compact('subject', 'view', 'data'));

        $user->shouldReceive('getRecipientEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getRecipientName')->once()->andReturn('Administrator');

        $memory->shouldReceive('get')->once()->with('email.queue', false)->andReturn(true);

        $mailer->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with($subject)->andReturnNull()
            ->shouldReceive('failures')->never();

        $notifier->shouldReceive('push')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer) {
                    $c($mailer);

                    return new Receipt($mailer, true);
                });

        $stub = new OrchestraNotifier($notifier);
        $stub->attach($memory);
        $receipt = $stub->send($user, $message);

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
        $mailer = m::mock('\Illuminate\Mail\Mailer');
        $notifier = m::mock('\Orchestra\Notifier\Mailer')->makePartial();
        $user = m::mock('\Orchestra\Contracts\Notification\Recipient');

        $subject = 'foobar';
        $view    = 'foo.bar';
        $data    = array();
        $message = new Message(compact('subject', 'view', 'data'));

        $user->shouldReceive('getRecipientEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getRecipientName')->once()->andReturn('Administrator');

        $mailer->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with($subject)->andReturnNull()
            ->shouldReceive('failures')->once()->andReturn(array('hello@orchestraplatform.com'));

        $notifier->shouldReceive('push')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer) {
                    $c($mailer);

                    return new Receipt($mailer, false);
                });

        $stub = new OrchestraNotifier($notifier);
        $receipt = $stub->send($user, $message);

        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $receipt);
        $this->assertFalse($receipt->sent());
    }
}
