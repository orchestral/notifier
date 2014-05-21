<?php namespace Orchestra\Notifier;

use Illuminate\Mail\Mailer;

class Receipt
{
    /**
     * Mailer instance.
     *
     * @var \Illuminate\Mail\Mailer
     */
    protected $mailer;

    /**
     * Set if mail was sent using queue.
     *
     * @var boolean
     */
    protected $usingQueue = false;

    /**
     * Construct a new mail receipt.
     *
     * @param  \Illuminate\Mail\Mailer  $mailer
     * @param  boolean                  $usingQueue
     */
    public function __construct(Mailer $mailer, $usingQueue = false)
    {
        $this->mailer = $mailer;
        $this->usingQueue = $usingQueue;
    }

    /**
     * Return true when all e-mail has been sent.
     *
     * @return boolean
     */
    public function sent()
    {
        return $this->isQueued() || ! $this->failed();
    }

    /**
     * Return true if any of the e-mail failed to be sent.
     *
     * @return boolean
     */
    public function failed()
    {
        if (! empty($this->failures())) {
            return true;
        }

        return false;
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
     * @param  boolean  $usingQueue
     * @return Receipt
     */
    public function usingQueue($usingQueue = false)
    {
        $this->usingQueue = $usingQueue;

        return $this;
    }

    /**
     * Get if e-mail is queued/delayed.
     *
     * @return boolean
     */
    public function isQueued()
    {
        return $this->usingQueue;
    }
}
