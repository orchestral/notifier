<?php namespace Orchestra\Notifier;

use Closure;
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
     * @param  RecipientInterface  $user
     * @param  string              $subject
     * @param  string|array        $view
     * @param  array               $data
     * @param  \Closure            $callback
     * @return boolean
     */
    public function send(
        RecipientInterface $user,
        $subject,
        $view,
        array $data = array(),
        Closure $callback = null
    ) {
        // Send the email directly using Illuminate\Mail\Mailer interface.
        $sent = $this->mailer->send($view, $data, function ($mail) use ($user, $subject, $callback) {
            // Set the recipient detail.
            $mail->to($user->getRecipientEmail(), $user->getRecipientName());

            // Only append the subject if it was provided.
            ! empty($subject) and $mail->subject($subject);

            // Run any callback if provided.
            is_callable($callback) and call_user_func_array($callback, func_get_args());
        });

        return (count($sent) > 0);
    }
}
