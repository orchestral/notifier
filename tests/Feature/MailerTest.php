<?php

namespace Orchestra\Notifier\Tests\Feature;

use Illuminate\Container\Container;
use Illuminate\Queue\SerializableClosure;
use Mockery as m;
use Psr\Log\LoggerInterface;

class MailerTest extends TestCase
{
    /** @test */
    public function it_can_push_mail()
    {
        $this->app->instance('orchestra.platform.memory', $memory = m::mock('Orchestra\Contracts\Memory\Provider'));
        $this->app->instance('mail.manager', $manager = m::mock('Illuminate\Mail\MailManager'));
        $this->app->instance('mailer', $mailer = m::mock('Illuminate\Contracts\Mail\Mailer'));

        $manager->shouldReceive('driver')->andReturn($mailer);

        $memory->shouldReceive('get')->with('email.driver', 'mail')->twice()->andReturn('mail')
            ->shouldReceive('get')->twice()->with('email.queue', false)->twice()->andReturn(false)
            ->shouldReceive('get')->with('email.from')->once()->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('send')->twice()->with('foo.bar', ['foo' => 'foobar'], '')->andReturn(true);

        $stub = $this->app['orchestra.postal'];
        $stub->configureIlluminateMailer($this->app['mail.manager']);

        $this->assertInstanceOf('Orchestra\Notifier\Receipt', $stub->push('foo.bar', ['foo' => 'foobar'], ''));
        $this->assertInstanceOf('Orchestra\Notifier\Receipt', $stub->push('foo.bar', ['foo' => 'foobar'], ''));
    }

    /** @test */
    public function it_can_push_mail_using_queue()
    {
        $this->app->instance('orchestra.platform.memory', $memory = m::mock('Orchestra\Contracts\Memory\Provider'));
        $this->app->instance('mail.manager', $manager = m::mock('Illuminate\Mail\MailManager'));
        $this->app->instance('mailer', $mailer = m::mock('Illuminate\Contracts\Mail\Mailer'));
        $this->app->instance('queue', $queue = m::mock('Illuminate\Contracts\Queue\Factory'));

        $manager->shouldReceive('driver')->andReturn($mailer);

        $memory->shouldReceive('get')->with('email.driver', 'mail')->twice()->andReturn('mail')
            ->shouldReceive('get')->once()->with('email.queue', false)->once()->andReturn(true)
            ->shouldReceive('get')->with('email.from')->once()->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $with = [
            'view' => 'foo.bar',
            'data' => ['foo' => 'foobar'],
            'callback' => function () {
                //
            },
        ];

        $queue->shouldReceive('push')->once()
            ->with('orchestra.postal@handleQueuedMessage', m::type('Array'), m::any())->andReturn(true);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturnNull();

        $stub = $this->app['orchestra.postal'];
        $stub->configureIlluminateMailer($this->app['mail.manager']);

        $this->assertInstanceOf('Orchestra\Notifier\Receipt', $stub->push($with['view'], $with['data'], $with['callback']));
    }

    /** @test */
    public function it_can_send_mail()
    {
        $this->app->instance('orchestra.platform.memory', $memory = m::mock('Orchestra\Contracts\Memory\Provider'));
        $this->app->instance('mail.manager', $manager = m::mock('Illuminate\Mail\MailManager'));
        $this->app->instance('mailer', $mailer = m::mock('Illuminate\Contracts\Mail\Mailer'));

        $manager->shouldReceive('driver')->andReturn($mailer);

        $memory->shouldReceive('get')->with('email.driver', 'mail')->twice()->andReturn('mail')
            ->shouldReceive('get')->with('email.from')->once()->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('send')->once()->with('foo.bar', ['foo' => 'foobar'], '')->andReturn(true);

        $stub = $this->app['orchestra.postal'];
        $stub->configureIlluminateMailer($this->app['mail.manager']);

        $this->assertInstanceOf('Orchestra\Notifier\Receipt', $stub->send('foo.bar', ['foo' => 'foobar'], ''));
    }

    /** @test */
    public function it_can_send_mail_via_mail()
    {
        $this->app->instance('orchestra.platform.memory', $memory = m::mock('Orchestra\Contracts\Memory\Provider'));
        $this->app->instance('mail.manager', $manager = m::mock('Illuminate\Mail\MailManager'));
        $this->app->instance('mailer', $mailer = m::mock('Illuminate\Contracts\Mail\Mailer'));

        $manager->shouldReceive('driver')->with('mail')->andReturn($mailer);

        $memory->shouldReceive('get')->with('email.driver', 'mail')->twice()->andReturn('mail')
            ->shouldReceive('get')->with('email.from')->once()->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('send')->once()->with('foo.bar', ['foo' => 'foobar'], '')->andReturn(true);

        $stub = $this->app['orchestra.postal'];
        $stub->configureIlluminateMailer($this->app['mail.manager']);

        $this->assertInstanceOf('Orchestra\Notifier\Receipt', $stub->send('foo.bar', ['foo' => 'foobar'], ''));
    }

