<?php namespace Orchestra\Notifier;

use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Support\Fluent;
use Orchestra\Support\Facades\Notifier;

trait NotifiableTrait
{
    /**
     * Send email notification to user
     *
     * @param  Orchestra\Model\User|RecipientInterface  $user
     * @param  string                                   $subject
     * @param  string                                   $view
     * @param  array                                    $data
     * @return boolean
     */
    protected function sendNotification(RecipientInterface $user, $subject, $view, array $data = [])
    {
        $entity = $user;

        if ($user instanceof ArrayableInterface) {
            $entity = $user->toArray();
        }

        $data = array_add($data, 'user', $entity);

        $message = new Fluent([
            'subject' => $subject,
            'view'    => $view,
            'data'    => $data
        ]);

        return Notifier::send($user, $message);
    }
}
