<?php namespace Orchestra\Notifier;

use Illuminate\Support\Manager;

class NotifierManager extends Manager
{
    /**
     * Create Orchestra Platform driver.
     *
     * @return OrchestraNotifier
     */
    protected function createOrchestraDriver()
    {
        $notifier = new OrchestraNotifier($this->app['orchestra.mail']);
        $notifier->attach($this->app['orchestra.memory']->makeOrFallback());

        return $notifier;
    }

    /**
     * Get the default driver.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']->get('orchestra/notifier::driver', 'orchestra');
    }
}
