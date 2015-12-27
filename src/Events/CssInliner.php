<?php namespace Orchestra\Notifier\Events;

use Swift_Message;
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
    public function handle(MessageSending $sending)
    {
        $messsage  = $sending->message;
        $converter = new CssToInlineStyles();
        $converter->setEncoding($message->getCharset());
        $converter->setUseInlineStylesBlock();
        $converter->setCleanup();

        if ($message->getContentType() === 'text/html' ||
            ($message->getContentType() === 'multipart/alternative' && $message->getBody())
        ) {
            $converter->setHTML($message->getBody());
            $message->setBody($converter->convert());
        }

        foreach ($message->getChildren() as $part) {
            if (Str::contains($part->getContentType(), 'text/html')) {
                $converter->setHTML($part->getBody());
                $part->setBody($converter->convert());
            }
        }
    }
}
