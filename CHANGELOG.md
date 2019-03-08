# Changelog

This changelog references the relevant changes (bug and security fixes) done to `orchestra/notifier`.

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

## 3.5.2

Released: 2018-02-24

### Changes

* Assign `orchestra.platform.memory` as `Orchestra\Contracts\Memory\Provider` if the IoC is bound.

## 3.5.1

Released: 2017-12-26

### Changes

* Move `jeremeamia/superclosure` dependencies to `require` section.
* Standardise return value for `send()` and `queue()` on `Orchestra\Notifier\Mailer` class.

## 3.5.0

Released: 2017-11-14

### Changes

* Update support to Laravel Framework 5.5.
