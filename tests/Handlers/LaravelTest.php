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
        $mailer = m::mock('\Illuminate\Mail\Mailer')->makePartial();
        $user = m::mock('\Orchestra\Contracts\Notification\Recipient');

        $subject = 'foobar';
        $view    = 'foo.bar';
        $data    = array();
        $message = new Message(compact('subject', 'view', 'data'));

        $user->shouldReceive('getRecipientEmail')->once()->andReturn('hello@orchestraplatform.com')
            ->shouldReceive('getRecipientName')->once()->andReturn('Administrator');

        $mailer->shouldReceive('send')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer) {
                    $c($mailer);

                    return array('hello@orchestraplatform.com');
                })
            ->shouldReceive('to')->once()->with('hello@orchestraplatform.com', 'Administrator')->andReturnNull()
            ->shouldReceive('subject')->once()->with($subject)->andReturnNull();

        $stub = new Laravel($mailer);
        $receipt = $stub->send($user, $message);

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
        $user = m::mock('\Orchestra\Contracts\Notification\Recipient');

        $subject = 'foobar';
        $view    = 'foo.bar';
        $data    = array();
        $message = new Message(compact('subject', 'view', 'data'));

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

        $stub = new Laravel($mailer);
        $receipt = $stub->send($user, $message);

        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $receipt);
        $this->assertFalse($receipt->sent());
    }
}
