<?php namespace Orchestra\Notifier;

use Aws\Ses\SesClient;
use Illuminate\Support\Manager;
use Orchestra\Memory\ContainerTrait;
use Illuminate\Mail\Transport\LogTransport;
use Illuminate\Mail\Transport\SesTransport;
use Illuminate\Mail\Transport\MailgunTransport;
use Illuminate\Mail\Transport\MandrillTransport;
use Swift_SmtpTransport as SmtpTransport;
use Swift_MailTransport as MailTransport;
use Swift_SendmailTransport as SendmailTransport;

class TransportManager extends Manager
{
    use ContainerTrait;

    /**
     * Create a new manager instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    public function __construct($app)
    {
        $this->app       = $app;
        $this->encrypter = $app->make('encrypter');
    }

    /**
     * Register the SMTP Swift Transport instance.
     *
     * @return \Swift_Transport
     */
    protected function createSmtpDriver()
    {
        $config = $this->getTransportConfig();

        $transport = SmtpTransport::newInstance($config['host'], $config['port']);

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

        return $transport;
    }

    /**
     * Register the Sendmail Swift Transport instance.
     *
     * @return \Swift_Transport
     */
    protected function createSendmailDriver()
    {
        return SendmailTransport::newInstance($this->getConfig('sendmail'));
    }

    /**
     * Create an instance of the Amazon SES Swift Transport driver.
     *
     * @return \Swift_Transport
     */
    protected function createSesDriver()
    {
        $sesClient = SesClient::factory([
            'key'    => $this->getSecureConfig('key'),
            'secret' => $this->getSecureConfig('secret'),
            'region' => $this->getConfig('region', 'us-east-1'),
        ]);

        return new SesTransport($sesClient);
    }

    /**
     * Register the Mail Swift Transport instance.
     *
     * @return \Swift_Transport
     */
    protected function createMailDriver()
    {
        return MailTransport::newInstance();
    }

    /**
     * Register the Mailgun Swift Transport instance.
     *
     * @return \Swift_Transport
     */
    protected function createMailgunDriver()
    {
        return new MailgunTransport($this->getSecureConfig('secret'), $this->getConfig('domain'));
    }

    /**
     * Register the Mandrill Swift Transport instance.
     *
     * @return \Swift_Transport
     */
    protected function createMandrillDriver()
    {
        return new MandrillTransport($this->getSecureConfig('secret'));
    }

    /**
     * Register the "Log" Swift Transport instance.
     *
     * @return \Swift_Transport
     */
    protected function createLogDriver()
    {
        return new LogTransport($this->app->make('log')->getMonolog());
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

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->memory->get('email.driver', 'mail');
    }
}
