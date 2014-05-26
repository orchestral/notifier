Orchestra Platform Notifier Component
==============

`Orchestra\Notifier` add a simplify approach to notifier the application user using mail (or other notification service) that is managed using `Orchestra\Notifier\NotifierManager`.

[![Latest Stable Version](https://poser.pugx.org/orchestra/notifier/v/stable.png)](https://packagist.org/packages/orchestra/notifier) 
[![Total Downloads](https://poser.pugx.org/orchestra/notifier/downloads.png)](https://packagist.org/packages/orchestra/notifier) 
[![Build Status](https://travis-ci.org/orchestral/notifier.svg?branch=2.1)](https://travis-ci.org/orchestral/notifier) 
[![Coverage Status](https://coveralls.io/repos/orchestral/notifier/badge.png?branch=2.1)](https://coveralls.io/r/orchestral/notifier?branch=2.1) 
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/orchestral/notifier/badges/quality-score.png?s=f9c6821fd536f8c4787a90bee7d5fc1ea58e416f)](https://scrutinizer-ci.com/g/orchestral/notifier/) 

## Quick Installation

To install through composer, simply put the following in your `composer.json` file:

```json
{
	"require": {
		"orchestra/notifier": "2.1.*"
	}
}
```

Next add the service provider in `app/config/app.php`.

```php
'providers' => array(

	// ...

	'Orchestra\Notifier\NotifierServiceProvider',

),
```

## Resources

* [Documentation](http://orchestraplatform.com/docs/latest/components/notifier)
* [Change Log](http://orchestraplatform.com/docs/latest/components/notifier/changes#v2-1)
