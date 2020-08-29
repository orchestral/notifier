<?php

namespace Orchestra\Notifier;

use Orchestra\Contracts\Notification\Recipient;

class GenericRecipient implements Recipient
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
     */
    public function __construct(string $email, ?string $name = null)
    {
        $this->email = $email;
        $this->name = $name ?? $email;
    }

    /**
     * Get the e-mail address where notification are sent.
     */
    public function getRecipientEmail(): string
    {
        return $this->email;
    }

    /**
     * Get the fullname where notification are sent.
     */
    public function getRecipientName(): string
    {
        return $this->name;
    }
}
