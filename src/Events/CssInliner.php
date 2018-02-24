<?php

namespace Orchestra\Notifier\Events;

use Illuminate\Support\Str;
use Illuminate\Mail\Events\MessageSending;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class CssInliner
{
    /**
     * Handle converting to inline CSS.
     *
     * @param  \Illuminate\Mail\Events\MessageSending  $sending
     *
     * @return void
     */
    public function handle(MessageSending $sending): void
    {
        $message = $sending->message;

        $converter = new CssToInlineStyles();

        if ($message->getContentType() === 'text/html' ||
            ($message->getContentType() === 'multipart/alternative' && $message->getBody())
        ) {
            $message->setBody($converter->convert($message->getBody()));
        }

        foreach ($message->getChildren() as $part) {
            if (Str::contains($part->getContentType(), 'text/html')) {
                $part->setBody($converter->convert($part->getBody()));
            }
        }
    }
}
