<?php

namespace Orchestra\Notifier\TestCase;

use Mockery as m;
use Orchestra\Notifier\Message;
use PHPUnit\Framework\TestCase;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Orchestra\Support\Facades\Notifier;

class NotifiableTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        $app = new Container();

        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);
    }

    /**
     * Teardown the test environment.
     */
    protected function tearDown()
    {
        m::close();
    }

    /**
     * Test \Orchestra\Notifier\NotifiableTrait::sendNotification()
     * method.
     *
     * @test
     */
    public function testSendNotificationTraitMethod()
    {
        $user = m::mock('\Orchestra\Contracts\Notification\Recipient', '\Illuminate\Contracts\Support\Arrayable');
        $notifier = m::mock('\Orchestra\Contracts\Notification\Notification');
        $stub = new Notifiable();

        $user->shouldReceive('toArray')->twice()->andReturn([
            'id' => 2,
            'fullname' => 'Administrator',
        ]);

        $notifier->shouldReceive('send')->twice()
            ->with($user, m::type('\Orchestra\Contracts\Notification\Message'))->andReturn(true);

        Notifier::swap($notifier);

        $this->assertTrue($stub->notify($user));
        $this->assertTrue($stub->notifyFluent($user));
    }
}

class Notifiable
{
    use \Orchestra\Notifier\Notifiable;

    public function notify($user)
    {
        return $this->sendNotification($user, 'foo', 'email.foo', []);
    }

    public function notifyFluent($user)
    {
        return $this->sendNotification($user, new Message(['view' => 'email.foo', 'data' => [], 'subject' => 'foo']));
    }
}
