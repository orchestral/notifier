<?php

namespace Orchestra\Notifier;

use Orchestra\Memory\Memorizable;
use Illuminate\Mail\MailableMailer as Mailable;

class MailableMailer extends Mailable
{
    use Memorizable;

    /**
     * Push a new mailable message instance.
     *
     * @param  Mailable  $mailable
     * @return mixed
     */
    public function push(Mailable $mailable)
    {
        $mailable = $mailable->to($this->to)
                 ->cc($this->cc)
                 ->bcc($this->bcc);

        $method = $this->shouldBeQueued() ? 'queue' : 'send';

        return $this->mailer->{$method}($mailable);
    }

    /**
     * Should the email be send via queue.
     *
     * @return bool
     */
    public function shouldBeQueued()
    {
        return $this->memory->get('email.queue', false);
    }
}
