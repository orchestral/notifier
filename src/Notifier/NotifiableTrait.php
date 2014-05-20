<?php namespace Orchestra\Notifier;

use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Support\Fluent;
use Orchestra\Support\Facades\Notifier;

trait NotifiableTrait
{
    /**
     * Send email notification to user
     *
     * @param  RecipientInterface                   $user
     * @param  \Illuminate\Support\Fluent|string    $subject
     * @param  string|null                          $view
     * @param  array                                $data
     * @return boolean
     */
    protected function sendNotification(RecipientInterface $user, $subject, $view = null, array $data = [])
    {
        $entity = $user;

        if ($subject instanceof Fluent) {
            $attributes = $subject->toArray();
        } else {
            if ($user instanceof ArrayableInterface) {
                $entity = $user->toArray();
            }

            $data = array_add($data, 'user', $entity);

            $attributes = [
                'subject' => $subject,
                'view'    => $view,
                'data'    => $data
            ];
        }

        return Notifier::send($user, new Fluent($attributes));
    }
}
