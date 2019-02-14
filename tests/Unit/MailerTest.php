<?php

namespace Orchestra\Notifier\TestCase\Unit;

use Mockery as m;
use Orchestra\Notifier\Mailer;
use PHPUnit\Framework\TestCase;
use Illuminate\Container\Container;
use Orchestra\Notifier\TransportManager;
use Illuminate\Queue\SerializableClosure;

class MailerTest extends TestCase
{
    /**
     * Teardown the test environment.
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /** @test */
    public function it_can_push_mail()
    {
        $app = new Container();

        $app->instance('orchestra.memory', $memory = m::mock('\Orchestra\Contracts\Memory\Provider'));
        $app->instance('mailer', $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer'));

        $memory->shouldReceive('get')->with('email', [])->andReturn(['driver' => 'mail'])
            ->shouldReceive('get')->with('email.driver', 'mail')->andReturn('mail')
            ->shouldReceive('get')->twice()->with('email.queue', false)->andReturn(false)
            ->shouldReceive('get')->with('email.from')->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('alwaysFrom')->once()->with('hello@orchestraplatform.com', 'Orchestra Platform')
            ->shouldReceive('send')->twice()->with('foo.bar', ['foo' => 'foobar'], '')->andReturn(true);

        $transport = new TransportManager($app);
        $stub = (new Mailer($app, $transport))->attach($app['orchestra.memory']);

        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->push('foo.bar', ['foo' => 'foobar'], ''));
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->push('foo.bar', ['foo' => 'foobar'], ''));
    }

    /** @test */
    public function it_can_push_mail_using_queue()
    {
        $app = new Container();

        $app->instance('orchestra.memory', $memory = m::mock('\Orchestra\Contracts\Memory\Provider'));
        $app->instance('mailer', $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer'));
        $app->instance('queue', $queue = m::mock('\Illuminate\Contracts\Queue\Factory'));

        $memory->shouldReceive('get')->with('email', [])->andReturn(['driver' => 'mail'])
            ->shouldReceive('get')->with('email.driver', 'mail')->andReturn('mail')
            ->shouldReceive('get')->once()->with('email.queue', false)->andReturn(true)
            ->shouldReceive('get')->with('email.from')->andReturn([
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
            ->with('orchestra.mail@handleQueuedMessage', m::type('Array'), m::any())->andReturn(true);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturnNull()
            ->shouldReceive('alwaysFrom')->once()->with('hello@orchestraplatform.com', 'Orchestra Platform');

        $transport = new TransportManager($app);
        $stub = (new Mailer($app, $transport))
                    ->setQueue($app['queue'])
                    ->attach($app['orchestra.memory']);
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->push($with['view'], $with['data'], $with['callback']));
    }

    /** @test */
    public function it_can_send_mail()
    {
        $app = new Container();

        $app->instance('orchestra.memory', $memory = m::mock('\Orchestra\Contracts\Memory\Provider'));
        $app->instance('mailer', $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer'));

        $memory->shouldReceive('get')->with('email', [])->andReturn(['driver' => 'mail'])
            ->shouldReceive('get')->with('email.driver', 'mail')->andReturn('mail')
            ->shouldReceive('get')->with('email.from')->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('alwaysFrom')->once()->with('hello@orchestraplatform.com', 'Orchestra Platform')
            ->shouldReceive('send')->once()->with('foo.bar', ['foo' => 'foobar'], '')->andReturn(true);

        $transport = new TransportManager($app);
        $stub = (new Mailer($app, $transport))->attach($app['orchestra.memory']);
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->send('foo.bar', ['foo' => 'foobar'], ''));
    }

    /** @test */
    public function it_can_send_mail_via_mail()
    {
        $app = new Container();

        $app->instance('orchestra.memory', $memory = m::mock('\Orchestra\Contracts\Memory\Provider'));
        $app->instance('mailer', $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer'));

        $memory->shouldReceive('get')->with('email', [])->andReturn(['driver' => 'mail'])
            ->shouldReceive('get')->with('email.driver', 'mail')->andReturn('mail')
            ->shouldReceive('get')->with('email.from')->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('alwaysFrom')->once()->with('hello@orchestraplatform.com', 'Orchestra Platform')
            ->shouldReceive('send')->once()->with('foo.bar', ['foo' => 'foobar'], '')->andReturn(true);

        $transport = new TransportManager($app);
        $stub = (new Mailer($app, $transport))->attach($memory);
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->send('foo.bar', ['foo' => 'foobar'], ''));
    }

    /** @test */
    public function it_can_send_mail_via_sendmail()
    {
        $app = new Container();

        $app->instance('orchestra.memory', $memory = m::mock('\Orchestra\Contracts\Memory\Provider'));
        $app->instance('mailer', $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer'));

        $memory->shouldReceive('get')->with('email.sendmail', null)->andReturn('/bin/sendmail -t')
            ->shouldReceive('get')->with('email.driver', 'mail')->andReturn('sendmail')
            ->shouldReceive('get')->with('email.from')->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('alwaysFrom')->once()->with('hello@orchestraplatform.com', 'Orchestra Platform')
            ->shouldReceive('send')->once()->with('foo.bar', ['foo' => 'foobar'], '')->andReturn(true);

        $transport = new TransportManager($app);
        $stub = (new Mailer($app, $transport))->attach($memory);
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->send('foo.bar', ['foo' => 'foobar'], ''));
    }

    /** @test */
    public function it_can_send_mail_via_smtp()
    {
        $app = new Container();

        $app->instance('orchestra.memory', $memory = m::mock('\Orchestra\Contracts\Memory\Provider'));
        $app->instance('mailer', $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer'));

        $memory->shouldReceive('get')->with('email', [])->andReturn([
                'host' => 'smtp.mailgun.org',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'hello@orchestraplatform.com',
            ])
            ->shouldReceive('secureGet')->with('email.password', null)->andReturn(123456)
            ->shouldReceive('get')->with('email.driver', 'mail')->andReturn('smtp')
            ->shouldReceive('get')->with('email.from')->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('alwaysFrom')->once()->with('hello@orchestraplatform.com', 'Orchestra Platform')
            ->shouldReceive('send')->once()->with('foo.bar', ['foo' => 'foobar'], '')->andReturn(true);

        $transport = new TransportManager($app);
        $stub = (new Mailer($app, $transport))->attach($memory);
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->send('foo.bar', ['foo' => 'foobar'], ''));
    }

    /** @test */
    public function it_can_send_mail_via_mailgun()
    {
        $app = new Container();

        $app->instance('orchestra.memory', $memory = m::mock('\Orchestra\Contracts\Memory\Provider'));
        $app->instance('mailer', $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer'));

        $memory->shouldReceive('secureGet')->with('email.secret', null)->andReturn('auniquetoken')
            ->shouldReceive('get')->with('email.driver', 'mail')->andReturn('mailgun')
            ->shouldReceive('get')->with('email.domain', null)->andReturn('mailer.mailgun.org')
            ->shouldReceive('get')->with('email.guzzle', [])->andReturn([])
            ->shouldReceive('get')->with('email.from')->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('alwaysFrom')->once()->with('hello@orchestraplatform.com', 'Orchestra Platform')
            ->shouldReceive('send')->once()->with('foo.bar', ['foo' => 'foobar'], '')->andReturn(true);

        $transport = new TransportManager($app);
        $stub = (new Mailer($app, $transport))->attach($memory);
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->send('foo.bar', ['foo' => 'foobar'], ''));
    }

    /** @test */
    public function it_can_send_mail_via_mandrill()
    {
        $app = new Container();

        $app->instance('orchestra.memory', $memory = m::mock('\Orchestra\Contracts\Memory\Provider'));
        $app->instance('mailer', $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer'));

        $memory->shouldReceive('secureGet')->with('email.secret', null)->andReturn('auniquetoken')
            ->shouldReceive('get')->with('email.driver', 'mail')->andReturn('mandrill')
            ->shouldReceive('get')->with('email.guzzle', [])->andReturn([])
            ->shouldReceive('get')->with('email.from')->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('alwaysFrom')->once()->with('hello@orchestraplatform.com', 'Orchestra Platform')
            ->shouldReceive('send')->once()->with('foo.bar', ['foo' => 'foobar'], '')->andReturn(true);

        $transport = new TransportManager($app);
        $stub = (new Mailer($app, $transport))->attach($memory);
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->send('foo.bar', ['foo' => 'foobar'], ''));
    }

    /** @test */
    public function it_can_send_mail_via_log()
    {
        $monolog = m::mock('\Psr\Log\LoggerInterface');

        $app = new Container();

        $app->instance('orchestra.memory', $memory = m::mock('\Orchestra\Contracts\Memory\Provider'));
        $app->instance('mailer', $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer'));
        $app->instance('log', $logger = m::mock('\Illuminate\Log\Writer'));

        $memory->shouldReceive('get')->with('email', [])->andReturn(['driver' => 'log'])
            ->shouldReceive('get')->with('email.driver', 'mail')->andReturn('log')
            ->shouldReceive('get')->with('email.from')->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('alwaysFrom')->once()->with('hello@orchestraplatform.com', 'Orchestra Platform')
            ->shouldReceive('send')->once()->with('foo.bar', ['foo' => 'foobar'], '')->andReturn(true);

        $logger->shouldReceive('getMonolog')->once()->andReturn($monolog);

        $transport = new TransportManager($app);
        $stub = (new Mailer($app, $transport))->attach($memory);
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->send('foo.bar', ['foo' => 'foobar'], ''));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function it_cant_send_mail_via_invalid_transport()
    {
        $app = new Container();

        $app->instance('orchestra.memory', $memory = m::mock('\Orchestra\Contracts\Memory\Provider'));
        $app->instance('mailer', $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer'));

        $memory->shouldReceive('get')->with('email', [])->andReturn(['driver' => 'invalid'])
            ->shouldReceive('get')->with('email.driver', 'mail')->andReturn('invalid')
            ->shouldReceive('get')->with('email.from')->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $mailer->shouldReceive('alwaysFrom')->once()
            ->with('hello@orchestraplatform.com', 'Orchestra Platform')
            ->andReturnNull();

        $transport = new TransportManager($app);
        $stub = (new Mailer($app, $transport))->attach($memory);
        $stub->send('foo.bar', ['foo' => 'foobar'], '');
    }

    /** @test */
    public function it_can_queue_mail()
    {
        $app = new Container();

        $app->instance('orchestra.memory', $memory = m::mock('\Orchestra\Contracts\Memory\Provider'));
        $app->instance('mailer', $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer'));
        $app->instance('queue', $queue = m::mock('\Illuminate\Contracts\Queue\Factory'));

        $memory->shouldReceive('get')->with('email', [])->andReturn(['driver' => 'mail'])
            ->shouldReceive('get')->with('email.driver', 'mail')->andReturn('mail')
            ->shouldReceive('get')->with('email.from')->andReturn([
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
            ->with('orchestra.mail@handleQueuedMessage', m::type('Array'), m::any())->andReturn(true);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturnNull()
            ->shouldReceive('alwaysFrom')->once()->with('hello@orchestraplatform.com', 'Orchestra Platform');

        $transport = new TransportManager($app);
        $stub = (new Mailer($app, $transport))
                    ->setQueue($app['queue'])
                    ->attach($app['orchestra.memory']);
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->queue($with['view'], $with['data'], $with['callback']));
    }

    /** @test */
    public function it_can_queue_mail_using_custom_class_name()
    {
        $app = new Container();

        $app->instance('orchestra.memory', $memory = m::mock('\Orchestra\Contracts\Memory\Provider'));
        $app->instance('mailer', $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer'));
        $app->instance('queue', $queue = m::mock('\Illuminate\Contracts\Queue\Factory'));

        $memory->shouldReceive('get')->with('email', [])->andReturn(['driver' => 'mail'])
            ->shouldReceive('get')->with('email.driver', 'mail')->andReturn('mail')
            ->shouldReceive('get')->with('email.from')->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $with = [
            'view' => 'foo.bar',
            'data' => ['foo' => 'foobar'],
            'callback' => 'FooMailHandler@foo',
        ];

        $queue->shouldReceive('push')->once()
            ->with('orchestra.mail@handleQueuedMessage', $with, '')
            ->andReturn(true);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturnNull()
            ->shouldReceive('alwaysFrom')->once()->with('hello@orchestraplatform.com', 'Orchestra Platform');

        $transport = new TransportManager($app);
        $stub = (new Mailer($app, $transport))
                    ->setQueue($app['queue'])
                    ->attach($app['orchestra.memory']);

        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->queue($with['view'], $with['data'], $with['callback']));
    }

    /**
     * @test
     * @dataProvider queueMessageDataProvdier
     */
    public function it_can_handle_queued_mail($view, $data, $callback)
    {
        $app = new Container();

        $app->instance('orchestra.memory', $memory = m::mock('\Orchestra\Contracts\Memory\Provider'));
        $app->instance('mailer', $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer'));

        $memory->shouldReceive('get')->with('email', [])->andReturn(['driver' => 'mail'])
            ->shouldReceive('get')->with('email.driver', 'mail')->andReturn('mail')
            ->shouldReceive('get')->with('email.from')->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $job = m::mock('\Illuminate\Contracts\Queue\Job');

        $job->shouldReceive('delete')->once()->andReturn(null);
        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('alwaysFrom')->once()->with('hello@orchestraplatform.com', 'Orchestra Platform')
            ->shouldReceive('send')->once()
                ->with($view, $data, m::any())->andReturn(true);

        $transport = new TransportManager($app);
        $stub = (new Mailer($app, $transport))->attach($app['orchestra.memory']);
        $stub->handleQueuedMessage($job, compact('view', 'data', 'callback'));
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function queueMessageDataProvdier()
    {
        SerializableClosure::setSecretKey('1asn3t5DmVASHszn+O8phiLNhjztDOPJFlZ6YIatMLU=');

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
