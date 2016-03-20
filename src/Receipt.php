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
     *
     * @param  \Illuminate\Contracts\Mail\Mailer  $mailer
     * @param  bool  $usingQueue
     */
    public function __construct(Mail $mailer, $usingQueue = false)
    {
        $this->mailer     = $mailer;
        $this->usingQueue = $usingQueue;
    }

    /**
     * Return true when all e-mail has been sent.
     *
     * @return bool
     */
    public function sent()
    {
        return $this->isQueued() || ! $this->failed();
    }

    /**
     * Return true if any of the e-mail failed to be sent.
     *
     * @return bool
     */
    public function failed()
    {
        $failures = $this->failures();

        return (! empty($failures));
    }

    /**
     * Get list of failed email recipient.
     *
     * @return array
     */
    public function failures()
    {
        return $this->mailer->failures();
    }

    /**
     * Set whether or not e-mail is sent via queue/delayed.
     *
     * @param  bool  $usingQueue
     *
     * @return $this
     */
    public function usingQueue($usingQueue = false)
    {
        $this->usingQueue = $usingQueue;

        return $this;
    }

    /**
     * Get if e-mail is queued/delayed.
     *
     * @return bool
     */
    public function isQueued()
    {
        return $this->usingQueue;
    }
}
