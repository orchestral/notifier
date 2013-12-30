<?php namespace Orchestra\Notifier;


interface NotifierInterface
{
    /**
     * Send notification via API.
     *
     * @param  NotifiableInterface $user
     * @param  string              $subject
     * @param  string|array        $view
     * @param  array               $data
     * @return boolean
     */
    public function send(NotifiableInterface $user, $subject, $view, array $data = array());
}
