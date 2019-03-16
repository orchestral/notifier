<?php

namespace Orchestra\Notifier;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Orchestra\Contracts\Notification\Recipient;

class PendingMail
{
    /**
     * The mailer instance.
     *
     * @var \Orchestra\Notifier\Mailer
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
     *
     * @param  \Orchestra\Notifier\Mailer  $mailer
     */
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Set the locale of the message.
     *
     * @param  string  $locale
     *
     * @return $this
     */
    public function locale($locale)
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
        if ($users instanceof Recipient) {
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
     *
     * @param  \Illuminate\Contracts\Mail\Mailable  $mailable
     *
     * @return \Orchestra\Contracts\Notification\Receipt
     */
    public function push(Mailable $mailable)
    {
        return $this->mailer->push($this->fill($mailable));
    }

    /**
     * Send a new mailable message instance.
     *
     * @param  \Illuminate\Contracts\Mail\Mailable  $mailable
     *
     * @return \Orchestra\Contracts\Notification\Receipt
     */
    public function send(Mailable $mailable)
    {
        if ($mailable instanceof ShouldQueue) {
            return $this->queue($mailable);
        }

        return $this->sendNow($mailable);
    }

    /**
     * Send a mailable message immediately.
     *
     * @param  \Illuminate\Contracts\Mail\Mailable  $mailable
     *
     * @return mixed
     */
    public function sendNow(Mailable $mailable)
    {
        return $this->mailer->send($this->fill($mailable));
    }

    /**
     * Queue a mailable message for sending.
     *
     * @param  \Illuminate\Contracts\Mail\Mailable  $mailable
     *
     * @return \Orchestra\Contracts\Notification\Receipt
     */
    public function queue(Mailable $mailable)
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
     * @param  \DateTime|int  $delay
     * @param  \Illuminate\Contracts\Mail\Mailable  $mailable
     *
     * @return \Orchestra\Contracts\Notification\Receipt
     */
    public function later($delay, Mailable $mailable)
    {
        return $this->mailer->later($delay, $this->fill($mailable));
    }

    /**
     * Populate the mailable with the addresses.
     *
     * @param  \Illuminate\Contracts\Mail\Mailable  $mailable
     *
     * @return \Illuminate\Contracts\Mail\Mailable
     */
    protected function fill(Mailable $mailable): Mailable
    {
        return $mailable->to($this->to)
                        ->cc($this->cc)
                        ->bcc($this->bcc)
                        ->locale($this->locale);
    }
}
