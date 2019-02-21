Notifier Component for Orchestra Platform
==============

Notifier Component add a simplify approach to notifier the application user using mail (or other notification service) that is managed using `Orchestra\Notifier\NotifierManager`.

[![Build Status](https://travis-ci.org/orchestral/notifier.svg?branch=3.8)](https://travis-ci.org/orchestral/notifier)
[![Latest Stable Version](https://poser.pugx.org/orchestra/notifier/version)](https://packagist.org/packages/orchestra/notifier)
[![Total Downloads](https://poser.pugx.org/orchestra/notifier/downloads)](https://packagist.org/packages/orchestra/notifier)
[![Latest Unstable Version](https://poser.pugx.org/orchestra/notifier/v/unstable)](//packagist.org/packages/orchestra/notifier)
[![License](https://poser.pugx.org/orchestra/notifier/license)](https://packagist.org/packages/orchestra/notifier)
[![Coverage Status](https://coveralls.io/repos/github/orchestral/notifier/badge.svg?branch=3.8)](https://coveralls.io/github/orchestral/notifier?branch=3.8)

## Table of Content

* [Version Compatibility](#version-compatibility)
* [Installation](#installation)
* [Configuration](#configuration)
* [Changelog](https://github.com/orchestral/notifier/releases)

## Version Compatibility

Laravel    | Notifier
:----------|:----------
 5.5.x     | 3.5.x
 5.6.x     | 3.6.x
 5.7.x     | 3.7.x
 5.8.x     | 3.8.x@dev

## Installation

To install through composer, simply put the following in your `composer.json` file:

```json
{
    "require": {
        "orchestra/notifier": "^3.5"
    }
}
```

And then run `composer install` from the terminal.

### Quick Installation

Above installation can also be simplify by using the following command:

    composer require "orchestra/notifier=^3.5"

## Configuration

Add following service providers in `config/app.php`.

```php
'providers' => [

    // ...

    Orchestra\Notifier\NotifierServiceProvider::class,

],
```
