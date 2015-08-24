<?php namespace Orchestra\Notifier;

use Orchestra\Notifier\Plugins\CssInliner;
use Orchestra\Support\Providers\ServiceProvider;

class NotifierServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

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
    protected function registerMailer()
    {
        $this->app->singleton('orchestra.mail', function ($app) {
            $transport = new TransportManager($app);

            return new Mailer($app, $transport);
        });
    }

    /**
     * Register the service provider for notifier.
     *
     * @return void
     */
    protected function registerNotifier()
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
        $path = realpath(__DIR__.'/../resources');

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
