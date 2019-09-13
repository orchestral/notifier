<?php

namespace Orchestra\Notifier\Tests\Unit;

use Orchestra\Notifier\GenericRecipient;
use PHPUnit\Framework\TestCase;

class GenericRecipientTest extends TestCase
{
    /** @test */
    public function it_can_initiate_recipient()
    {
        $email = 'admin@orchestraplatform.com';
        $name = 'Administrator';
        $stub = new GenericRecipient($email, $name);

        $this->assertInstanceOf('Orchestra\Contracts\Notification\Recipient', $stub);
        $this->assertEquals($email, $stub->getRecipientEmail());
        $this->assertEquals($name, $stub->getRecipientName());
    }
}
