# Changelog

This changelog references the relevant changes (bug and security fixes) done to `orchestra/notifier`.

## 3.8.2

Released: 2019-04-09

### Changes

* Register event listeners after `$app` has been booted.

## 3.8.1

Released: 2019-03-22

### Changes

* Configure `Illuminate\Mail\Mailer` to use configuration set by Notifier when it's being resolved instead of only using it once Notifier being resolved.

### Fixes 

* Fixed missing `from` on Mailable when using queued Mailable.

## 3.8.0

Released: 2019-03-13

### Changes

* Update support to Laravel Framework 5.8.

## 3.7.3

Released: 2019-03-22

### Changes

* Configure `Illuminate\Mail\Mailer` to use configuration set by Notifier when it's being resolved instead of only using it once Notifier being resolved.

### Fixes 

* Fixed missing `from` on Mailable when using queued Mailable.

## 3.7.2

Released: 2019-03-08

### Changes

* Rename `Orchestra\Notifier\MailableMailer` to `Orchestra\Notifier\PendingMail`.
* Make `$name` optional under `Orchestra\Notifier\GenericRecipient`.
* Update `Orchestra\Notifier\TransportManager` based on Laravel.

## 3.7.1

Released: 2019-02-21

### Changes

* Improve performance by prefixing all global functions calls with `\` to skip the look up and resolve process and go straight to the global function.
* Replace `jeremeamia/superclosure` with `illuminate/queue` which now utilize `opis/closure`.

## 3.7.0

Released: 2018-11-08

### Changes

* Update support to Laravel Framework 5.7.

## 3.6.0

Released: 2018-05-24

### Changes

* Update support to Laravel Framework 5.6.