    /** @test */
    public function it_can_send_mail_via_sendmail()
    {
        $this->app->instance('orchestra.platform.memory', $memory = m::mock('Orchestra\Contracts\Memory\Provider'));
        $this->app->instance('mail.manager', $manager = m::mock('Illuminate\Mail\MailManager'));
        $this->app->instance('mailer', $mailer = m::mock('Illuminate\Contracts\Mail\Mailer'));

        $manager->shouldReceive('driver')->with('sendmail')->andReturn($mailer);

        $memory->shouldReceive('get')->with('email.sendmail', null)->once()->andReturn('/bin/sendmail -t')
            ->shouldReceive('get')->with('email.driver', 'mail')->twice()->andReturn('sendmail')
            ->shouldReceive('get')->with('email.from')->once()->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('send')->once()->with('foo.bar', ['foo' => 'foobar'], '')->andReturn(true);

        $stub = $this->app['orchestra.postal'];
        $stub->configureIlluminateMailer($this->app['mail.manager']);

        $this->assertInstanceOf('Orchestra\Notifier\Receipt', $stub->send('foo.bar', ['foo' => 'foobar'], ''));
    }

    /** @test */
    public function it_can_send_mail_via_smtp()
    {
        $this->app->instance('orchestra.platform.memory', $memory = m::mock('Orchestra\Contracts\Memory\Provider'));
        $this->app->instance('mail.manager', $manager = m::mock('Illuminate\Mail\MailManager'));
        $this->app->instance('mailer', $mailer = m::mock('Illuminate\Contracts\Mail\Mailer'));

        $manager->shouldReceive('driver')->with('smtp')->andReturn($mailer);

        $memory->shouldReceive('get')->with('email', [])->once()->andReturn([
                'host' => 'smtp.mailgun.org',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'hello@orchestraplatform.com',
            ])
            ->shouldReceive('secureGet')->with('email.password', null)->once()->andReturn(123456)
            ->shouldReceive('get')->with('email.driver', 'mail')->twice()->andReturn('smtp')
            ->shouldReceive('get')->with('email.from')->once()->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('send')->once()->with('foo.bar', ['foo' => 'foobar'], '')->andReturn(true);

        $stub = $this->app['orchestra.postal'];
        $stub->configureIlluminateMailer($this->app['mail.manager']);

        $this->assertInstanceOf('Orchestra\Notifier\Receipt', $stub->send('foo.bar', ['foo' => 'foobar'], ''));
    }

    /** @test */
    public function it_can_send_mail_via_mailgun()
    {
        $this->app->instance('orchestra.platform.memory', $memory = m::mock('Orchestra\Contracts\Memory\Provider'));
        $this->app->instance('mail.manager', $manager = m::mock('Illuminate\Mail\MailManager'));
        $this->app->instance('mailer', $mailer = m::mock('Illuminate\Contracts\Mail\Mailer'));

        $manager->shouldReceive('driver')->with('mailgun')->andReturn($mailer);

        $memory->shouldReceive('secureGet')->with('email.secret', null)->once()->andReturn('auniquetoken')
            ->shouldReceive('get')->with('email.driver', 'mail')->twice()->andReturn('mailgun')
            ->shouldReceive('get')->with('email.domain', null)->once()->andReturn('mailer.mailgun.org')
            ->shouldReceive('get')->with('email.guzzle', [])->once()->andReturn([])
            ->shouldReceive('get')->with('email.from')->once()->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('send')->once()->with('foo.bar', ['foo' => 'foobar'], '')->andReturn(true);

        $stub = $this->app['orchestra.postal'];
        $stub->configureIlluminateMailer($this->app['mail.manager']);

        $this->assertInstanceOf('Orchestra\Notifier\Receipt', $stub->send('foo.bar', ['foo' => 'foobar'], ''));
    }

    /** @test */
    public function it_can_send_mail_via_log()
    {
        $this->app->instance('orchestra.platform.memory', $memory = m::mock('Orchestra\Contracts\Memory\Provider'));
        $this->app->instance('mail.manager', $manager = m::mock('Illuminate\Mail\MailManager'));
        $this->app->instance('mailer', $mailer = m::mock('Illuminate\Contracts\Mail\Mailer'));
        $this->app->instance(LoggerInterface::class, $logger = m::mock(LoggerInterface::class));

        $manager->shouldReceive('driver')->with('log')->andReturn($mailer);

        $memory->shouldReceive('get')->with('email.driver', 'mail')->twice()->andReturn('log')
            ->shouldReceive('get')->with('email.from')->once()->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('send')->once()->with('foo.bar', ['foo' => 'foobar'], '')->andReturn(true);

        $stub = $this->app['orchestra.postal'];
        $stub->configureIlluminateMailer($this->app['mail.manager']);

        $this->assertInstanceOf('Orchestra\Notifier\Receipt', $stub->send('foo.bar', ['foo' => 'foobar'], ''));
    }

