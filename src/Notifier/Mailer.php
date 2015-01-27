<?php namespace Orchestra\Notifier;

use Closure;
use Swift_Mailer;
use Illuminate\Support\Arr;
use Illuminate\Mail\Mailer as Mail;
use Orchestra\Memory\ContainerTrait;
use Illuminate\Support\SerializableClosure;

class Mailer
{
    use ContainerTrait;

    /**
     * Application instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $app;

    /**
     * Mailer instance.
     *
     * @var \Illuminate\Mail\Mailer
     */
    protected $mailer;

    /**
     * Transporter instance.
     *
     * @var TransportManager
     */
    protected $transport;

    /**
     * Construct a new Mail instance.
     *
     * @param  \Illuminate\Container\Container  $app
     * @param  TransportManager                 $transport
     */
    public function __construct($app, TransportManager $transport)
    {
        $this->app = $app;
        $this->transport = $transport;
    }

    /**
     * Register the Swift Mailer instance.
     *
     * @return \Illuminate\Mail\Mailer
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
     * @param  string           $view
     * @param  array            $data
     * @param  Closure|string   $callback
     * @param  string           $queue
     * @return Receipt
     */
    public function push($view, array $data, $callback, $queue = null)
    {
        $method = 'queue';
        $memory = $this->memory;

        if (false === $memory->get('email.queue', false)) {
            $method = 'send';
        }

        return call_user_func(array($this, $method), $view, $data, $callback, $queue);
    }

    /**
     * Force Orchestra Platform to send email directly.
     *
     * @param  string           $view
     * @param  array            $data
     * @param  \Closure|string  $callback
     * @return Receipt
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
     * @param  string           $view
     * @param  array            $data
     * @param  \Closure|string  $callback
     * @param  string           $queue
     * @return Receipt
     */
    public function queue($view, array $data, $callback, $queue = null)
    {
        $callback = $this->buildQueueCallable($callback);

        $with = array(
            'view'     => $view,
            'data'     => $data,
            'callback' => $callback,
        );

        $this->app['queue']->push('orchestra.mail@handleQueuedMessage', $with, $queue);

        return new Receipt($this->mailer ?: $this->app['mailer'], true);
    }

    /**
     * Build the callable for a queued e-mail job.
     *
     * @param  mixed  $callback
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
     * @param  \Illuminate\Queue\Jobs\Job  $job
     * @param  array  $data
     * @return void
     */
    public function handleQueuedMessage($job, $data)
    {
        $this->send($data['view'], $data['data'], $this->getQueuedCallable($data));

        $job->delete();
    }

    /**
     * Get the true callable for a queued e-mail message.
     *
     * @param  array  $data
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
     * @return \Illuminate\Mail\Mailer
     */
    protected function resolveMailer()
    {
        $from   = $this->memory->get('email.from');
        $mailer = $this->app['mailer'];

        // If a "from" address is set, we will set it on the mailer so that
        // all mail messages sent by the applications will utilize the same
        // "from" address on each one, which makes the developer's life a
        // lot more convenient.
        if (is_array($from) && isset($from['address'])) {
            $mailer->alwaysFrom($from['address'], $from['name']);
        }

        $mailer->setSwiftMailer(new Swift_Mailer($this->transport->driver()));

        return $mailer;
    }
}
