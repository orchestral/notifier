<?php namespace Orchestra\Notifier\TestCase;

use Mockery as m;
use Orchestra\Notifier\Mailer;
use Illuminate\Container\Container;
use SuperClosure\SerializableClosure;
use Orchestra\Notifier\TransportManager;

class MailerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * Test Orchestra\Notifier\Mailer::push() method uses Mail::send().
     *
     * @test
     */
    public function testPushMethodUsesSend()
    {
        $app = new Container();

        $app->instance('encrypter', m::mock('\Illuminate\Contracts\Encryption\Encrypter'));
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
        $stub = with(new Mailer($app, $transport))->attach($app['orchestra.memory']);

        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->push('foo.bar', ['foo' => 'foobar'], ''));
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->push('foo.bar', ['foo' => 'foobar'], ''));
    }

    /**
     * Test Orchestra\Notifier\Mailer::push() method uses Mail::queue().
     *
     * @test
     */
    public function testPushMethodUsesQueue()
    {
        $app = new Container();

        $app->instance('encrypter', m::mock('\Illuminate\Contracts\Encryption\Encrypter'));
        $app->instance('orchestra.memory', $memory = m::mock('\Orchestra\Contracts\Memory\Provider'));
        $app->instance('mailer', $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer'));
        $app->instance('queue', $queue = m::mock('QueueListener'));

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

            },
        ];

        $queue->shouldReceive('push')->once()
            ->with('orchestra.mail@handleQueuedMessage', m::type('Array'), m::any())->andReturn(true);

        $transport = new TransportManager($app);
        $stub = with(new Mailer($app, $transport))->attach($app['orchestra.memory']);
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->push($with['view'], $with['data'], $with['callback']));
    }

    /**
     * Test Orchestra\Notifier\Mailer::send() method.
     *
     * @test
     */
    public function testSendMethod()
    {
        $app = new Container();

        $app->instance('encrypter', m::mock('\Illuminate\Contracts\Encryption\Encrypter'));
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
        $stub = with(new Mailer($app, $transport))->attach($app['orchestra.memory']);
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->send('foo.bar', ['foo' => 'foobar'], ''));
    }

    /**
     * Test Orchestra\Notifier\Mailer::send() method using mail.
     *
     * @test
     */
    public function testSendMethodViaMail()
    {
        $app = new Container();

        $app->instance('encrypter', m::mock('\Illuminate\Contracts\Encryption\Encrypter'));
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
        $stub = with(new Mailer($app, $transport))->attach($memory);
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->send('foo.bar', ['foo' => 'foobar'], ''));
    }

    /**
     * Test Orchestra\Notifier\Mailer::send() method using sendmail.
     *
     * @test
     */
    public function testSendMethodViaSendMail()
    {
        $app = new Container();

        $app->instance('encrypter', m::mock('\Illuminate\Contracts\Encryption\Encrypter'));
        $app->instance('orchestra.memory', $memory = m::mock('\Orchestra\Contracts\Memory\Provider'));
        $app->instance('mailer', $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer'));

        $memory->shouldReceive('get')->with('email', [])->andReturn([
                'driver' => 'sendmail',
                'sendmail' => '/bin/sendmail -t',
            ])
            ->shouldReceive('get')->with('email.driver', 'mail')->andReturn('sendmail')
            ->shouldReceive('get')->with('email.from')->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('alwaysFrom')->once()->with('hello@orchestraplatform.com', 'Orchestra Platform')
            ->shouldReceive('send')->once()->with('foo.bar', ['foo' => 'foobar'], '')->andReturn(true);

        $transport = new TransportManager($app);
        $stub = with(new Mailer($app, $transport))->attach($memory);
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->send('foo.bar', ['foo' => 'foobar'], ''));
    }

    /**
     * Test Orchestra\Notifier\Mailer::send() method using smtp.
     *
     * @test
     */
    public function testSendMethodViaSmtp()
    {
        $app = new Container();

        $app->instance('encrypter', $encrypter = m::mock('\Illuminate\Contracts\Encryption\Encrypter'));
        $app->instance('orchestra.memory', $memory = m::mock('\Orchestra\Contracts\Memory\Provider'));
        $app->instance('mailer', $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer'));

        $memory->shouldReceive('get')->with('email', [])->andReturn([
                'driver' => 'smtp',
                'host' => 'smtp.mailgun.org',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'hello@orchestraplatform.com',
                'password' => 123456,
            ])
            ->shouldReceive('get')->with('email.driver', 'mail')->andReturn('smtp')
            ->shouldReceive('get')->with('email.from')->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('alwaysFrom')->once()->with('hello@orchestraplatform.com', 'Orchestra Platform')
            ->shouldReceive('send')->once()->with('foo.bar', ['foo' => 'foobar'], '')->andReturn(true);

        $encrypter->shouldReceive('decrypt')->once()->with(123456)->andReturn(123456);

        $transport = new TransportManager($app);
        $stub = with(new Mailer($app, $transport))->attach($memory);
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->send('foo.bar', ['foo' => 'foobar'], ''));
    }

    /**
     * Test Orchestra\Notifier\Mailer::send() method using mailgun.
     *
     * @test
     */
    public function testSendMethodViaMailgun()
    {
        $app = new Container();

        $app->instance('encrypter', $encrypter = m::mock('\Illuminate\Contracts\Encryption\Encrypter'));
        $app->instance('orchestra.memory', $memory = m::mock('\Orchestra\Contracts\Memory\Provider'));
        $app->instance('mailer', $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer'));

        $memory->shouldReceive('get')->with('email', [])->andReturn([
                'driver' => 'mailgun',
                'secret' => 'auniquetoken',
                'domain' => 'mailer.mailgun.org',
            ])
            ->shouldReceive('get')->with('email.driver', 'mail')->andReturn('mailgun')
            ->shouldReceive('get')->with('email.from')->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('alwaysFrom')->once()->with('hello@orchestraplatform.com', 'Orchestra Platform')
            ->shouldReceive('send')->once()->with('foo.bar', ['foo' => 'foobar'], '')->andReturn(true);

        $encrypter->shouldReceive('decrypt')->once()->with('auniquetoken')->andReturn('auniquetoken');

        $transport = new TransportManager($app);
        $stub = with(new Mailer($app, $transport))->attach($memory);
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->send('foo.bar', ['foo' => 'foobar'], ''));
    }

    /**
     * Test Orchestra\Notifier\Mailer::send() method using mandrill.
     *
     * @test
     */
    public function testSendMethodViaMandrill()
    {
        $app = new Container();

        $app->instance('encrypter', $encrypter = m::mock('\Illuminate\Contracts\Encryption\Encrypter'));
        $app->instance('orchestra.memory', $memory = m::mock('\Orchestra\Contracts\Memory\Provider'));
        $app->instance('mailer', $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer'));

        $memory->shouldReceive('get')->with('email', [])->andReturn([
                'driver' => 'mandrill',
                'secret' => 'auniquetoken',
            ])
            ->shouldReceive('get')->with('email.driver', 'mail')->andReturn('mandrill')
            ->shouldReceive('get')->with('email.from')->andReturn([
                'address' => 'hello@orchestraplatform.com',
                'name' => 'Orchestra Platform',
            ]);

        $mailer->shouldReceive('setSwiftMailer')->once()->andReturn(null)
            ->shouldReceive('alwaysFrom')->once()->with('hello@orchestraplatform.com', 'Orchestra Platform')
            ->shouldReceive('send')->once()->with('foo.bar', ['foo' => 'foobar'], '')->andReturn(true);

        $encrypter->shouldReceive('decrypt')->once()->with('auniquetoken')->andReturn('auniquetoken');

        $transport = new TransportManager($app);
        $stub = with(new Mailer($app, $transport))->attach($memory);
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->send('foo.bar', ['foo' => 'foobar'], ''));
    }

    /**
     * Test Orchestra\Notifier\Mailer::send() method using log.
     *
     * @test
     */
    public function testSendMethodViaLog()
    {
        $monolog = m::mock('\Psr\Log\LoggerInterface');

        $app = new Container();

        $app->instance('encrypter', m::mock('\Illuminate\Contracts\Encryption\Encrypter'));
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
        $stub = with(new Mailer($app, $transport))->attach($memory);
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->send('foo.bar', ['foo' => 'foobar'], ''));
    }

    /**
     * Test Orchestra\Notifier\Mailer::send() method using invalid driver
     * throws exception.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSendMethodViaInvalidDriverThrowsException()
    {
        $app = new Container();

        $app->instance('encrypter', m::mock('\Illuminate\Contracts\Encryption\Encrypter'));
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
        $stub = with(new Mailer($app, $transport))->attach($memory);
        $stub->send('foo.bar', ['foo' => 'foobar'], '');
    }

    /**
     * Test Orchestra\Notifier\Mailer::queue() method.
     *
     * @test
     */
    public function testQueueMethod()
    {
        $app = new Container();

        $app->instance('encrypter', m::mock('\Illuminate\Contracts\Encryption\Encrypter'));
        $app->instance('orchestra.memory', $memory = m::mock('\Orchestra\Contracts\Memory\Provider'));
        $app->instance('mailer', $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer'));
        $app->instance('queue', $queue = m::mock('QueueListener'));

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

            },
        ];

        $queue->shouldReceive('push')->once()
            ->with('orchestra.mail@handleQueuedMessage', m::type('Array'), m::any())->andReturn(true);

        $transport = new TransportManager($app);
        $stub = with(new Mailer($app, $transport))->attach($app['orchestra.memory']);
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->queue($with['view'], $with['data'], $with['callback']));
    }

    /**
     * Test Orchestra\Notifier\Mailer::queue() method when a class name
     * is given.
     *
     * @test
     */
    public function testQueueMethodWhenClassNameIsGiven()
    {$app = new Container();

        $app->instance('encrypter', m::mock('\Illuminate\Contracts\Encryption\Encrypter'));
        $app->instance('orchestra.memory', $memory = m::mock('\Orchestra\Contracts\Memory\Provider'));
        $app->instance('mailer', $mailer = m::mock('\Illuminate\Contracts\Mail\Mailer'));
        $app->instance('queue', $queue = m::mock('QueueListener'));

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

        $transport = new TransportManager($app);
        $stub = with(new Mailer($app, $transport))->attach($app['orchestra.memory']);
        $this->assertInstanceOf('\Orchestra\Notifier\Receipt', $stub->queue($with['view'], $with['data'], $with['callback']));
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function queueMessageDataProvdier()
    {
        $callback = new SerializableClosure(function () {

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
                'callback' => "hello world",
            ],
        ];
    }

    /**
     * Test Orchestra\Notifier\Mailer::handleQueuedMessage() method.
     *
     * @test
     * @dataProvider queueMessageDataProvdier
     */
    public function testHandleQueuedMessageMethod($view, $data, $callback)
    {
        $app = new Container();

        $app->instance('encrypter', m::mock('\Illuminate\Contracts\Encryption\Encrypter'));
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
        $stub = with(new Mailer($app, $transport))->attach($app['orchestra.memory']);
        $stub->handleQueuedMessage($job, compact('view', 'data', 'callback'));
    }
}
