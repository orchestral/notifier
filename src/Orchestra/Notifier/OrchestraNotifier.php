<?php namespace Orchestra\Notifier;

use Closure;
use Orchestra\Memory\Abstractable\Container;
use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Support\SerializableClosure;

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
        // In order to pass a Closure as "use" we need to actually convert it into
        // Serializable Closure, otherwise Laravel would throw an exception.
        $callback = ($callback instanceof Closure ? new SerializableClosure($callback) : $callback);

        $sent = $this->mailer->push($view, $data, function ($message) use ($user, $subject, $callback) {
            $message->to($user->getNotifierEmail(), $user->getNotifierName());

            ! empty($subject) and $message->subject($subject);

            is_callable($callback) and call_user_func_array($callback, func_get_args());
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
