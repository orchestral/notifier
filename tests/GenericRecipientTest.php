<?php

namespace Orchestra\Notifier\TestCase;

use Orchestra\Notifier\GenericRecipient;

class GenericRecipientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Orchestra\Notifier\GenericRecipient.
     *
     * @test
     */
    public function testGenericRecipient()
    {
        $email = 'admin@orchestraplatform.com';
        $name = 'Administrator';
        $stub = new GenericRecipient($email, $name);

        $this->assertInstanceOf('\Orchestra\Contracts\Notification\Recipient', $stub);
        $this->assertEquals($email, $stub->getRecipientEmail());
        $this->assertEquals($name, $stub->getRecipientName());
    }
}
