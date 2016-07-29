<?php

namespace Orchestra\Notifier;

use Illuminate\Contracts\Mail\Mailable;

class MailableMailer
{
    /**
     * The mailer instance.
     *
     * @var \Orchestra\Notifier\Mailer
     */
    protected $mailer;

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
     * Set the recipients of the message.
     *
     * @param  mixed  $users
     * @return $this
     */
    public function to($users)
    {
        $this->to = $users;

        return $this;
    }

    /**
     * Set the recipients of the message.
     *
     * @param  mixed  $users
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
        $mailable = $mailable->to($this->to)
                 ->cc($this->cc)
                 ->bcc($this->bcc);

        return $this->mailer->push($mailable);
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
        $mailable = $mailable->to($this->to)
                 ->cc($this->cc)
                 ->bcc($this->bcc);

        return $this->mailer->send($mailable);
    }

    /**
     * Queue a mailable message for sending.
     *
     * @param  \Illuminate\Contracts\Mail\Mailable  $mailable
     *
     * @return \Orchestra\Notifier\Receipt
     */
    public function queue(Mailable $mailable)
    {
        $mailable = $mailable->to($this->to)
                 ->cc($this->cc)
                 ->bcc($this->bcc);

        return $this->mailer->queue($mailable);
    }

    /**
     * Deliver the queued message after the given delay.
     *
     * @param  \DateTime|int  $delay
     * @param  \Illuminate\Contracts\Mail\Mailable  $mailable
     *
     * @return \Orchestra\Notifier\Receipt
     */
    public function later($delay, Mailable $mailable)
    {
        $mailable = $mailable->to($this->to)
                 ->cc($this->cc)
                 ->bcc($this->bcc);

        return $this->mailer->later($delay, $mailable);
    }
}
