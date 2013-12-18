<?php namespace Orchestra\Notifier;

use Illuminate\Support\ServiceProvider;

class NotifierServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var boolean
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
    }

    /**
    * Register the service provider for mail.
    *
    * @return void
    */
    protected function registerMailer()
    {
        $this->app->bindShared('orchestra.mail', function ($app) {
            return new Mailer($app);
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerMemoryEvent();
    }

    /**
     * Register memory events during booting.
     *
     * @return void
     */
    protected function registerMemoryEvent()
    {
        $memory = $app['orchestra.memory']->makeOrFallback();

        $this->app['orchestra.mail']->attach($memory);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('orchestra.mail');
    }
}
