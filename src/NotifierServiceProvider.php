<?php

namespace Orchestra\Notifier;

use Orchestra\Support\Providers\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class NotifierServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerMailer();

        $this->registerNotifier();
    }

    /**
     * Register the service provider for mail.
     *
     * @return void
     */
    protected function registerMailer(): void
    {
        $this->app->singleton('orchestra.mail', function ($app) {
            $mailer = new Mailer($app, new TransportManager($app));

            if ($app->bound('orchestra.platform.memory')) {
                $mailer->attach($app->make('orchestra.platform.memory'));
            }

            if ($app->bound('queue')) {
                $mailer->setQueue($app->make('queue'));
            }

            return $mailer;
        });
    }

    /**
     * Register the service provider for notifier.
     *
     * @return void
     */
    protected function registerNotifier(): void
    {
        $this->app->singleton('orchestra.notifier', function ($app) {
            return new NotifierManager($app);
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $path = \realpath(__DIR__.'/../resources');

        $this->addConfigComponent('orchestra/notifier', 'orchestra/notifier', "{$path}/config");
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['orchestra.mail', 'orchestra.notifier'];
    }
}
