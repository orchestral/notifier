<?php

namespace Orchestra\Notifier\Handlers;

use Closure;
use Orchestra\Notifier\Receipt;
use Illuminate\Contracts\Mail\Mailer as Mail;
use Orchestra\Contracts\Notification\Recipient;
use Orchestra\Contracts\Notification\Notification;
use Orchestra\Contracts\Notification\Receipt as ReceiptContract;
use Orchestra\Contracts\Notification\Message as MessageContract;

class Laravel extends Handler implements Notification
{
    /**
     * Mailer instance.
     *
     * @var \Illuminate\Contracts\Mail\Mailer
     */
    protected $mailer;

    /**
     * Setup Illuminate Mailer.
     *
     * @param  \Illuminate\Contracts\Mail\Mailer  $mailer
     */
    public function __construct(Mail $mailer)
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
        $data = $message->getData();
        $subject = $message->getSubject();

        $this->mailer->send($view, $data, $this->createMessageCallback($user, $subject, $callback));

        return new Receipt($this->mailer, false);
    }
}
