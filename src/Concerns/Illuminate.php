<?php

namespace Orchestra\Notifier\Concerns;

use Closure;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Contracts\Queue\Factory as QueueContract;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\SerializableClosure;
use Illuminate\Support\Str;
use Orchestra\Contracts\Notification\Receipt;
use Serializable;

trait Illuminate
{
    /**
     * Mailer instance.
     *
     * @var \Illuminate\Contracts\Mail\Mailer
     */
    protected $mailer;

    /**
     * The queue implementation.
     *
     * @var \Illuminate\Contracts\Queue\Factory
     */
    protected $queue;

    /**
     * The global from address and name.
     *
     * @var array
     */
    protected $from;

    /**
     * Set the global from address and name.
     */
    public function alwaysFrom(string $address, ?string $name = null): void
    {
        $this->getMailer()->alwaysFrom($address, $name);
    }

    /**
     * Set the global to address and name.
     */
    public function alwaysTo(string $address, ?string $name = null): void
    {
        $this->getMailer()->alwaysTo($address, $name);
    }

    /**
     * Send a new message when only a raw text part.
     *
     * @param  mixed  $callback
     */
    public function raw(string $text, $callback): Receipt
    {
        return $this->send(['raw' => $text], [], $callback);
    }

    /**
     * Send a new message when only a plain part.
     *
     * @param  mixed  $callback
     */
    public function plain(string $view, array $data, $callback): Receipt
    {
        return $this->send(['text' => $view], $data, $callback);
    }

    /**
     * Queue a new e-mail message for sending on the given queue.
     *
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  \Closure|string|null  $callback
     */
    public function onQueue(string $queue, $view, array $data = [], $callback = null): Receipt
    {
        return $this->queue($view, $data, $callback, $queue);
    }

    /**
     * Queue a new e-mail message for sending on the given queue.
     *
     * This method didn't match rest of framework's "onQueue" phrasing. Added "onQueue".
     *
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  \Closure|string  $callback
     */
    public function queueOn(string $queue, $view, array $data, $callback = null): Receipt
    {
        return $this->onQueue($queue, $view, $data, $callback);
    }

    /**
     * Queue a new e-mail message for sending after (n) seconds on the given queue.
     *
     * @param  int  $delay
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  \Closure|string  $callback
     */
    public function laterOn(string $queue, $delay, $view, array $data = [], $callback = null): Receipt
    {
        return $this->later($delay, $view, $data, $callback, $queue);
    }

    /**
     * Set the queue manager instance.
     *
     * @return $this
     */
    public function setQueue(QueueContract $queue)
    {
        if ($this->mailer instanceof MailerContract) {
            $this->mailer->setQueue($queue);
        }

        $this->queue = $queue;

        return $this;
    }

    /**
     * Get the array of failed recipients.
     */
    public function failures(): array
    {
        return $this->getMailer()->failures();
    }

    /**
     * Build the callable for a queued e-mail job.
     *
     * @param  mixed  $callback
     *
     * @return mixed
     */
    protected function buildQueueCallable($callback)
    {
        if (! $callback instanceof Closure && ! $callback instanceof Serializable) {
            return $callback;
        }

        return \serialize(new SerializableClosure($callback));
    }

    /**
     * Handle a queued e-mail message job.
     *
     * @param  array  $data
     *
     * @return void
     */
    public function handleQueuedMessage(Job $job, $data)
    {
        $this->send($data['view'], $data['data'], $this->getQueuedCallable($data));

        $job->delete();
    }

    /**
     * Get the true callable for a queued e-mail message.
     *
     * @return mixed
     */
    protected function getQueuedCallable(array $data)
    {
        if (Str::contains($data['callback'], 'SerializableClosure')) {
            return \unserialize($data['callback'])->getClosure();
        }

        return $data['callback'];
    }

    /**
     * Register the Swift Mailer instance.
     */
    abstract public function getMailer(): MailerContract;

    /**
     * Force Orchestra Platform to send email directly.
     *
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  \Closure|string|null  $callback
     */
    abstract public function send($view, array $data = [], $callback = null): Receipt;

    /**
     * Force Orchestra Platform to send email using queue.
     *
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  \Closure|string|null  $callback
     */
    abstract public function queue($view, array $data = [], $callback = null, ?string $queue = null): Receipt;

    /**
     * Force Orchestra Platform to send email using queue for sending after (n) seconds.
     *
     * @param  int  $delay
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  \Closure|string|null  $callback
     */
    abstract public function later($delay, $view, array $data = [], $callback = null, ?string $queue = null): Receipt;
}
