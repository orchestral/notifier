<?php

namespace Orchestra\Notifier;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\DeferrableProvider;
use Orchestra\Support\Providers\ServiceProvider;

class NotifierServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPostal();

        $this->registerNotifier();

        $this->registerIlluminateMailerResolver();
    }

    /**
     * Register the service provider for mail.
     *
     * @return void
     */
    protected function registerPostal(): void
    {
        $this->app->singleton('orchestra.postal', static function (Container $app) {
            $mailer = new Postal($app, $transport = new TransportManager($app));

            if ($app->bound('orchestra.platform.memory')) {
                $mailer->attach($memory = $app->make('orchestra.platform.memory'));
                $transport->setMemoryProvider($memory);
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
        $this->app->singleton('orchestra.notifier', static function (Container $app) {
            return new NotifierManager($app);
        });
    }

    /**
     * Register the service provider for notifier.
     *
     * @return void
     */
    protected function registerIlluminateMailerResolver(): void
    {
        $this->app->afterResolving('mailer', function ($service) {
            $this->app->make('orchestra.postal')->configureIlluminateMailer($service);
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $path = \realpath(__DIR__.'/../');

        $this->addConfigComponent('orchestra/notifier', 'orchestra/notifier', "{$path}/config");
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['orchestra.postal', 'orchestra.notifier'];
    }
}
