<?php

namespace Orchestra\Notifier;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Contracts\Foundation\Application;
use Orchestra\Support\Providers\Concerns\EventProvider;
use Illuminate\Mail\MailServiceProvider as ServiceProvider;

class MailServiceProvider extends ServiceProvider
{
    use EventProvider;

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        MessageSending::class => [Events\CssInliner::class],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->app->booted(function (Application $app) {
            $this->registerEventListeners($app->make('events'));
        })
    }
}
