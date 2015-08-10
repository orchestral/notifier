<?php namespace Orchestra\Notifier\Plugin;

use Swift_Events_SendEvent;
use Swift_Events_SendListener;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class CssInliner implements Swift_Events_SendListener
{
    /**
     * @param  \Swift_Events_SendEvent  $evt
     *
     * @return void
     */
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();

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
            if (strpos($part->getContentType(), 'text/html') === 0) {
                $converter->setHTML($part->getBody());
                $part->setBody($converter->convert());
            }
        }
    }

    /**
     * Do nothing.
     *
     * @param Swift_Events_SendEvent $evt
     *
     * @return void
     */
    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        //
    }
}
