<?php namespace Orchestra\Notifier\Handlers;

use Closure;
use Illuminate\Mail\Message;
use Orchestra\Notifier\Mailer;
use Orchestra\Memory\Memorizable;
use SuperClosure\SerializableClosure;
use Orchestra\Contracts\Memory\Provider;
use Orchestra\Contracts\Notification\Recipient;
use Orchestra\Contracts\Notification\Notification;
use Orchestra\Contracts\Notification\Message as MessageContract;

class Orchestra implements Notification
{
    use Memorizable;

    /**
     * Mailer instance.
     *
     * @var \Orchestra\Notifier\Mailer
     */
    protected $mailer;

    /**
     * Construct a new Orchestra Platform notifier.
     *
     * @param  \Orchestra\Notifier\Mailer  $mailer
     */
    public function __construct(Mailer $mailer)
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
        $data    = $message->getData() ?: [];
        $subject = $message->getSubject() ?: '';

        // In order to pass a Closure as "use" we need to actually convert
        // it into Serializable Closure, otherwise Laravel would throw an
        // exception.
        $callback = ($callback instanceof Closure ? new SerializableClosure($callback) : $callback);

        // Send the notification using push which would allow Orchestra
        // Platform to choose either to use queue or send.
        $receipt = $this->mailer->push($view, $data, function (Message $message) use ($user, $subject, $callback) {
            // Set the recipient detail.
            $message->to($user->getRecipientEmail(), $user->getRecipientName());

            // Only append the subject if it was provided.
            ! empty($subject) && $message->subject($subject);

            // Run any callback if provided.
            is_callable($callback) && call_user_func_array($callback, func_get_args());
        });

        return $receipt->usingQueue($this->isUsingQueue());
    }

    /**
     * Determine if mailer using queue.
     *
     * @return bool
     */
    protected function isUsingQueue()
    {
        // It impossible to get either the email is sent out straight away
        // when the mailer is only push to queue, in this case we should
        // assume that sending is successful when using queue.

        $usingQueue = false;
        $usingApi   = 'mail';

        if ($this->memory instanceof Provider) {
            $usingQueue = $this->memory->get('email.queue', false);
            $usingApi   = $this->memory->get('email.driver');
        }

        return ($usingQueue || in_array($usingApi, ['mailgun', 'mandrill', 'log']));
    }
}
