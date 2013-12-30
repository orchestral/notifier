<?php namespace Orchestra\Notifier;

use Closure;
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
     * @param  UserProviderInterface   $user
     * @param  string                  $subject
     * @param  string|array            $view
     * @param  array                   $data
     * @param  Closure                 $callback
     * @return boolean
     */
    public function send(
        UserProviderInterface $user,
        $subject,
        $view,
        array $data = array(),
        Closure $callback = null
    ) {

        $sent = $this->mailer->send($view, $data, function ($mail) use ($user, $subject, $callback) {
            $mail->to($user->getNotifierEmail(), $user->getNotifierName());
            ! empty($subject) and $mail->subject($subject);

            is_callable($callback) and call_user_func_array($callback, func_get_args());
        });

        return (count($sent) > 0);
    }
}
