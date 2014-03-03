<?php namespace Orchestra\Notifier;

class GenericRecipient implements RecipientInterface
{
    /**
     * Recipient e-mail address.
     *
     * @var string
     */
    protected $email;

    /**
     * Recipient name.
     *
     * @var string
     */
    protected $name;

    /**
     * Create a new recipient.
     *
     * @param  string  $email
     * @param  string  $name
     */
    public function __construct($email, $name)
    {
        $this->email = $email;
        $this->name = $name;
    }

    /**
     * Get the e-mail address where notification are sent.
     *
     * @return string
     */
    public function getRecipientEmail()
    {
        return $this->email;
    }

    /**
     * Get the fullname where notification are sent.
     *
     * @return string
     */
    public function getRecipientName()
    {
        return $this->name;
    }
}
