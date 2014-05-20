<?php namespace Orchestra\Notifier;

use Closure;
use Illuminate\Support\SerializableClosure;
use Illuminate\Support\Fluent;
use Orchestra\Memory\ContainerTrait;

class OrchestraNotifier implements NotifierInterface
{
    use ContainerTrait;

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

        // In order to pass a Closure as "use" we need to actually convert
        // it into Serializable Closure, otherwise Laravel would throw an
        // exception.
        $callback = ($callback instanceof Closure ? new SerializableClosure($callback) : $callback);

        // Send the notification using push which would allow Orchestra
        // Platform to choose either to use queue or send.
        $sent = $this->mailer->push($view, $data, function ($message) use ($user, $subject, $callback) {
            // Set the recipient detail.
            $message->to($user->getRecipientEmail(), $user->getRecipientName());

            // Only append the subject if it was provided.
            ! empty($subject) && $message->subject($subject);

            // Run any callback if provided.
            is_callable($callback) && call_user_func_array($callback, func_get_args());
        });

        // It impossible to get either the email is sent out straight away
        // when the mailer is only push to queue, in this case we should
        // assume that sending is successful when using queue.
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

        $isQueue = $this->memory->get('email.queue', false);
        $isApi   = $this->memory->get('email.driver');

        return (! ($isQueue || in_array($isApi, array('mailgun', 'mandrill', 'log'))));
    }
}
