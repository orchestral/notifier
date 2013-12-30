<?php namespace Orchestra\Notifier;

interface UserProviderInterface
{
    /**
     * Get the e-mail address where notification are sent.
     *
     * @return string
     */
    public function getNotifierEmail();

    /**
     * Get the fullname where notification are sent.
     *
     * @return string
     */
    public function getNotifierName();
}
