<?php namespace Orchestra\Notifier;

use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Support\Arr;
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

        if ($subject instanceof Fluent || $subject instanceof Message) {
            $attributes = $subject->toArray();

            $data    = $attributes['data'];
            $subject = $attributes['subject'];
            $view    = $attributes['view'];
        }

        if ($user instanceof ArrayableInterface) {
            $entity = $user->toArray();
        }

        $data = Arr::add($data, 'user', $entity);

        return Notifier::send($user, Message::create($view, $data, $subject));
    }
}
