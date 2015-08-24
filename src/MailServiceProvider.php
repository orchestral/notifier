<?php namespace Orchestra\Notifier;

use Orchestra\Notifier\Events\CssInliner;
use Orchestra\Support\Providers\Traits\EventProviderTrait;
use Illuminate\Mail\MailServiceProvider as ServiceProvider;

class MailServiceProvider extends ServiceProvider
{
    use EventProviderTrait;

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'mailer.sending' => [CssInliner::class],
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

        $this->registerEventListeners($this->app->make('events'));
    }
}
