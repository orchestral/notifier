<?php namespace Orchestra\Notifier;

use Closure;

interface NotifierInterface
{
    /**
     * Send notification via API.
     *
     * @param  UserProviderInterface   $user
     * @param  string                  $subject
     * @param  string|array            $view
     * @param  array                   $data
     * @param  Closure                 $callback
     * @return boolean
     */
    public function send(
        UserProviderInterface $user,
        $subject,
        $view,
        array $data = array(),
        Closure $callback = null
    );
}
