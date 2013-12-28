<?php namespace Orchestra\Notifier;

use Illuminate\Auth\Reminders\RemindableInterface;

interface NotifierInterface
{
    /**
     * Send notification via API.
     *
     * @param  \Illuminate\Auth\Reminders\RemindableInterface  $user
     * @param  string                                          $subject
     * @param  string                                          $view
     * @param  array                                           $data
     * @return boolean
     */
    public function send(RemindableInterface $user, $subject, $view, array $data = array());
}
