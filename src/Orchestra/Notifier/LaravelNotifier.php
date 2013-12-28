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
     * @param  \Illuminate\Auth\Reminders\RemindableInterface  $user
     * @param  string                                          $subject
     * @param  string|array                                    $view
     * @param  array                                           $data
     * @return boolean
     */
    public function send(RemindableInterface $user, $subject, $view, array $data = array())
    {
        $sent = $this->mailer->send($view, $data, function ($mail) use ($user, $subject) {
            $mail->to($user->getReminderEmail());
            $mail->subject($subject);
        });

        return (count($sent) > 0);
    }
}
