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
        $this->registerNotifier();
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
    * Register the service provider for notifier.
    *
    * @return void
    */
    protected function registerNotifier()
    {
        $this->app->bindShared('orchestra.notifier', function ($app) {
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
        $path = realpath(__DIR__.'/../');

        $this->package('orchestra/notifier', 'orchestra/notifier', $path);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('orchestra.mail', 'orchestra.notifier');
    }
}
