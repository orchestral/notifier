<?php

namespace Orchestra\Notifier\TestCase;

use Orchestra\Notifier\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    /**
     * Test \Orchestra\Notifier\Message instance signature.
     *
     * @test
     */
    public function testInstanceSignature()
    {
        $stub = new Message();

        $this->assertInstanceOf('\Illuminate\Support\Fluent', $stub);
    }

    public function testCreateFactoryMethod()
    {
        $view = 'foo.bar';
        $data = ['data' => 'foo'];
        $subject = "Hello world";
        $stub = Message::create($view, $data, $subject);

        $this->assertEquals($view, $stub->view);
        $this->assertEquals($data, $stub->data);
        $this->assertEquals($subject, $stub->subject);
        $this->assertEquals($view, $stub->getView());
        $this->assertEquals($data, $stub->getData());
        $this->assertEquals($subject, $stub->getSubject());
    }
}
