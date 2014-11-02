<?php namespace Orchestra\Notifier;

use Illuminate\Support\Arr;
use Orchestra\Support\Facades\Notifier;
use Illuminate\Contracts\Support\Arrayable;
use Orchestra\Contracts\Notification\Recipient;
use Orchestra\Contracts\Notification\Message as MessageContract;

trait NotifiableTrait
{
    /**
     * Send email notification to user
     *
     * @param  \Orchestra\Contracts\Notification\Recipient  $user
     * @param  \Orchestra\Contracts\Notification\Message|string  $subject
     * @param  string|null  $view
     * @param  array  $data
     * @return bool
     */
    protected function sendNotification(Recipient $user, $subject, $view = null, array $data = [])
    {
        $entity = $user;

        if ($subject instanceof MessageContract) {
            $data    = $subject->getData();
            $view    = $subject->getView();
            $subject = $subject->getSubject();
        }

        if ($user instanceof Arrayable) {
            $entity = $user->toArray();
        }

        $data = Arr::add($data, 'user', $entity);

        return Notifier::send($user, Message::create($view, $data, $subject));
    }
}
