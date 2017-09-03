Notifier Component for Orchestra Platform
==============

Notifier Component add a simplify approach to notifier the application user using mail (or other notification service) that is managed using `Orchestra\Notifier\NotifierManager`.

[![Build Status](https://travis-ci.org/orchestral/notifier.svg?branch=3.5)](https://travis-ci.org/orchestral/notifier)
[![Latest Stable Version](https://poser.pugx.org/orchestra/notifier/version)](https://packagist.org/packages/orchestra/notifier)
[![Total Downloads](https://poser.pugx.org/orchestra/notifier/downloads)](https://packagist.org/packages/orchestra/notifier)
[![Latest Unstable Version](https://poser.pugx.org/orchestra/notifier/v/unstable)](//packagist.org/packages/orchestra/notifier)
[![License](https://poser.pugx.org/orchestra/notifier/license)](https://packagist.org/packages/orchestra/notifier)

## Table of Content

* [Version Compatibility](#version-compatibility)
* [Installation](#installation)
* [Configuration](#configuration)
* [Changelog](https://github.com/orchestral/notifier/releases)

## Version Compatibility

Laravel    | Notifier
:----------|:----------
 4.1.x     | 2.1.x
 4.2.x     | 2.2.x
 5.0.x     | 3.0.x
 5.1.x     | 3.1.x
 5.2.x     | 3.2.x
 5.3.x     | 3.3.x
 5.4.x     | 3.4.x
 5.5.x     | 3.5.x

## Installation

To install through composer, simply put the following in your `composer.json` file:

```json
{
    "require": {
        "orchestra/notifier": "~3.0"
    }
}
```

And then run `composer install` from the terminal.

### Quick Installation

Above installation can also be simplify by using the following command:

    composer require "orchestra/notifier=~3.0"

## Configuration

Add following service providers in `config/app.php`.

```php
'providers' => [

    // ...

    Orchestra\Notifier\NotifierServiceProvider::class,

],
```
