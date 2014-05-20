<?php namespace Orchestra\Notifier\TestCase;

use Mockery as m;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Fluent;
use Orchestra\Support\Facades\Notifier;

class NotifiableTraitTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $app = new Container;

        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);
    }
    public function tearDown()
    {
        m::close();
    }

    public function testSendNotificationTraitMethod()
    {
        $user = m::mock('\Orchestra\Notifier\RecipientInterface', '\Illuminate\Support\Contracts\ArrayableInterface');
        $notifier = m::mock('\Orchestra\Notifier\NotifierInterface');
        $stub = new Notifiable;

        $user->shouldReceive('toArray')->once()->andReturn([
            'id' => 2,
            'fullname' => 'Administrator',
        ]);

        $notifier->shouldReceive('send')->twice()
            ->with($user, m::type('\Illuminate\Support\Fluent'))->andReturn(true);

        Notifier::swap($notifier);

        $this->assertTrue($stub->notify($user));
        $this->assertTrue($stub->notifyFluent($user));
    }
}

class Notifiable
{
    use \Orchestra\Notifier\NotifiableTrait;

    public function notify($user)
    {
        return $this->sendNotification($user, 'foo', 'email.foo', []);
    }

    public function notifyFluent($user)
    {
        return $this->sendNotification($user, new Fluent(['subject' => 'foo', 'view' => 'email.foo', 'data' => []]));
    }
}
