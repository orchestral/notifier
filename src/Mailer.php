<?php

namespace Orchestra\Notifier;

use Swift_Mailer;
use Orchestra\Memory\Memorizable;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Contracts\Mail\Mailable as MailableContract;
use Orchestra\Contracts\Notification\Receipt as ReceiptContract;

class Mailer
{
    use Concerns\Illuminate,
        Memorizable;

    /**
     * Application instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $app;

    /**
     * Transporter instance.
     *
     * @var \Orchestra\Notifier\TransportManager
     */
    protected $transport;

    /**
     * Construct a new Mail instance.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @param  \Orchestra\Notifier\TransportManager  $transport
     */
    public function __construct($app, TransportManager $transport)
    {
        $this->app = $app;
        $this->transport = $transport;
    }

    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param  mixed  $users
     *
     * @return \Orchestra\Notifier\PendingMail
     */
    public function to($users): PendingMail
    {
        return (new PendingMail($this))->to($users);
    }

    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param  mixed  $users
     *
     * @return \Orchestra\Notifier\PendingMail
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
     * @param  array  $data
     * @param  \Closure|string|null  $callback
     * @param  string|null  $queue
     *
     * @return \Orchestra\Contracts\Notification\Receipt
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
     * @param  array  $data
     * @param  \Closure|string|null  $callback
     *
     * @return \Orchestra\Contracts\Notification\Receipt
     */
    public function send($view, array $data = [], $callback = null): ReceiptContract
    {
        $mailer = $this->getMailer();

        if ($view instanceof MailableContract) {
            $this->updateFromOnMailable($view)->send($mailer);
        } else {
            $mailer->send($view, $data, $callback);
        }

        return new Receipt($mailer, false);
    }

    /**
     * Force Orchestra Platform to send email using queue.
     *
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  array  $data
     * @param  \Closure|string|null  $callback
     * @param  string|null  $queue
     *
     * @return \Orchestra\Contracts\Notification\Receipt
     */
    public function queue($view, array $data = [], $callback = null, ?string $queue = null): ReceiptContract
    {
        if ($view instanceof MailableContract) {
            $this->updateFromOnMailable($view)->queue($this->queue);
        } else {
            $callback = $this->buildQueueCallable($callback);
            $with = \compact('view', 'data', 'callback');

            $this->queue->push('orchestra.mail@handleQueuedMessage', $with, $queue);
        }

        return new Receipt($this->getMailer(), true);
    }

    /**
     * Force Orchestra Platform to send email using queue for sending after (n) seconds.
     *
     * @param  int  $delay
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  array  $data
     * @param  \Closure|string|null  $callback
     * @param  string|null  $queue
     *
     * @return \Orchestra\Contracts\Notification\Receipt
     */
    public function later($delay, $view, array $data = [], $callback = null, ?string $queue = null): ReceiptContract
    {
        if ($view instanceof MailableContract) {
            return $this->updateFromOnMailable($view)->later($delay, $this->queue);
        }

        $callback = $this->buildQueueCallable($callback);
        $with = \compact('view', 'data', 'callback');

        $this->queue->later($delay, 'orchestra.mail@handleQueuedMessage', $with, $queue);

        return new Receipt($this->getMailer(), true);
    }

    /**
     * Should the email be send via queue.
     *
     * @return bool
     */
    public function shouldBeQueued(): bool
    {
        return $this->memory->get('email.queue', false);
    }

    /**
     * Register the Swift Mailer instance.
     *
     * @return \Illuminate\Contracts\Mail\Mailer
     */
    public function getMailer(): MailerContract
    {
        if (! $this->mailer instanceof MailerContract) {
            $this->transport->setMemoryProvider($this->memory);

            $this->mailer = $this->resolveMailer();
        }

        return $this->mailer;
    }

    /**
     * Setup mailer.
     *
     * @return \Illuminate\Contracts\Mail\Mailer
     */
    protected function resolveMailer(): MailerContract
    {
        $from = $this->memory->get('email.from');
        $mailer = $this->app->make('mailer');

        // If a "from" address is set, we will set it on the mailer so that
        // all mail messages sent by the applications will utilize the same
        // "from" address on each one, which makes the developer's life a
        // lot more convenient.
        if (\is_array($from) && ! empty($from['address'])) {
            $mailer->alwaysFrom($from['address'], $from['name']);
        }

        if ($this->queue instanceof QueueContract) {
            $mailer->setQueue($this->queue);
        }

        $mailer->setSwiftMailer(new Swift_Mailer($this->transport->driver()));

        return $mailer;
    }

    /**
     * Update from on mailable.
     *
     * @param  \Illuminate\Contracts\Mail\Mailable $message
     * @return \Illuminate\Contracts\Mail\Mailable
     */
    protected function updateFromOnMailable(MailableContract $message): MailableContract
    {
        if (! empty($this->from['address'])) {
            $message->from($this->from['address'], $this->from['name']);
        }

        return $message;
    }
}
