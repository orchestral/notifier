<?php

namespace Orchestra\Notifier\Handlers;

use Closure;
use Orchestra\Notifier\Mailer;
use Orchestra\Memory\Memorizable;
use SuperClosure\SerializableClosure;
use Orchestra\Contracts\Memory\Provider;
use Orchestra\Contracts\Notification\Recipient;
use Orchestra\Contracts\Notification\Notification;
use Orchestra\Contracts\Notification\Message as MessageContract;
use Orchestra\Contracts\Notification\Receipt as ReceiptContract;

class Orchestra extends Handler implements Notification
{
    use Memorizable;

    /**
     * Mailer instance.
     *
     * @var \Orchestra\Notifier\Mailer
     */
    protected $mailer;

    /**
     * Construct a new Orchestra Platform notifier.
     *
     * @param  \Orchestra\Notifier\Mailer  $mailer
     */
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Send notification via API.
     *
     * @param  \Orchestra\Contracts\Notification\Recipient  $user
     * @param  \Orchestra\Contracts\Notification\Message  $message
     * @param  \Closure|null  $callback
     *
     * @return \Orchestra\Contracts\Notification\Receipt
     */
    public function send(Recipient $user, MessageContract $message, Closure $callback = null): ReceiptContract
    {
        $view = $message->getView();
        $data = $message->getData() ?: [];
        $subject = $message->getSubject() ?: '';

        // In order to pass a Closure as "use" we need to actually convert
        // it into Serializable Closure, otherwise Laravel would throw an
        // exception.
        $callback = $callback instanceof Closure
                        ? new SerializableClosure($callback)
                        : $callback;

        $receipt = $this->mailer->push($view, $data, $this->createMessageCallback($user, $subject, $callback));

        return $receipt->usingQueue($this->isUsingQueue());
    }

    /**
     * Determine if mailer using queue.
     *
     * @return bool
     */
    protected function isUsingQueue()
    {
        // It impossible to get either the email is sent out straight away
        // when the mailer is only push to queue, in this case we should
        // assume that sending is successful when using queue.

        $queue = false;
        $driver = 'mail';

        if ($this->memory instanceof Provider) {
            $queue = $this->memory->get('email.queue', false);
            $driver = $this->memory->get('email.driver');
        }

        return $queue || in_array($driver, ['mailgun', 'mandrill', 'log']);
    }
}
