<?php namespace Orchestra\Notifier;

use Closure;
use Illuminate\Support\Fluent;
use Illuminate\Mail\Mailer as IlluminateMailer;

class LaravelNotifier implements NotifierInterface
{
    /**
     * Mailer instance.
     *
     * @var \Illuminate\Mail\Mailer
     */
    protected $mailer;

    /**
     * Setup Illuminate Mailer.
     *
     * @param  \Illuminate\Mail\Mailer  $mailer
     */
    public function __construct(IlluminateMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Send notification via API.
     *
     * @param  RecipientInterface           $user
     * @param  \Illuminate\Support\Fluent   $message
     * @param  \Closure                     $callback
     * @return boolean
     */
    public function send(RecipientInterface $user, Fluent $message, Closure $callback = null)
    {
        $view    = $message->view;
        $data    = $message->data;
        $subject = $message->subject;

        // Send the email directly using Illuminate\Mail\Mailer interface.
        $sent = $this->mailer->send($view, $data, function ($mail) use ($user, $subject, $callback) {
            // Set the recipient detail.
            $mail->to($user->getRecipientEmail(), $user->getRecipientName());

            // Only append the subject if it was provided.
            ! empty($subject) && $mail->subject($subject);

            // Run any callback if provided.
            is_callable($callback) && call_user_func_array($callback, func_get_args());
        });

        return (count($sent) > 0);
    }
}
