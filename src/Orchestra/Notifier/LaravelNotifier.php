<?php namespace Orchestra\Notifier;

use Illuminate\Mail\Mailer as IlluminateMailer;
use Illuminate\Auth\Reminders\RemindableInterface;

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
     * @param  NotifiableInterface $user
     * @param  string              $subject
     * @param  string|array        $view
     * @param  array               $data
     * @return boolean
     */
    public function send(NotifiableInterface $user, $subject, $view, array $data = array())
    {
        $sent = $this->mailer->send($view, $data, function ($mail) use ($user, $subject) {
            $mail->to($user->getNotifierEmail(), $user->getNotifierName());
            $mail->subject($subject);
        });

        return (count($sent) > 0);
    }
}
