<?php namespace Orchestra\Notifier;

use Closure;
use InvalidArgumentException;
use Illuminate\Support\SerializableClosure;
use Illuminate\Mail\Mailer as M;
use Illuminate\Mail\Transport\LogTransport;
use Illuminate\Mail\Transport\MailgunTransport;
use Illuminate\Mail\Transport\MandrillTransport;
use Orchestra\Memory\ContainerTrait;
use Swift_Mailer;
use Swift_SmtpTransport as SmtpTransport;
use Swift_MailTransport as MailTransport;
use Swift_SendmailTransport as SendmailTransport;

class Mailer
{
    use ContainerTrait;

    /**
     * Application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Mailer instance.
     *
     * @var \Illuminate\Mail\Mailer
     */
    protected $mailer;

    /**
     * Construct a new Mail instance.
     *
     * @param  \Illuminate\Foundation\Application   $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Register the Swift Mailer instance.
     *
     * @return void
     */
    protected function getMailer()
    {
        if ($this->mailer instanceof M) {
            return $this->mailer;
        }

        $this->mailer = $this->app['mailer'];
        $config       = $this->memory->get('email');
        $transport    = $this->registerSwiftTransport($config);

        // If a "from" address is set, we will set it on the mailer so that
        // all mail messages sent by the applications will utilize the same
        // "from" address on each one, which makes the developer's life a
        // lot more convenient.
        $from = $this->memory->get('email.from');

        if (is_array($from) && isset($from['address'])) {
            $this->mailer->alwaysFrom($from['address'], $from['name']);
        }

        $this->mailer->setSwiftMailer(new Swift_Mailer($transport));

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
     * @return \Illuminate\Mail\Mailer
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
     * @param  Closure|string   $callback
     * @param  string           $queue
     * @return \Illuminate\Mail\Mailer
     */
    public function send($view, array $data, $callback)
    {
        return $this->getMailer()->send($view, $data, $callback);
    }

    /**
     * Force Orchestra Platform to send email using queue.
     *
     * @param  string           $view
     * @param  array            $data
     * @param  Closure|string   $callback
     * @param  string           $queue
     * @return \Illuminate\Mail\Mailer
     */
    public function queue($view, array $data, $callback, $queue = null)
    {
        $callback = $this->buildQueueCallable($callback);

        $with = array(
            'view'     => $view,
            'data'     => $data,
            'callback' => $callback,
        );

        return $this->app['queue']->push('orchestra.mail@handleQueuedMessage', $with, $queue);
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
     * Register the Swift Transport instance.
     *
     * @param  array  $config
     * @return \Swift_Transport
     */
    protected function registerSwiftTransport($config)
    {
        switch ($config['driver'])
        {
            case 'smtp':
                return $this->registerSmtpTransport($config);

            case 'sendmail':
                return $this->registerSendmailTransport($config);

            case 'mail':
                return $this->registerMailTransport($config);

            case 'mailgun':
                return $this->registerMailgunTransport($config);

            case 'mandrill':
                return $this->registerMandrillTransport($config);

            case 'log':
                return $this->registerLogTransport($config);

            default:
                throw new InvalidArgumentException('Invalid mail driver.');
        }
    }

    /**
     * Register the SMTP Swift Transport instance.
     *
     * @param  array  $config
     * @return \Swift_Transport
     */
    protected function registerSmtpTransport($config)
    {
        $transport = SmtpTransport::newInstance($config['host'], $config['port']);

        if (isset($config['encryption'])) {
            $transport->setEncryption($config['encryption']);
        }

        // Once we have the transport we will check for the presence of a username
        // and password. If we have it we will set the credentials on the Swift
        // transporter instance so that we'll properly authenticate delivery.
        if (isset($config['username'])) {
            $transport->setUsername($config['username']);
            $transport->setPassword($config['password']);
        }

        return $transport;
    }

    /**
     * Register the Sendmail Swift Transport instance.
     *
     * @param  array  $config
     * @return \Swift_Transport
     */
    protected function registerSendmailTransport($config)
    {
        return SendmailTransport::newInstance($config['sendmail']);
    }

    /**
     * Register the Mail Swift Transport instance.
     *
     * @param  array  $config
     * @return \Swift_Transport
     */
    protected function registerMailTransport($config)
    {
        unset($config);

        return MailTransport::newInstance();
    }

    /**
     * Register the Mailgun Swift Transport instance.
     *
     * @param  array  $config
     * @return \Swift_Transport
     */
    protected function registerMailgunTransport($config)
    {
        return new MailgunTransport($config['secret'], $config['domain']);
    }

    /**
     * Register the Mandrill Swift Transport instance.
     *
     * @param  array  $config
     * @return \Swift_Transport
     */
    protected function registerMandrillTransport($config)
    {
        return new MandrillTransport($config['secret']);
    }

    /**
     * Register the "Log" Swift Transport instance.
     *
     * @param  array  $config
     * @return void
     */
    protected function registerLogTransport($config)
    {
        return new LogTransport($this->app['log']->getMonolog());
    }
}
