<?php

namespace Orchestra\Notifier;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Orchestra\Contracts\Notification\Message as MessageContract;
use Orchestra\Contracts\Notification\Receipt as ReceiptContract;
use Orchestra\Contracts\Notification\Recipient;
use Orchestra\Support\Facades\Notifier;

trait Notifiable
{
    /**
     * Send email notification to user.
     *
     * @param  \Orchestra\Contracts\Notification\Message|string  $subject
     * @param  \Illuminate\Contracts\Mail\Mailable|string|null  $view
     */
    protected function sendNotifications(Collection $users, $subject, $view = null, array $data = []): void
    {
        foreach ($users->all() as $user) {
            $this->sendNotification($user, $subject, $view, $data);
        }
    }

    /**
     * Send email notification to user.
     *
     * @param  \Orchestra\Contracts\Notification\Message|string  $subject
     * @param  \Illuminate\Contracts\Mail\Mailable|string|null  $view
     */
    protected function sendNotification(Recipient $user, $subject, $view = null, array $data = []): ReceiptContract
    {
        if ($subject instanceof MessageContract) {
            if ($subject->mailable()) {
                return Notifier::send($user, $subject);
            }

            $view = $subject->getView();
            $data = $subject->getData();
            $subject = $subject->getSubject();
        }

        $entity = $user;

        if ($user instanceof Arrayable) {
            $entity = $user->toArray();
        }

        $data = Arr::add($data, 'user', $entity);

        return Notifier::send($user, Message::create($view, $data, $subject));
    }
}
