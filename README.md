Orchestra Platform Notifier Component
==============

`Orchestra\Notifier` add a simplify approach to notifier the application user using mail (or other notification service) that is managed using `Orchestra\Notifier\NotifierManager`.

[![Latest Stable Version](https://poser.pugx.org/orchestra/notifier/v/stable.png)](https://packagist.org/packages/orchestra/notifier) 
[![Total Downloads](https://poser.pugx.org/orchestra/notifier/downloads.png)](https://packagist.org/packages/orchestra/notifier) 
[![Build Status](https://travis-ci.org/orchestral/notifier.png?branch=master)](https://travis-ci.org/orchestral/notifier) 
[![Coverage Status](https://coveralls.io/repos/orchestral/notifier/badge.png?branch=master)](https://coveralls.io/r/orchestral/notifier?branch=master) 
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/orchestral/notifier/badges/quality-score.png?s=c45e8b240b7aedd08eaf70a0061c2b1d25c04f09)](https://scrutinizer-ci.com/g/orchestral/notifier/) 

## Quick Installation

To install through composer, simply put the following in your `composer.json` file:

```json
{
	"require": {
		"orchestra/notifier": "2.2.*@dev"
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
* [Change Log](http://orchestraplatform.com/docs/latest/components/notifier/changes#v2-2)
