<?php namespace Orchestra\Notifier;

use Illuminate\Support\Fluent;

class Message extends Fluent
{
    /**
     * Create a new Message instance.
     *
     * @param  string|array $view
     * @param  array        $data
     * @param  string|null  $subject
     * @return Message
     */
    public static function create($view, array $data = array(), $subject = null)
    {
        return new static([
            'view'    => $view,
            'data'    => $data,
            'subject' => $subject,
        ]);
    }

    /**
     * Get data.
     *
     * @return array
     */
    public function getData()
    {
        return array_get($this->attributes, 'data', []);
    }

    /**
     * Get subject.
     *
     * @return string
     */
    public function getSubject()
    {
        return array_get($this->attributes, 'subject', '');
    }

    /**
     * Get view.
     *
     * @return string|array
     */
    public function getView()
    {
        return array_get($this->attributes, 'view');
    }
}
