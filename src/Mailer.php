<?php namespace Orchestra\Notifier;

use Closure;
use Swift_Mailer;
use Orchestra\Memory\Memorizable;
use Illuminate\Contracts\Queue\Job;
use SuperClosure\SerializableClosure;
use Illuminate\Contracts\Mail\Mailer as Mail;

class Mailer
{
    use Memorizable;

    /**
     * Application instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $app;

    /**
     * Mailer instance.
     *
     * @var \Illuminate\Contracts\Mail\Mailer
     */
    protected $mailer;

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
        if (! $this->mailer instanceof Mail) {
            $this->transport->setMemoryProvider($this->memory);

            $this->mailer = $this->resolveMailer();
        }

        return $this->mailer;
    }

    /**
     * Allow Orchestra Platform to either use send or queue based on
     * settings.
     *
     * @param  string|array  $view
     * @param  array  $data
     * @param  \Closure|string  $callback
     * @param  string  $queue
     *
     * @return \Orchestra\Contracts\Notification\Receipt
     */
    public function push($view, array $data, $callback, $queue = null)
    {
        $method = 'queue';
        $memory = $this->memory;

        if (false === $memory->get('email.queue', false)) {
            $method = 'send';
        }

        return call_user_func([$this, $method], $view, $data, $callback, $queue);
    }

    /**
     * Force Orchestra Platform to send email directly.
     *
     * @param  string|array  $view
     * @param  array  $data
     * @param  \Closure|string  $callback
     *
     * @return \Orchestra\Contracts\Notification\Receipt
     */
    public function send($view, array $data, $callback)
    {
        $mailer = $this->getMailer();

        $mailer->send($view, $data, $callback);

        return new Receipt($mailer, false);
    }

    /**
     * Force Orchestra Platform to send email using queue.
     *
     * @param  string|array  $view
     * @param  array  $data
     * @param  \Closure|string  $callback
     * @param  string  $queue
     *
     * @return \Orchestra\Contracts\Notification\Receipt
     */
    public function queue($view, array $data, $callback, $queue = null)
    {
        $callback = $this->buildQueueCallable($callback);

        $with = [
            'view'     => $view,
            'data'     => $data,
            'callback' => $callback,
        ];

        $this->app->make('queue')->push('orchestra.mail@handleQueuedMessage', $with, $queue);

        return new Receipt($this->mailer ?: $this->app->make('mailer'), true);
    }

    /**
     * Build the callable for a queued e-mail job.
     *
     * @param  mixed  $callback
     *
     * @return mixed
     */
    protected function buildQueueCallable($callback)
    {
        if (! $callback instanceof Closure) {
            return $callback;
        }

        return serialize(new SerializableClosure($callback));
    }

    /**
     * Handle a queued e-mail message job.
     *
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  array  $data
     *
     * @return void
     */
    public function handleQueuedMessage(Job $job, $data)
    {
        $this->send($data['view'], $data['data'], $this->getQueuedCallable($data));

        $job->delete();
    }

    /**
     * Get the true callable for a queued e-mail message.
     *
     * @param  array  $data
     *
     * @return mixed
     */
    protected function getQueuedCallable(array $data)
    {
        if (str_contains($data['callback'], 'SerializableClosure')) {
            return with(unserialize($data['callback']))->getClosure();
        }

        return $data['callback'];
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

        $mailer->setSwiftMailer(new Swift_Mailer($this->transport->driver()));

        return $mailer;
    }
}
