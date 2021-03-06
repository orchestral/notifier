<?php

namespace Orchestra\Notifier\Handlers;

use Closure;
use Illuminate\Contracts\Mail\Mailer as Mail;
use Orchestra\Contracts\Notification\Message as MessageContract;
use Orchestra\Contracts\Notification\Notification;
use Orchestra\Contracts\Notification\Receipt as ReceiptContract;
use Orchestra\Contracts\Notification\Recipient;
use Orchestra\Notifier\Receipt;

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
     */
    public function __construct(Mail $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Send notification via API.
     */
    public function send(Recipient $user, MessageContract $message, Closure $callback = null): ReceiptContract
    {
        $view = $message->getView();
        $data = $message->getData();
        $subject = $message->getSubject();

        $this->mailer->send($view, $data, $this->createMessageResolver($user, $subject, $callback));

        return new Receipt($this->mailer, false);
    }
}
