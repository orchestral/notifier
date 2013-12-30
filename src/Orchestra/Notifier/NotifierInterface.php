<?php namespace Orchestra\Notifier;

use Closure;

interface NotifierInterface
{
    /**
     * Send notification via API.
     *
     * @param  RecipientInterface  $user
     * @param  string              $subject
     * @param  string|array        $view
     * @param  array               $data
     * @param  \Closure            $callback
     * @return boolean
     */
    public function send(
        RecipientInterface $user,
        $subject,
        $view,
        array $data = array(),
        Closure $callback = null
    );
}
