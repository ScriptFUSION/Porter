Porter Quick Start Guide
========================

This quick start guide will walk through getting up and running with Porter from scratch and assumes you already have a PHP environment set up with Composer. Let's start by initializing our Composer file by running `composer init` in our project's root directory and accepting the defaults. We can skip defining dependencies interactively because we'll issue separate commands in  a moment.

Let's start with the [European Central Bank][ECB provider] (ECB) provider by including it in our `composer.json` with the following command.

```sh
composer require provider/european-central-bank
```

We now have the provider installed along with all its dependencies, including Porter herself. We want to create a `new Porter` instance now, but we need to pass a `ContainerInterface` to her constructor. Any PSR-11 container is valid, but let's use Joomla DI for now.

```sh
composer require joomla/di
```

Create a new container and register an instance of `EuropeanCentralBankProvider` with it. Pass the container to a new Porter instance. Don't forget to include the autoloader!

```php
use Joomla\DI\Container;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\Provider\EuropeanCentralBank\Provider\EuropeanCentralBankProvider;

require 'vendor/autoload.php';

$container = new Container;
$container->set(EuropeanCentralBankProvider::class, new EuropeanCentralBankProvider);

$porter = new Porter($container);
```

We're now ready to import any of the ECB's resources. Let's import the latest daily foreign exchange rates provided by `DailyForexRates`. Porter's `import()` method requires a `Specification` that accepts the resource we want to import.

```php
$rates = $porter->import(new Specification(new DailyForexRates));
```

Porter returns an iterator, so we can now loop over the rates and print them out.

```php
foreach ($rates as $rate) {
    echo "$rate[currency]: $rate[rate]\n";
}
```

This outputs something similar to the following, with today's current rates.

>USD: 1.2304  
JPY: 131.66  
BGN: 1.9558  
CZK: 25.357  
DKK: 7.4469  
...

Since these rates come from the European Central Bank, they're relative to the Euro (EUR), which is assumed to always be *1*. We can use this information to write a currency converter that's always up-to-date with the latest exchange rate information.

This just scratches the surface of Porter without going into any details. Explore the [rest of the manual][Readme] at your leisure to gain a fuller understanding of the features at your disposal.

⮪ [Back to main readme][Readme]


  [Readme]: https://github.com/ScriptFUSION/Porter/blob/master/README.md
  [ECB provider]: https://github.com/Provider/European-Central-Bank
