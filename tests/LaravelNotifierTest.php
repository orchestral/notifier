<?php namespace Orchestra\Notifier\TestCase;

use Mockery as m;
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
        $mailer = m::mock('\Illuminate\Mail\Mailer');
        $user = m::mock('\Illuminate\Auth\Reminders\RemindableInterface');
        $subject = 'foobar';
        $view = 'foo.bar';
        $data = array();

        $user->shouldReceive('getReminderEmail')->once()->andReturn('hello@orchestraplatform.com');

        $mailer->shouldReceive('send')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer) {
                    $c($mailer);

                    return array('hello@orchestraplatform.com');
                })
            ->shouldReceive('to')->once()->with('hello@orchestraplatform.com')->andReturnNull()
            ->shouldReceive('subject')->once()->with($subject)->andReturnNull();

        $stub = new LaravelNotifier($mailer);

        $this->assertTrue($stub->send($user, $subject, $view, $data));
    }

    /**
     * Test Orchestra\Notifier\LaravelNotifier::send() method failed.
     *
     * @test
     */
    public function testSendMethodFailed()
    {
        $mailer = m::mock('\Illuminate\Mail\Mailer');
        $user = m::mock('\Illuminate\Auth\Reminders\RemindableInterface');
        $subject = 'foobar';
        $view = 'foo.bar';
        $data = array();

        $user->shouldReceive('getReminderEmail')->once()->andReturn('hello@orchestraplatform.com');

        $mailer->shouldReceive('send')->once()->with($view, $data, m::type('Closure'))
                ->andReturnUsing(function ($v, $d, $c) use ($mailer) {
                    $c($mailer);

                    return array();
                })
            ->shouldReceive('to')->once()->with('hello@orchestraplatform.com')->andReturnNull()
            ->shouldReceive('subject')->once()->with($subject)->andReturnNull();

        $stub = new LaravelNotifier($mailer);

        $this->assertFalse($stub->send($user, $subject, $view, $data));
    }
}
