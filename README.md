Notifier Component for Orchestra Platform
==============

Notifier Component add a simplify approach to notifier the application user using mail (or other notification service) that is managed using `Orchestra\Notifier\NotifierManager`.


[![tests](https://github.com/orchestral/notifier/workflows/tests/badge.svg?branch=master)](https://github.com/orchestral/notifier/actions?query=workflow%3Atests+branch%3Amaster)
[![Latest Stable Version](https://poser.pugx.org/orchestra/notifier/version)](https://packagist.org/packages/orchestra/notifier)
[![Total Downloads](https://poser.pugx.org/orchestra/notifier/downloads)](https://packagist.org/packages/orchestra/notifier)
[![Latest Unstable Version](https://poser.pugx.org/orchestra/notifier/v/unstable)](//packagist.org/packages/orchestra/notifier)
[![License](https://poser.pugx.org/orchestra/notifier/license)](https://packagist.org/packages/orchestra/notifier)
[![Coverage Status](https://coveralls.io/repos/github/orchestral/notifier/badge.svg?branch=master)](https://coveralls.io/github/orchestral/notifier?branch=master)

## Version Compatibility

Laravel    | Notifier
:----------|:----------
 5.5.x     | 3.5.x
 5.6.x     | 3.6.x
 5.7.x     | 3.7.x
 5.8.x     | 3.8.x
 6.x       | 4.x
 7.x       | 5.x
 8.x       | 6.x

## Installation

To install through composer, run the following command from terminal:

```bash
composer require "orchestra/notifier"
```

## Configuration

Add following service providers in `config/app.php`.

```php
'providers' => [

    // ...

    Orchestra\Notifier\NotifierServiceProvider::class,

],
```
