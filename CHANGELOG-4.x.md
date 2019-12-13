# Changelog for 4.x

This changelog references the relevant changes (bug and security fixes) done to `orchestra/notifier`.

## 4.0.2

Released: 2019-12-13

### Fixes

* Fixes `Orchestra\Notifier\PendingMail` to use `Orchestra\Notifier\Postal`.

## 4.0.1

Released: 2019-10-30

### Fixes

* Fixes `Orchestra\Notifier\Postal::later()` queue instance resolver.

## 4.0.0

Released: 2019-09-04

### Changes

* Update support to Laravel Framework v6.0.
* Rename `Orchestra\Notifier\Mailer` to `Orchestra\Notifier\Postal`.

### Removed

* Remove `mandrill` and `sparkpost` support.