    /** @test */
    public function it_cant_send_mail_via_invalid_transport()
    {
        $this->expectException('InvalidArgumentException');

        $this->app->instance('orchestra.platform.memory', $memory = m::mock('Orchestra\Contracts\Memory\Provider'));

        $memory->shouldReceive('get')->with('email.driver', 'mail')->once()->andReturn('invalid')
            ->shouldReceive('get')->with('email.from')->once()->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $stub = $this->app['orchestra.postal'];
        $stub->configureIlluminateMailer($this->app['mail.manager']);

        $stub->send('foo.bar', ['foo' => 'foobar'], '');
    }

    /** @test */
    public function it_can_queue_mail()
    {
        $app = new Container();

        $this->app->instance('orchestra.platform.memory', $memory = m::mock('Orchestra\Contracts\Memory\Provider'));
        $this->app->instance('mail.manager', $manager = m::mock('Illuminate\Mail\MailManager'));
        $this->app->instance('mailer', $mailer = m::mock('Illuminate\Contracts\Mail\Mailer'));
        $this->app->instance('queue', $queue = m::mock('Illuminate\Contracts\Queue\Factory'));

        $manager->shouldReceive('driver')->with('mail')->andReturn($mailer);

        $memory->shouldReceive('get')->with('email.driver', 'mail')->twice()->andReturn('mail')
            ->shouldReceive('get')->with('email.from')->once()->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $with = [
            'view' => 'foo.bar',
            'data' => ['foo' => 'foobar'],
            'callback' => function () {
                //
            },
        ];

        $queue->shouldReceive('push')->once()
            ->with('orchestra.postal@handleQueuedMessage', m::type('Array'), m::any())->andReturn(true);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturnNull();

        $stub = $this->app['orchestra.postal'];
        $stub->configureIlluminateMailer($this->app['mail.manager']);

        $this->assertInstanceOf('Orchestra\Notifier\Receipt', $stub->queue($with['view'], $with['data'], $with['callback']));
    }

    /** @test */
    public function it_can_queue_mail_using_custom_class_name()
    {
        $this->app->instance('orchestra.platform.memory', $memory = m::mock('Orchestra\Contracts\Memory\Provider'));
        $this->app->instance('mail.manager', $manager = m::mock('Illuminate\Mail\MailManager'));
        $this->app->instance('mailer', $mailer = m::mock('Illuminate\Contracts\Mail\Mailer'));
        $this->app->instance('queue', $queue = m::mock('Illuminate\Contracts\Queue\Factory'));

        $manager->shouldReceive('driver')->with('mail')->andReturn($mailer);

        $memory->shouldReceive('get')->with('email.driver', 'mail')->twice()->andReturn('mail')
            ->shouldReceive('get')->with('email.from')->once()->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $with = [
            'view' => 'foo.bar',
            'data' => ['foo' => 'foobar'],
            'callback' => 'FooMailHandler@foo',
        ];

        $queue->shouldReceive('push')->once()
            ->with('orchestra.postal@handleQueuedMessage', $with, '')
            ->andReturn(true);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturnNull();

        $stub = $this->app['orchestra.postal'];
        $stub->configureIlluminateMailer($this->app['mail.manager']);

        $this->assertInstanceOf('Orchestra\Notifier\Receipt', $stub->queue($with['view'], $with['data'], $with['callback']));
    }

    /**
     * @test
     * @dataProvider queueMessageDataProvdier
     */
    public function it_can_handle_queued_mail($view, $data, $callback)
    {
        $this->app->instance('orchestra.platform.memory', $memory = m::mock('Orchestra\Contracts\Memory\Provider'));
        $this->app->instance('mail.manager', $manager = m::mock('Illuminate\Mail\MailManager'));
        $this->app->instance('mailer', $mailer = m::mock('Illuminate\Contracts\Mail\Mailer'));

        $manager->shouldReceive('driver')->with('mail')->andReturn($mailer);

        $memory->shouldReceive('get')->with('email.driver', 'mail')->twice()->andReturn('mail')
            ->shouldReceive('get')->with('email.from')->once()->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $job = m::mock('Illuminate\Contracts\Queue\Job');

        $job->shouldReceive('delete')->once()->andReturn(null);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('send')->once()
                ->with($view, $data, m::any())->andReturn(true);

        $stub = $this->app['orchestra.postal'];
        $stub->configureIlluminateMailer($this->app['mail.manager']);

        $stub->handleQueuedMessage($job, compact('view', 'data', 'callback'));
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function queueMessageDataProvdier()
    {
        SerializableClosure::setSecretKey('AckfSECXIvnK5r28GVIWUAxmbBSjTsmF');

        $callback = new SerializableClosure(function () {
            //
        });

        return [
            [
                'view' => 'foo.bar',
                'data' => ['foo' => 'foobar'],
                'callback' => serialize($callback),
            ],
            [
                'view' => 'foo.bar',
                'data' => ['foo' => 'foobar'],
                'callback' => 'hello world',
            ],
        ];
    }
}
