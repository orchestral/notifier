<?php

namespace Orchestra\Notifier\Concerns;

use Closure;
use Serializable;
use Illuminate\Support\Str;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\SerializableClosure;
use Orchestra\Contracts\Notification\Receipt;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Contracts\Queue\Factory as QueueContract;

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
     *
     * @param  string  $address
     * @param  string|null  $name
     *
     * @return void
     */
    public function alwaysFrom(string $address, ?string $name = null): void
    {
        $this->getMailer()->alwaysFrom($address, $name);
    }

    /**
     * Set the global to address and name.
     *
     * @param  string  $address
     * @param  string|null  $name
     *
     * @return void
     */
    public function alwaysTo(string $address, ?string $name = null): void
    {
        $this->getMailer()->alwaysTo($address, $name);
    }

    /**
     * Send a new message when only a raw text part.
     *
     * @param  string  $text
     * @param  mixed  $callback
     *
     * @return \Orchestra\Contracts\Notification\Receipt
     */
    public function raw(string $text, $callback): Receipt
    {
        return $this->send(['raw' => $text], [], $callback);
    }

    /**
     * Send a new message when only a plain part.
     *
     * @param  string  $view
     * @param  array  $data
     * @param  mixed  $callback
     *
     * @return \Orchestra\Contracts\Notification\Receipt
     */
    public function plain(string $view, array $data, $callback): Receipt
    {
        return $this->send(['text' => $view], $data, $callback);
    }

    /**
     * Queue a new e-mail message for sending on the given queue.
     *
     * @param  string  $queue
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  array  $data
     * @param  \Closure|string|null  $callback
     *
     * @return \Orchestra\Contracts\Notification\Receipt
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
     * @param  string  $queue
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  array  $data
     * @param  \Closure|string  $callback
     *
     * @return \Orchestra\Contracts\Notification\Receipt
     */
    public function queueOn(string $queue, $view, array $data, $callback = null): Receipt
    {
        return $this->onQueue($queue, $view, $data, $callback);
    }

    /**
     * Queue a new e-mail message for sending after (n) seconds on the given queue.
     *
     * @param  string  $queue
     * @param  int  $delay
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  array  $data
     * @param  \Closure|string  $callback
     *
     * @return \Orchestra\Contracts\Notification\Receipt
     */
    public function laterOn(string $queue, $delay, $view, array $data = [], $callback = null): Receipt
    {
        return $this->later($delay, $view, $data, $callback, $queue);
    }

    /**
     * Set the queue manager instance.
     *
     * @param  \Illuminate\Contracts\Queue\Factory  $queue
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
     *
     * @return array
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
     * @param  \Illuminate\Contracts\Queue\Job  $job
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
     * @param  array  $data
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
     *
     * @return \Illuminate\Contracts\Mail\Mailer
     */
    abstract public function getMailer(): MailerContract;

    /**
     * Force Orchestra Platform to send email directly.
     *
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  array  $data
     * @param  \Closure|string|null  $callback
     *
     * @return \Orchestra\Contracts\Notification\Receipt
     */
    abstract public function send($view, array $data = [], $callback = null): Receipt;

    /**
     * Force Orchestra Platform to send email using queue.
     *
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  array  $data
     * @param  \Closure|string|null  $callback
     * @param  string|null  $queue
     *
     * @return \Orchestra\Contracts\Notification\Receipt
     */
    abstract public function queue($view, array $data = [], $callback = null, ?string $queue = null): Receipt;

    /**
     * Force Orchestra Platform to send email using queue for sending after (n) seconds.
     *
     * @param  int  $delay
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  array  $data
     * @param  \Closure|string|null  $callback
     * @param  string|null  $queue
     *
     * @return \Orchestra\Contracts\Notification\Receipt
     */
    abstract public function later($delay, $view, array $data = [], $callback = null, ?string $queue = null): Receipt;
}
