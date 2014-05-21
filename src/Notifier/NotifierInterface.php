<?php namespace Orchestra\Notifier;

use Closure;
use Illuminate\Support\Fluent;

interface NotifierInterface
{
    /**
     * Send notification via API.
     *
     * @param  RecipientInterface           $user
     * @param  \Illuminate\Support\Fluent   $message
     * @param  \Closure                     $callback
     * @return Receipt
     */
    public function send(RecipientInterface $user, Fluent $message, Closure $callback = null);
}
