<?php

namespace Orchestra\Notifier\TestCase\Feature;

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
            \Orchestra\Notifier\NotifierServiceProvider::class,
        ];
    }
}
