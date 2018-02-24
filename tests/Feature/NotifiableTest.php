<?php

namespace Orchestra\Notifier\TestCase\Feature;

use Mockery as m;
use Orchestra\Notifier\Message;
use Orchestra\Notifier\Notifiable;
use Orchestra\Support\Facades\Notifier;

class NotifiableTest extends TestCase
{
    /** @test */
    public function it_can_send_email_using_notifiable_trait()
    {
        $user = m::mock('\Orchestra\Contracts\Notification\Recipient', '\Illuminate\Contracts\Support\Arrayable');
        $notifier = m::mock('\Orchestra\Contracts\Notification\Notification');
        $receipt = m::mock('\Orchestra\Contracts\Notification\Receipt');

        $stub = new class() {
            use Notifiable;

            public function notify($user)
            {
                return $this->sendNotification(
                    $user, 'foo', 'email.foo', []
                );
            }

            public function notifyFluent($user)
            {
                return $this->sendNotification(
                    $user, new Message(['view' => 'email.foo', 'data' => [], 'subject' => 'foo'])
                );
            }
        };

        $user->shouldReceive('toArray')->twice()->andReturn([
            'id' => 2,
            'fullname' => 'Administrator',
        ]);

        $notifier->shouldReceive('send')->twice()
            ->with($user, m::type('\Orchestra\Contracts\Notification\Message'))->andReturn($receipt);

        $receipt->shouldReceive('sent')->andReturn(true);

        Notifier::swap($notifier);

        $this->assertSame($receipt, $stub->notify($user));
        $this->assertSame($receipt, $stub->notifyFluent($user));
    }
}
