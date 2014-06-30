<?php namespace Orchestra\Notifier\TestCase;

use Orchestra\Notifier\Message;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceSignature()
    {
        $stub = new Message;

        $this->assertInstanceOf('\Illuminate\Support\Fluent', $stub);
    }
}
