<?php namespace Orchestra\Notifier;


interface NotifierInterface
{
    /**
     * Send notification via API.
     *
     * @param  UserProviderInterface   $user
     * @param  string                  $subject
     * @param  string|array            $view
     * @param  array                   $data
     * @return boolean
     */
    public function send(UserProviderInterface $user, $subject, $view, array $data = array());
}
