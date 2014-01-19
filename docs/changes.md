---
title: Notifier Change Log

---

## Version 2.1 {#v2-1}

### v2.1.1 {#v2-1-1}

* Handle attaching `orchestra/memory` on `orchestra.mail` service locator from `orchestra/foundation`.

### v2.1.0 {#v2-1-0}

* Initial functionality to handle user notification by implementing `Orchestra\Notifier\RecipientInterface`.
* Move `Orchestra\Foundation\Mail` to `Orchestra\Notifier\Mailer`.
* Add `Orchestra\Notifier\LaravelNotifier` and `Orchestra\Notifier\OrchestraNotifier` which implements `Orchestra\Notifier\NotifierInterface`.
