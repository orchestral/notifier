<?php

namespace Orchestra\Notifier;

use Illuminate\Contracts\Mail\Mailer as Mail;
use Orchestra\Contracts\Notification\Receipt as ReceiptContract;

class Receipt implements ReceiptContract
{
    /**
     * Mailer instance.
     *
     * @var \Illuminate\Contracts\Mail\Mailer
     */
    protected $mailer;

    /**
     * Set if mail was sent using queue.
     *
     * @var bool
     */
    protected $usingQueue = false;

    /**
     * Construct a new mail receipt.
     */
    public function __construct(Mail $mailer, bool $usingQueue = false)
    {
        $this->mailer = $mailer;
        $this->usingQueue = $usingQueue;
    }

    /**
     * Return true when all e-mail has been sent.
     */
    public function sent(): bool
    {
        return $this->isQueued() || ! $this->failed();
    }

    /**
     * Return true if any of the e-mail failed to be sent.
     */
    public function failed(): bool
    {
        $failures = $this->failures();

        return ! empty($failures);
    }

    /**
     * Get list of failed email recipient.
     */
    public function failures(): array
    {
        return $this->mailer->failures();
    }

    /**
     * Set whether or not e-mail is sent via queue/delayed.
     *
     * @return $this
     */
    public function usingQueue(bool $usingQueue = false)
    {
        $this->usingQueue = $usingQueue;

        return $this;
    }

    /**
     * Get if e-mail is queued/delayed.
     */
    public function isQueued(): bool
    {
        return $this->usingQueue;
    }
}
