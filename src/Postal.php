<?php

namespace Orchestra\Notifier;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Mail\Mailable as MailableContract;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Orchestra\Contracts\Notification\Receipt as ReceiptContract;
use Orchestra\Memory\Memorizable;
use Swift_Mailer;

class Postal
{
    use Concerns\Illuminate,
        Memorizable;

    /**
     * Application instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * Transporter instance.
     *
     * @var \Orchestra\Notifier\TransportManager
     */
    protected $transport;

    /**
     * Construct a new Mail instance.
     *
     * @param  \Orchestra\Notifier\TransportManager  $transport
     */
    public function __construct(Container $container, TransportManager $transport)
    {
        $this->container = $container;
        $this->transport = $transport;
    }

    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param  mixed  $users
     */
    public function to($users): PendingMail
    {
        return (new PendingMail($this))->to($users);
    }

    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param  mixed  $users
     */
    public function bcc($users): PendingMail
    {
        return (new PendingMail($this))->bcc($users);
    }

    /**
     * Allow Orchestra Platform to either use send or queue based on
     * settings.
     *
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  \Closure|string|null  $callback
     */
    public function push($view, array $data = [], $callback = null, ?string $queue = null): ReceiptContract
    {
        $method = $this->shouldBeQueued() ? 'queue' : 'send';

        return $this->{$method}($view, $data, $callback, $queue);
    }

    /**
     * Force Orchestra Platform to send email directly.
     *
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  \Closure|string|null  $callback
     */
    public function send($view, array $data = [], $callback = null): ReceiptContract
    {
        $mailer = $this->getMailer();

        if ($view instanceof MailableContract) {
            $this->updateSenderOnMailable($view)->send($mailer);
        } else {
            $mailer->send($view, $data, $callback);
        }

        return new Receipt($mailer, false);
    }

    /**
     * Force Orchestra Platform to send email using queue.
     *
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  \Closure|string|null  $callback
     */
    public function queue($view, array $data = [], $callback = null, ?string $queue = null): ReceiptContract
    {
        $mailer = $this->getMailer();

        if ($view instanceof MailableContract) {
            $this->updateSenderOnMailable($view)->queue($this->queue);
        } else {
            $callback = $this->buildQueueCallable($callback);
            $with = \compact('view', 'data', 'callback');

            $this->queue->push('orchestra.postal@handleQueuedMessage', $with, $queue);
        }

        return new Receipt($mailer, true);
    }

    /**
     * Force Orchestra Platform to send email using queue for sending after (n) seconds.
     *
     * @param  \DateInterval|int  $delay
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  \Closure|string|null  $callback
     */
    public function later($delay, $view, array $data = [], $callback = null, ?string $queue = null): ReceiptContract
    {
        $mailer = $this->getMailer();

        if ($view instanceof MailableContract) {
            return $this->updateSenderOnMailable($view)->later($delay, $this->queue);
        }

        $callback = $this->buildQueueCallable($callback);
        $with = \compact('view', 'data', 'callback');

        $this->queue->later($delay, 'orchestra.postal@handleQueuedMessage', $with, $queue);

        return new Receipt($mailer, true);
    }

    /**
     * Should the email be send via queue.
     */
    public function shouldBeQueued(): bool
    {
        return $this->memory->get('email.queue', false);
    }

    /**
     * Update from on mailable.
     */
    protected function updateSenderOnMailable(MailableContract $message): MailableContract
    {
        if (! empty($this->from['address'])) {
            $message->from($this->from['address'], $this->from['name']);
        }

        return $message;
    }

    /**
     * Register the Swift Mailer instance.
     */
    public function getMailer(): MailerContract
    {
        if (! $this->mailer instanceof MailerContract) {
            $this->mailer = $this->container->make('mailer');
        }

        return $this->mailer;
    }

    /**
     * Setup mailer.
     */
    public function configureIlluminateMailer(MailerContract $mailer): MailerContract
    {
        $from = $this->memory->get('email.from');

        // If a "from" address is set, we will set it on the mailer so that
        // all mail messages sent by the applications will utilize the same
        // "from" address on each one, which makes the developer's life a
        // lot more convenient.
        if (\is_array($from) && ! empty($from['address'])) {
            $this->from = $from;
            $mailer->alwaysFrom($from['address'], $from['name']);
        }

        if ($this->queue instanceof QueueContract) {
            $mailer->setQueue($this->queue);
        }

        $mailer->setSwiftMailer(new Swift_Mailer($this->transport->driver()));

        return $mailer;
    }
}
