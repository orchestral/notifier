<?php namespace Orchestra\Notifier\Events;

use Swift_Message;
use Illuminate\Support\Str;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class CssInliner
{
    /**
     * Handle converting to inline CSS.
     *
     * @param  \Swift_Message  $message
     *
     * @return void
     */
    public function handle(Swift_Message $message)
    {
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
