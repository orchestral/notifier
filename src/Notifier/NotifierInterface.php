<?php namespace Orchestra\Notifier;

use Closure;

interface NotifierInterface
{
    /**
     * Send notification via API.
     *
     * @param  RecipientInterface   $user
     * @param  Message              $message
     * @param  \Closure             $callback
     * @return Receipt
     */
    public function send(RecipientInterface $user, Message $message, Closure $callback = null);
}
