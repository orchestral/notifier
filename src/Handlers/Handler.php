<?php

namespace Orchestra\Notifier\Handlers;

use Illuminate\Mail\Message;
use Orchestra\Contracts\Notification\Recipient;

abstract class Handler
{
    /**
     * Create message callback.
     *
     * @return \Closure
     */
    protected function createMessageResolver(Recipient $user, ?string $subject = null, ?callable $callback = null)
    {
        return static function (Message $message) use ($user, $subject, $callback) {
            // Set the recipient detail.
            $message->to($user->getRecipientEmail(), $user->getRecipientName());

            // Only append the subject if it was provided.
            ! empty($subject) && $message->subject($subject);

            // Run any callback if provided.
            \is_callable($callback) && \call_user_func($callback, ...func_get_args());
        };
    }
}
