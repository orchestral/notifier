<?php

namespace Orchestra\Notifier;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Orchestra\Contracts\Notification\Receipt as ReceiptContract;
use Orchestra\Contracts\Notification\Recipient as RecipientContract;

class PendingMail
{
    /**
     * The mailer instance.
     *
     * @var \Orchestra\Notifier\Postal
     */
    protected $mailer;

    /**
     * The locale of the message.
     *
     * @var string
     */
    protected $locale;

    /**
     * The "to" recipients of the message.
     *
     * @var array
     */
    protected $to = [];

    /**
     * The "cc" recipients of the message.
     *
     * @var array
     */
    protected $cc = [];

    /**
     * The "bcc" recipients of the message.
     *
     * @var array
     */
    protected $bcc = [];

    /**
     * Create a new mailable mailer instance.
     */
    public function __construct(Postal $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Set the locale of the message.
     *
     * @return $this
     */
    public function locale(string $locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Set the recipients of the message.
     *
     * @param  mixed  $users
     *
     * @return $this
     */
    public function to($users)
    {
        if ($users instanceof RecipientContract) {
            $this->to = [['email' => $users->getRecipientEmail(), 'name' => $users->getRecipientName()]];
        } else {
            $this->to = $users;
        }

        if (! $this->locale && $users instanceof HasLocalePreference) {
            $this->locale($users->preferredLocale());
        }

        return $this;
    }

    /**
     * Set the recipients of the message.
     *
     * @param  mixed  $users
     *
     * @return $this
     */
    public function cc($users)
    {
        $this->cc = $users;

        return $this;
    }

    /**
     * Set the recipients of the message.
     *
     * @param  mixed  $users
     *
     * @return $this
     */
    public function bcc($users)
    {
        $this->bcc = $users;

        return $this;
    }

    /**
     * Push a mailable message for sending.
     */
    public function push(Mailable $mailable): RecipientContract
    {
        return $this->mailer->push($this->fill($mailable));
    }

    /**
     * Send a new mailable message instance.
     */
    public function send(Mailable $mailable): ReceiptContract
    {
        if ($mailable instanceof ShouldQueue) {
            return $this->queue($mailable);
        }

        return $this->sendNow($mailable);
    }

    /**
     * Send a mailable message immediately.
     */
    public function sendNow(Mailable $mailable): ReceiptContract
    {
        return $this->mailer->send($this->fill($mailable));
    }

    /**
     * Queue a mailable message for sending.
     */
    public function queue(Mailable $mailable): RecipientContract
    {
        $mailable = $this->fill($mailable);

        if (isset($mailable->delay)) {
            return $this->mailer->later($mailable->delay, $mailable);
        }

        return $this->mailer->queue($mailable);
    }

    /**
     * Deliver the queued message after the given delay.
     *
     * @param  \DateInterval|int  $delay
     */
    public function later($delay, Mailable $mailable): ReceiptContract
    {
        return $this->mailer->later($delay, $this->fill($mailable));
    }

    /**
     * Populate the mailable with the addresses.
     */
    protected function fill(Mailable $mailable): Mailable
    {
        return $mailable->to($this->to)
                        ->cc($this->cc)
                        ->bcc($this->bcc)
                        ->locale($this->locale);
    }
}
