<?php namespace Orchestra\Notifier;

use Orchestra\Memory\Abstractable\Container;
use Illuminate\Auth\Reminders\RemindableInterface;

class OrchestraNotifier extends Container implements NotifierInterface
{
    /**
     * Mailer instance.
     *
     * @var Mailer
     */
    protected $mailer;

    /**
     * Construct a new Orchestra Platform notifier.
     *
     * @param  Mailer  $mailer
     */
    public function __construct(Mailer $mailer)
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
        $sent = $this->mailer->push($view, $data, function ($message) use ($user, $subject) {
            $message->to($user->getNotifierEmail(), $user->getNotifierName());
            $message->subject($subject);
        });

        if ($this->isNotQueued()) {
            return (count($sent) > 0);
        }

        return true;
    }

    /**
     * Determine if mailer using queue.
     *
     * @return boolean
     */
    protected function isNotQueued()
    {
        if (! isset($this->memory)) {
            return true;
        }

        return (! $this->memory->get('email.queue', false));
    }
}
