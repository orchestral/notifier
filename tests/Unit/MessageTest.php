<?php

namespace Orchestra\Notifier\TestCase\Unit;

use Orchestra\Notifier\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    /** @test */
    public function it_has_proper_signature()
    {
        $stub = new Message();

        $this->assertInstanceOf('Illuminate\Support\Fluent', $stub);
    }

    /** @test */
    public function it_can_be_initiated()
    {
        $view = 'foo.bar';
        $data = ['data' => 'foo'];
        $subject = 'Hello world';
        $stub = Message::create($view, $data, $subject);

        $this->assertEquals($view, $stub->view);
        $this->assertEquals($data, $stub->data);
        $this->assertEquals($subject, $stub->subject);
        $this->assertEquals($view, $stub->getView());
        $this->assertEquals($data, $stub->getData());
        $this->assertEquals($subject, $stub->getSubject());
    }
}
