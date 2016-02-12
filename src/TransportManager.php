<?php namespace Orchestra\Notifier;

use Aws\Ses\SesClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Manager;
use GuzzleHttp\Client as HttpClient;
use Orchestra\Memory\ContainerTrait;
use Swift_MailTransport as MailTransport;
use Swift_SmtpTransport as SmtpTransport;
use Illuminate\Mail\Transport\LogTransport;
use Illuminate\Mail\Transport\SesTransport;
use Illuminate\Mail\Transport\MailgunTransport;
use Illuminate\Mail\Transport\MandrillTransport;
use Swift_SendmailTransport as SendmailTransport;
use Illuminate\Contracts\Encryption\DecryptException;

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
            $transport->setPassword($this->getDecryptedConfig($config['password']));
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
        $config = $this->getTransportConfig();

        return SendmailTransport::newInstance($config['sendmail']);
    }

    /**
     * Create an instance of the Amazon SES Swift Transport driver.
     *
     * @return \Swift_Transport
     */
    protected function createSesDriver()
    {
        $config = $this->getTransportConfig();

        $client = new SesClient([
            'credentials' => [
                'key'    => $this->getDecryptedConfig($config['key']),
                'secret' => $this->getDecryptedConfig($config['secret']),
            ],
            'region'  => $config['region'],
            'service' => 'email',
            'version' => 'latest',
        ]);

        return new SesTransport($client);
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
        $config = $this->getTransportConfig();

        $client = new HttpClient(Arr::get($config, 'guzzle', []));

        return new MailgunTransport($client, $this->getDecryptedConfig($config['secret']), $config['domain']);
    }

    /**
     * Register the Mandrill Swift Transport instance.
     *
     * @return \Swift_Transport
     */
    protected function createMandrillDriver()
    {
        $config = $this->getTransportConfig();

        $client = new HttpClient(Arr::get($config, 'guzzle', []));

        return new MandrillTransport($client, $this->getDecryptedConfig($config['secret']));
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
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->memory->get('email.driver', 'mail');
    }

    /**
     * Get decrypted configuration value.
     *
     * @param  string  $value
     *
     * @return string
     */
    public function getDecryptedConfig($value)
    {
        try {
            return $this->encrypter->decrypt($value);
        } catch (DecryptException $e) {
            return $value;
        }
    }
}
