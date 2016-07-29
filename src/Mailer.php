<?php

namespace Orchestra\Notifier;

use Orchestra\Notifier\Traits\Illuminate;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Contracts\Mail\Mailable as MailableContract;

class Mailer
{
    use Illuminate;

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
        $this->app       = $app;
        $this->transport = $transport;
    }

    /**
     * Register the Swift Mailer instance.
     *
     * @return \Illuminate\Contracts\Mail\Mailer
     */
    public function getMailer()
    {
        if (! $this->mailer instanceof MailerContract) {
            $this->transport->setMemoryProvider($this->memory);

            $this->mailer = $this->resolveMailer();
        }

        return $this->mailer;
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
    public function push($view, array $data = [], $callback = null, $queue = null)
    {
        $method = 'send';
        $memory = $this->memory;

        if ($this->shouldBeQueued()) {
            $method = 'queue';
        }

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
    public function send($view, array $data = [], $callback = null)
    {
        $mailer = $this->getMailer();

        if ($view instanceof MailableContract) {
            return $view->send($this->getMailer());
        }

        $mailer->send($view, $data, $callback);

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
    public function queue($view, array $data = [], $callback = null, $queue = null)
    {
        if ($view instanceof MailableContract) {
            return $view->queue($this->queue);
        }

        $callback = $this->buildQueueCallable($callback);

        $with = [
            'view'     => $view,
            'data'     => $data,
            'callback' => $callback,
        ];

        $this->queue->push('orchestra.mail@handleQueuedMessage', $with, $queue);

        return new Receipt($this->getMailer(), true);
    }

    /**
     * Should the email be send via queue.
     *
     * @return bool
     */
    public function shouldBeQueued()
    {
        return $this->memory->get('email.queue', false);
    }

    /**
     * Setup mailer.
     *
     * @return \Illuminate\Contracts\Mail\Mailer
     */
    protected function resolveMailer()
    {
        $from   = $this->memory->get('email.from');
        $mailer = $this->app->make('mailer');

        // If a "from" address is set, we will set it on the mailer so that
        // all mail messages sent by the applications will utilize the same
        // "from" address on each one, which makes the developer's life a
        // lot more convenient.
        if (is_array($from) && ! empty($from['address'])) {
            $mailer->alwaysFrom($from['address'], $from['name']);
        }

        if ($this->queue instanceof QueueContract) {
            $mailer->setQueue($this->queue);
        }

        $mailer->setSwiftMailer(new Swift_Mailer($this->transport->driver()));

        return $mailer;
    }
}
