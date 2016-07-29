<?php

namespace Orchestra\Notifier\Handlers;

use Illuminate\Mail\Message;
use Orchestra\Contracts\Notification\Recipient;

abstract class Handler
{
    /**
     * Create message callback.
     *
     * @param  \Orchestra\Contracts\Notification\Recipient  $user
     * @param  string|null  $subject
     * @param  callable|null  $callback
     *
     * @return \Closure
     */
    protected function createMessageCallback(Recipient $user, $subject = null, $callback = null)
    {
        return function (Message $message) use ($user, $subject, $callback) {
            // Set the recipient detail.
            $message->to($user->getRecipientEmail(), $user->getRecipientName());

            // Only append the subject if it was provided.
            ! empty($subject) && $message->subject($subject);

            // Run any callback if provided.
            is_callable($callback) && $callback(...func_get_args());
        };
    }
}
