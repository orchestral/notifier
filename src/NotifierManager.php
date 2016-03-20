<?php

namespace Orchestra\Notifier;

use Illuminate\Support\Manager;
use Orchestra\Notifier\Handlers\Laravel;
use Orchestra\Notifier\Handlers\Orchestra;

class NotifierManager extends Manager
{
    /**
     * Create Laravel driver.
     *
     * @return \Orchestra\Contracts\Notification\Notification
     */
    protected function createLaravelDriver()
    {
        return new Laravel($this->app->make('mailer'));
    }

    /**
     * Create Orchestra Platform driver.
     *
     * @return \Orchestra\Contracts\Notification\Notification
     */
    protected function createOrchestraDriver()
    {
        $notifier = new Orchestra($this->app->make('orchestra.mail'));

        $notifier->attach($this->app->make('orchestra.memory')->makeOrFallback());

        return $notifier;
    }

    /**
     * Get the default driver.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app->make('config')->get('orchestra/notifier::driver', 'laravel');
    }

    /**
     * Set the default driver.
     *
     * @param  string  $name
     *
     * @return string
     */
    public function setDefaultDriver($name)
    {
        $this->app->make('config')->set('orchestra/notifier::driver', $name);
    }
}
