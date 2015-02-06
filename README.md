Notifier Component for Orchestra Platform
==============

Notifier Component add a simplify approach to notifier the application user using mail (or other notification service) that is managed using `Orchestra\Notifier\NotifierManager`.

[![Latest Stable Version](https://img.shields.io/github/release/orchestral/notifier.svg?style=flat)](https://packagist.org/packages/orchestra/notifier)
[![Total Downloads](https://img.shields.io/packagist/dt/orchestra/notifier.svg?style=flat)](https://packagist.org/packages/orchestra/notifier)
[![MIT License](https://img.shields.io/packagist/l/orchestra/notifier.svg?style=flat)](https://packagist.org/packages/orchestra/notifier)
[![Build Status](https://img.shields.io/travis/orchestral/notifier/master.svg?style=flat)](https://travis-ci.org/orchestral/notifier)
[![Coverage Status](https://img.shields.io/coveralls/orchestral/notifier/master.svg?style=flat)](https://coveralls.io/r/orchestral/notifier?branch=master)
[![Scrutinizer Quality Score](https://img.shields.io/scrutinizer/g/orchestral/notifier/master.svg?style=flat)](https://scrutinizer-ci.com/g/orchestral/notifier/)

## Table of Content

* [Version Compatibility](#version-compatibility)
* [Installation](#installation)
* [Configuration](#configuration)
* [Resources](#resources)

## Version Compatibility

Laravel    | Notifier
:----------|:----------
 4.1.x     | 2.1.x
 4.2.x     | 2.2.x

## Installation

To install through composer, simply put the following in your `composer.json` file:

```json
{
	"require": {
		"orchestra/notifier": "3.0.*"
	}
}
```

And then run `composer install` from the terminal.

### Quick Installation

Above installation can also be simplify by using the following command:

    composer require "orchestra/notifier=2.2.*"

## Configuration

Add following service providers in `app/config/app.php`.

```php
'providers' => [

	// ...

	'Orchestra\Notifier\NotifierServiceProvider',

],
```

## Resources

* [Documentation](http://orchestraplatform.com/docs/latest/components/notifier)
* [Change Log](http://orchestraplatform.com/docs/latest/components/notifier/changes#v3-0)
