<?php

namespace Orchestra\Notifier;

use Illuminate\Support\Manager;
use Orchestra\Contracts\Notification\Notification;

class NotifierManager extends Manager
{
    /**
     * Create Laravel driver.
     *
     * @return \Orchestra\Contracts\Notification\Notification
     */
    protected function createLaravelDriver(): Notification
    {
        return new Handlers\Laravel($this->app->make('mailer'));
    }

    /**
     * Create Orchestra Platform driver.
     *
     * @return \Orchestra\Contracts\Notification\Notification
     */
    protected function createOrchestraDriver(): Notification
    {
        $mailer = $this->app->make('orchestra.postal');
        $notifier = new Handlers\Orchestra($mailer);

        if ($mailer->attached()) {
            $notifier->attach($mailer->getMemoryProvider());
        } else {
            $notifier->attach($this->app->make('orchestra.memory')->makeOrFallback());
        }

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
