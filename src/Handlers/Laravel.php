<?php namespace Orchestra\Notifier\Handlers;

use Closure;
use Illuminate\Mail\Message;
use Orchestra\Notifier\Receipt;
use Illuminate\Contracts\Mail\Mailer as Mail;
use Orchestra\Contracts\Notification\Recipient;
use Orchestra\Contracts\Notification\Notification;
use Orchestra\Contracts\Notification\Message as MessageContract;

class Laravel implements Notification
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
     * @param  \Closure  $callback
     *
     * @return \Orchestra\Contracts\Notification\Receipt
     */
    public function send(Recipient $user, MessageContract $message, Closure $callback = null)
    {
        $view    = $message->getView();
        $data    = $message->getData();
        $subject = $message->getSubject();

        // Send the email directly using Illuminate\Contracts\Mail\Mailer interface.
        $this->mailer->send($view, $data, function (Message $mail) use ($user, $subject, $callback) {
            // Set the recipient detail.
            $mail->to($user->getRecipientEmail(), $user->getRecipientName());

            // Only append the subject if it was provided.
            ! empty($subject) && $mail->subject($subject);

            // Run any callback if provided.
            is_callable($callback) && call_user_func_array($callback, func_get_args());
        });

        return new Receipt($this->mailer, false);
    }
}
