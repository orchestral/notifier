<?php

namespace Orchestra\Notifier;

use Aws\Ses\SesClient;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;
use Illuminate\Log\LogManager;
use Illuminate\Support\Manager;
use Orchestra\Memory\Memorizable;
use GuzzleHttp\Client as HttpClient;
use Swift_SmtpTransport as SmtpTransport;
use Illuminate\Mail\Transport\LogTransport;
use Illuminate\Mail\Transport\SesTransport;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Mail\Transport\MailgunTransport;
use Swift_SendmailTransport as SendmailTransport;

class TransportManager extends Manager
{
    use Memorizable;

    /**
     * Create an instance of the SMTP Swift Transport driver.
     *
     * @return \Swift_SmtpTransport
     */
    protected function createSmtpDriver()
    {
        $config = $this->getTransportConfig();

        // The Swift SMTP transport instance will allow us to use any SMTP backend
        // for delivering mail such as Sendgrid, Amazon SES, or a custom server
        // a developer has available. We will just pass this configured host.
        $transport = new SmtpTransport($config['host'], $config['port']);

        if (isset($config['encryption'])) {
            $transport->setEncryption($config['encryption']);
        }

        // Once we have the transport we will check for the presence of a username
        // and password. If we have it we will set the credentials on the Swift
        // transporter instance so that we'll properly authenticate delivery.
        if (isset($config['username'])) {
            $transport->setUsername($config['username']);
            $transport->setPassword($this->getSecureConfig('password'));
        }

        // Next we will set any stream context options specified for the transport
        // and then return it. The option is not required any may not be inside
        // the configuration array at all so we'll verify that before adding.
        if (isset($config['stream'])) {
            $transport->setStreamOptions($config['stream']);
        }

        return $transport;
    }

    /**
     * Create an instance of the Sendmail Swift Transport driver.
     *
     * @return \Swift_SendmailTransport
     */
    protected function createSendmailDriver()
    {
        return new SendmailTransport($this->getConfig('sendmail'));
    }

    /**
     * Create an instance of the Amazon SES Swift Transport driver.
     *
     * @return \Illuminate\Mail\Transport\SesTransport
     */
    protected function createSesDriver()
    {
        $config = [
            'version' => 'latest', 'service' => 'email',
            'key' => $this->getSecureConfig('key'),
            'secret' => $this->getSecureConfig('secret'),
            'region' => $this->getConfig('region'),
            'options' => [],
        ];

        return new SesTransport(
            new SesClient($this->addSesCredentials($config)), []
        );
    }

    /**
     * Add the SES credentials to the configuration array.
     *
     * @param  array  $config
     *
     * @return array
     */
    protected function addSesCredentials(array $config)
    {
        if ($config['key'] && $config['secret']) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        return $config;
    }

    /**
     * Create an instance of the Mail Swift Transport driver.
     *
     * @return \Swift_SendmailTransport
     */
    protected function createMailDriver()
    {
        return new SendmailTransport();
    }

    /**
     * Create an instance of the Mailgun Swift Transport driver.
     *
     * @return \Illuminate\Mail\Transport\MailgunTransport
     */
    protected function createMailgunDriver()
    {
        return new MailgunTransport(
            $this->guzzle(),
            $this->getSecureConfig('secret'),
            $this->getConfig('domain'),
            null
        );
    }

    /**
     * Create an instance of the Log Swift Transport driver.
     *
     * @return \Illuminate\Mail\Transport\LogTransport
     */
    protected function createLogDriver()
    {
        $logger = $this->app->make(LoggerInterface::class);

        if ($logger instanceof LogManager) {
            $logger = $logger->channel($this->app['config']['mail.log_channel']);
        }

        return new LogTransport($logger);
    }

    /**
     * Create an instance of the Array Swift Transport Driver.
     *
     * @return \Illuminate\Mail\Transport\ArrayTransport
     */
    protected function createArrayDriver()
    {
        return new ArrayTransport();
    }

    /**
     * Get a fresh Guzzle HTTP client instance.
     *
     * @return \GuzzleHttp\Client
     */
    protected function guzzle()
    {
        return new HttpClient(Arr::add(
            $this->getConfig('guzzle') ?? [], 'connect_timeout', 60
        ));
    }

    /**
     * Get the default mail driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->attached() ? $this->memory->get('email.driver', 'mail') : 'mail';
    }

    /**
     * Get transport configuration.
     *
     * @return array
     */
    protected function getTransportConfig()
    {
        return $this->memory->get('email', []);
    }

    /**
     * Get transport configuration.
     *
     * @param  string  $key
     * @param  mixed  $default
     *
     * @return array
     */
    public function getConfig($key, $default = null)
    {
        return $this->memory->get("email.{$key}", $default);
    }

    /**
     * Get transport encrypted configuration.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     *
     * @return array
     */
    public function getSecureConfig($key = null, $default = null)
    {
        return $this->memory->secureGet("email.{$key}", $default);
    }
}
