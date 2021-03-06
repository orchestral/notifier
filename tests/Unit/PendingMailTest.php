<?php

namespace Orchestra\Notifier\Tests\Feature;

use Mockery as m;
use Orchestra\Notifier\PendingMail;
use PHPUnit\Framework\TestCase;

class PendingMailTest extends TestCase
{
    /**
     * Teardown the test environment.
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /** @test */
    public function it_translate_recipient_instance_to_pending_mail()
    {
        $recipient = m::mock('Orchestra\Contracts\Notification\Recipient', [
            'getRecipientEmail' => 'hello@orchestraplatform.com',
            'getRecipientName' => 'Administrator',
        ]);

        $mailer = m::mock('Orchestra\Notifier\Postal');
        $mailable = m::mock('Illuminate\Contracts\Mail\Mailable');

        $mailable->shouldReceive('to')->once()->with([
            ['email' => 'hello@orchestraplatform.com', 'name' => 'Administrator'],
        ])->andReturnSelf()
        ->shouldReceive('cc')->with([])->andReturnSelf()
        ->shouldReceive('bcc')->with([])->andReturnSelf()
        ->shouldReceive('locale')->with([])->andReturnSelf();

        $mailer->shouldReceive('send')->once()->with($mailable);

        $stub = new PendingMail($mailer);
        $stub->to($recipient);
        $stub->send($mailable);
    }
}
