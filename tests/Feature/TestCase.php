<?php

namespace Orchestra\Notifier\Tests\Feature;

use Orchestra\Testbench\TestCase as Testbench;

abstract class TestCase extends Testbench
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            \Orchestra\Memory\MemoryServiceProvider::class,
            \Orchestra\Notifier\NotifierServiceProvider::class,
        ];
    }
}
