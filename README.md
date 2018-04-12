Porter <img src="https://github.com/ScriptFUSION/Porter/wiki/images/porter%20222x.png" align="right">
======

[![Latest version][Version image]][Releases]
[![Total downloads][Downloads image]][Downloads]
[![Build status][Build image]][Build]
[![Test coverage][Coverage image]][Coverage]
[![Code style][Style image]][Style]

[![][Porter icon]][Provider]
[![][Porter transformers icon]][Porter transformers]
[![][Porter connectors icon]][Porter connectors]

Porter is the PHP data importer. She fetches data from anywhere, from the local file system to third party online services, and returns an [iterator](#record-collections). Porter is a fully pluggable import framework that can be extended with [connectors](#connectors) for any protocol and [transformers](#transformers) to manipulate data immediately after import.

Ready-to-use data [providers][Provider] include all the necessary connectors and other dependencies to access popular online services such as [Stripe][Stripe provider] for online payments, the [European Central Bank][ECB provider] for foreign exchange rates or [Steam][Steam provider] for its complete PC games library and more. Porter's provider library is limited right now, and some implementations are incomplete, but we hope the PHP community will rally around Porter's abstractions and become the de facto framework for publishing online services, APIs, web scrapers and data dumps. Porter's interfaces have undergone intensive scrutiny and several iterations during years of production use to ensure they are efficient, robust, flexible, testable and easy to implement.

Porter's key [durability](#durability) feature ensures recoverable connection failures are transparently retried up to five times by default, with increasing delays between each attempt until the fetch is successful. This helps ensure intermittent network failures will not disrupt the entire import operation. Special care has been taken to ensure Porter's features are safe for concurrency, such that multiple imports can be paused and resumed simultaneously, which is especially important for iterators implemented with generators (which can be paused) as well as the upcoming asynchronous imports in v5.

Contents
--------

  1. [Benefits](#benefits)
  1. [Quick start](#quick-start)
  1. [Understanding this manual](#understanding-this-manual)
  1. [Usage](#usage)
  1. [Porter's API](#porters-api)
  1. [Overview](#overview)
  1. [Import specifications](#import-specifications)
  1. [Record collections](#record-collections)
  1. [Transformers](#transformers)
  1. [Filtering](#filtering)
  1. [Durability](#durability)
  1. [Caching](#caching)
  1. [Architecture](#architecture)
  1. [Providers](#providers)
  1. [Resources](#resources)
  1. [Connectors](#connectors)
  1. [Requirements](#requirements)
  1. [Limitations](#limitations)
  1. [Testing](#testing)
  1. [Contributing](#contributing)
  1. [License](#license)

Benefits
--------

 * Formally defines a structured data import framework with the following concepts: [providers](#providers) represent one or more [resources](#resources) that fetch data from [connectors](#connectors).
 * Provides efficient in-memory data processing interfaces to handle large data sets one record at a time, via iterators, which can be implemented using generators.
 * Offers post-import [transformations](#transformers), such as [filtering](#filtering) and [mapping][MappingTransformer], to transform third-party into data useful for first-party applications.
 * Protects against intermittent network failures with [durability](#durability) features.
 * Supports PSR-6 [caching](#caching), at the connector level, for each fetch operation.
 * Joins two or more linked data sets together using [sub-imports][Sub-imports] automatically.

Quick start
-----------

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

We're now ready to import any of the ECB's resources. Let's import the latest daily foreign exchange rates provided by `DailyForexRates`. Porter's `import()` method requires an `ImportSpecification` that accepts the resource we want to import.

```php
$rates = $porter->import(new ImportSpecification(new DailyForexRates));
```

Porter returns an iterator so we can now loop over the rates and print them out.

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

Since these rates come from the European Central Bank, they're relative to the Euro (EUR), which is assumed to always be `1`. We can use this information to write a currency converter that's always up-to-date with the latest exchange rate information.

This just scratches the surface of Porter without going into any details. Explore the rest of this manual at your leisure to gain a fuller understanding of the features at your disposal.

Understanding this manual
-------------------------

The first half of this manual covers Porter's main features and how to use them. The second half covers architecture, interface and implementation details for Porter developers. There's an intermission inbetween so you'll know where the cut-off is!

Text marked as `inline code` denotes literal code, as it would appear in a PHP file. For example, `Porter` refers specifically to the class of the same name within this library, whereas *Porter* refers to this entire project as a whole.

Usage
-----

### Creating the container

Create a `new Porter` instance—we'll usually only need one per application. Porter's constructor requires a [PSR-11][PSR-11] compatible `ContainerInterface` that acts as a repository of [providers](#providers).

When integrating Porter into a typical MVC framework application, we'll usually have a service locator or DI container implementing this interface already. We can simply inject the entire container into Porter. Although it's probably safer to create a separate container just for Porter's providers, it usually doesn't matter.

Without a framework, pick any [PSR-11 compatible library][PSR-11 search] and inject an instance of its container class. We could even write our own container since the interface is easy to implement, but using an existing library is beneficial, particularly since most support lazy-loading of services. If you're not sure which to use, [Joomla DI](https://github.com/joomla-framework/di) is fairly lightweight and straightforward.

### Registering providers

Configure the container by registering one or more Porter [providers][Provider]. In this example we'll add the [ECB provider][ECB provider] for foreign exchange rates. Most provider libraries will export just one provider class; in this case it's `EuropeanCentralBankProvider`. We could add the provider to the container by writing something similar to `$container->set(EuropeanCentralBankProvider::class, new EuropeanCentralBankProvider)`, but consult the manual for your particular container implementation for the exact syntax.

It is recommended to use the provider's class name as the container service name, as in the example in the previous paragraph. Porter will retrieve the service matching the provider's class name by default, so this reduces friction when getting started. If we use a different service name, it will need to be configured later in the `ImportSpecification` by calling `setProviderName()`.

### Importing data

Porter's `import` method accepts an `ImportSpecification` that describes which data should be imported and how the data should be transformed. To import `DailyForexRates` without applying any transformations we can write the following.

```php
$records = $porter->import(new ImportSpecification(new DailyForexRates));
```

Calling `import()` returns an instance of `PorterRecords` or `CountablePorterRecords`, which both implement `Iterator`, allowing each record in the collection to be enumerated using `foreach` as in the following example.

```php
foreach ($records as $record) {
    // Insert breakpoint or var_dump($record) here to examine each record.
}
```

Porter's API
------------

`Porter`'s simple API comprises data import methods that must always be used to begin imports, instead of calling methods directly on providers or resources, in order to take advantage of Porter's features correctly.

`Porter` provides just two public methods. These are the methods to be most familiar with, where the life of a data import operation begins.

* `import(ImportSpecification) : PorterRecords|CountablePorterRecords` &ndash; Imports data according to the design of the specified import specification.
* `importOne(ImportSpecification) : ?array` &ndash; Imports one record according to the design of the specified import specification. If more than one record is imported, `ImportException` is thrown. Use this when you're sure a provider just returns a single record.

Overview
--------

The following data flow diagram gives a high level overview of Porter's main interfaces and the data flows between them when importing data. Note that we use the term *resource* for brevity, but the actual interface is called `ProviderResource`, because *resource* is a reserved word in PHP. Also note, I don't know how to draw data flow diagrams, so just go with it.

<div align="center">

![Data flow diagram][Data flow diagram]

</div>

Our application calls `Porter::import()` with an `ImportSpecification` and receives `PorterRecords` in return. Everything else happens internally and we don't need to worry about it unless writing custom providers and resources.

Import specifications
---------------------

Import specifications specify *what* to import, *how* it should be [transformed](#transformers) and whether to use [caching](#caching). The only required parameter, passed to the constructor, is a `ProviderResource` that specifies the resource we want to import.

Options may be configured by some of the methods listed below.

 - `setProviderName(string)` &ndash; Sets the provider service name.
 - `addTransformer(Transformer)` &ndash; Adds a transformer to the end of the transformation queue.
 - `addTransformers(Transformer[])` &ndash; Adds one or more transformers to the end of the transformation queue.
 - `setContext(mixed)` &ndash; Specifies user-defined data to be passed to transformers.
 - `enableCache()` &ndash; Enables caching. Requires a `CachingConnector`.
 - `setMaxFetchAttempts(int)` &ndash; Sets the maximum number of fetch attempts per connection before failure is considered permanent.
 - `setFetchExceptionHandler(FetchExceptionHandler)` &ndash; Sets the exception handler invoked each time a fetch attempt fails.

Record collections
------------------

Record collections are `Iterator`s, guaranteeing imported data is enumerable using `foreach`. Each *record* of the collection is the familiar and flexible `array` type, allowing us to represent any flat or structured data hierarchy, like CSV or JSON, as an array.

### Details

Record collections may be `Countable`, depending on whether the imported data was countable and whether any destructive operations were performed after import. Filtering is a destructive operation since it may remove records and therefore the count reported by a `ProviderResource` would no longer be accurate. It is the responsibility of the resource to supply the number of records in its collection by returning an iterator that implements `Countable`, such as `ArrayIterator` or `CountableProviderRecords`. When a countable iterator is detected, Porter returns `CountablePorterRecords` provided no destructive operations were performed.

Record collections are composed by Porter using the decorator pattern. If provider data is not modified, `PorterRecords` will decorate the `ProviderRecords` returned from a `ProviderResource`. That is, `PorterRecords` has a pointer back to the previous collection, which could be written as: `PorterRecords` → `ProviderRecords`. If a [filter](#filtering) was applied, the collection stack would be `PorterRecords` → `FilteredRecords` → `ProviderRecords`. Normally this is an unimportant detail but can sometimes be useful for debugging.

The stack of record collection types informs us of the transformations a collection has undergone and each type holds a pointer to relevant objects that participated in the transformation. For example, `PorterRecords` holds a reference to the `ImportSpecification` that was used to create it and can be accessed using `PorterRecords::getSpecification`.

### Metadata

Since record collections are just objects, it is possible to define derived types that implement custom fields to expose additional *metadata* in addition to the iterated data. Collections are very good at representing a repeating series of data but some APIs send additional non-repeating data which we can expose as metadata. However, if the data is not repeating at all, it should be treated as a single record rather than metadata.

The result of a successful `Porter::import` call is always an instance of `PorterRecords` or `CountablePorterRecords`, depending on whether the number of records is known. If we need to access methods of the original collection, returned by the provider, we can call `findFirstCollection()` on the collection. For an example, see [CurrencyRecords][CurrencyRecords] of the [European Central Bank Provider][ECB] and its associated [test case][ECB test].

Transformers
------------

Transformers manipulate imported data. Transforming data is useful because third-party data seldom arrives in a format that looks exactly as we want. Transformers are added to the transformation queue of an `ImportSpecification` by calling its `addTransformer` method and are executed in the order they are added.

Porter includes one transformer, `FilterTransformer`, that removes records from the collection based on a predicate. For more information, see [filtering](#filtering). More powerful data transformations can be designed with [MappingTransformer][MappingTransformer]. More transformers may be available from [Porter transformers][Porter transformers].

### Writing a transformer

Transformers implement the `Transformer` interface that defines one method with the following signature.

```php
public function transform(RecordCollection $records, $context) : RecordCollection;
```

When `transform()` is called the transformer may iterate each record and change it in any way, including removing or inserting additional records. The record collection must be returned by the method, whether or not changes were made.

Transformers should also implement the `__clone` magic method if the they store any object state in order to facilitate deep copy when Porter clones the owning `ImportSpecification` during an import.

Filtering
---------

Filtering provides a way to remove some records. For each record, if the specified predicate function returns `false` (or a falsy value), the record will be removed, otherwise the record will be kept. The predicate receives the current record as an array as its first parameter and context as its second parameter.

In general we would like to avoid filtering because it is inefficient to import data and then immediately remove some of it, but some immature APIs do not provide a way to reduce the data set on the server, so filtering on the client is the only alternative. Filtering also invalidates the record count reported by some resources, meaning we no longer know how many records are in the collection before iteration.

#### Example

The following example filters out any records that do not have an *id* field present.

```php
$records = $porter->import(
    (new ImportSpecification(new MyResource))
        ->addTransformer(
            new FilterTransformer(static function (array $record) {
                return array_key_exists('id', $record);
            })
        )
);
```

Durability
----------

Porter automatically retries connections when an exception occurs during `Connector::fetch`. This helps mitigate intermittent network conditions that cause temporary data fetch failures. The number of retry attempts can be configured by calling the `setMaxFetchAttempts` method of an [`ImportSpecification`](#import-specifications).

The default exception handler, `ExponentialSleepFetchExceptionHandler`, causes a failed fetch to pause the entire program for a series of increasing delays, doubling each time. Given that the default number of retry attempts is *five*, the exception handler may be called up to *four* times, delaying each retry attempt for ~0.1, ~0.2, ~0.4, and finally, ~0.8 seconds. After the fifth and final failure, `FailingTooHardException` is thrown.

The exception handler can be changed by calling `setFetchExceptionHandler`. For example, the following code changes the initial retry delay to one second.

```php
$specification->setFetchExceptionHandler(new ExponentialSleepFetchExceptionHandler(1000000));
```

Durability only applies when connectors throw a recoverable exception type derived from `RecoverableConnectorException`. If an unexpected exception occurs the fetch attempt will be aborted. For more information, see [implementing connector durability](#durability-1). Exception handlers receive the thrown exception as their first argument. An exception handler can inspect the recoverable exception and throw its own exception if it decides the exception should be treated as fatal instead of recoverable.

Caching
-------

Any connector can be wrapped in a `CachingConnector` to provide [PSR-6][PSR-6] caching facilities to the base connector. Porter ships with one cache implementation, `MemoryCache`, which caches fetched data in memory, but this can be substituted for any other PSR-6 cache implementation. The `CachingConnector` caches raw responses for each unique [cache key](#cache-keys).

Remember that whilst using a `CachingConnector` enables caching, caching must also be enabled on a per-import basis by calling `ImportSpecification::enableCache()`.

#### Example

The follow example enables connector caching.

```php
$records = $porter->import(
    (new ImportSpecification(new MyResource))
        ->enableCache()
);
```

### Cache keys

The cache key is generated by a `CacheKeyGenerator` that encodes the source and connector options to produce a unique cache key for each distinct `Connector::fetch` request. The default `JsonCacheKeyGenerator` simply JSON-encodes the parameters to create a cache key. The cache key generation strategy may be changed by calling `CachingConnector::setCacheKeyGenerator`.

#### Writing a cache key generator

The `CacheKeyGenerator` interface defines one method with the following interface.

```php
public function generateCacheKey(string $source, array $sortedOptions) : string;
```

Implementations receive the source of the fetch request and an array and sorted options, so that options originally specified in a different order still result in the same cache key. The method must return a [PSR-6][PSR-6] compatible cache key.

##### Implementation example

The following example demonstrates cache key generation using a hash of JSON-encoded parameters.

```php
class MyCacheKeyGenerator implements CacheKeyGenerator
{
    public function generateCacheKey($source, array $sortedOptions)
    {
        return md5(json_encode([$source, $optionsSorted]));
    }
}
```

---

<div align="center">

INTERMISSION
------------

Congratulations! We have covered everything needed to use Porter.

The rest of this readme is for those wishing to go deeper. Continue when you're ready to learn how to write [providers](#providers), [resources](#resources) and [connectors](#connectors).

</div>

---

Architecture
------------

The following UML class diagram shows a partial architectural overview illustrating Porter's main components and how they are related. [[enlarge][Class diagram]]

[![Class diagram][Class diagram]][Class diagram]

Providers
---------

Providers supply their `ProviderResource` objects with a `Connector`. The provider must ensure it supplies a connector of the correct type for accessing its service's resources. A provider implements `Provider` that defines one method with the following signature.

```php
public function getConnector() : Connector;
```

A provider does not know how many resources it has nor maintains a list of such resources and neither does any other part of Porter. That is, a resource class can be created at any time and claim to belong to a given provider without any formal registration.

### Writing a provider

Providers must implement the `Provider` interface and supply a valid connector when `getConnector` is called. From Porter's perspective, writing a provider often requires little more than supplying the correct type hint when storing a connector instance, but we can embellish the class with any other features we may want. For HTTP service providers, it is common to add a base URL constant and some static methods to compose URLs, reducing code duplication in its resources.

#### Implementation example

In the following example we create a provider that only accepts `HttpConnector` instances. We also create a default connector in case one is not supplied. Note it is not always possible to create a default connector and it is perfectly valid to insist the caller supplies a connector.

```php
final class MyProvider implements Provider
{
    private $connector;

    public function __construct(HttpConnector $connector = null)
    {
        $this->connector = $connector ?: new HttpConnector;
    }

    public function getConnector()  
    {  
        return $this->connector;  
    }
}
```

Resources
---------

Resources fetch data using the supplied connector and format it as a collection of arrays. A resource implements `ProviderResource` that defines the following three methods.

```php
public function getProviderClassName() : string;
public function fetch(ImportConnector $connector) : Iterator;
```

A resource supplies the class name of the provider it expects a connector from when `getProviderClassName()` is called.

When `fetch()` is called it is passed the connector from which data must be fetched. The resource must ensure data is formatted as an iterator of array values whilst remaining as true to the original format as possible; that is, we must avoid renaming or restructuring data because it is the caller's prerogative to perform data customization if desired. The recommended way to return an iterator is to use `yield` to implicitly return a `Generator`, which has the added benefit of processing one record at a time.

The fetch method receives an `ImportConnector`, which is a runtime wrapper for the underlying connector supplied by the provider. This wrapper is used to isolate the connector's state from the rest of the application. Since PHP doesn't have native immutability support, working with cloned state is the only way we can guarantee unexpected changes do not occur once an import has started. This means it's safe to import one resource, make changes to the connector's settings and then start another import before the first has completed. Providers can also safely make changes to the underlying connector by calling `getWrappedConnector()`, because the wrapped connector is cloned as soon as `ImportConnector` is constructed.

Providing immutability via cloning is an important concept because resources are often implemented using generators, which implies delayed code execution. Multiple fetches can be started with different settings, but execute in a different order some time later when they're finally enumerated. This issue will become even more pertinent when Porter supports asynchronous fetches, enabling multiple fetches to execute concurrently. However, we don't need to worry about this implementation detail unless writing a connector ourselves.

### Writing a resource

Resources must implement the `ProviderResource` interface. `getProviderClassName()` usually returns a hard-coded provider class name and `fetch()` must always return an iterator of array values.

In this contrived example that uses dummy data and ignores the connector, suppose we want to return the numeric series one to three: the following implementation would be invalid because it returns an iterator of integer values instead of an iterator of array values.

```php
public function fetch(ImportConnector $connector)
{
    return new ArrayIterator(range(1, 3)); // Invalid return type.
}
```

Either of the following `fetch()` implementations would be valid.

```php
public function fetch(ImportConnector $connector)
{
    foreach (range(1, 3) as $number) {
        yield [$number];
    }
}
```

Since the total number of records is known, the iterator can be wrapped in `CountableProviderRecords` to enrch the caller with this information.

```php
public function fetch(ImportConnector $connector)
{
    $series = function ($limit) {
        foreach (range(1, $limit) as $number) {
            yield [$number];
        }
    }

    return new CountableProviderRecords($series($count = 3), $count, $this);
}
```

#### Implementation example

In the following example we create a resource that receives a connector from `MyProvider` and uses it to retrieve data from a hard-coded URL. We expect the data to be JSON encoded so we decode it into an array and use `yield` to return it as a single-item iterator.

```php
class MyResource extends AbstractResource
{
    private const URL = 'https://example.com';

    public function getProviderClassName()
    {
        return MyProvider::class;
    }

    public function fetch(ImportConnector $connector)
    {
        $data = $connector->fetch(self::URL);

        yield json_decode($data, true);
    }
}
```

If the data represents a repeating series, yield each record separately instead, as in the following example.

```php
public function fetch(ImportConnector $connector)
{
    $data = $connector->fetch(self::URL);

    foreach (json_decode($data, true) as $datum) {
        yield $datum;
    }
}
```

If we need to make any changes to the connector before calling fetch, such as attaching a POST body to an HTTP request, we can call `$connector->findBaseConnector()` to access the underlying connector and modify it as normal. Don't forget to check the underlying connector is of the expected type before trying to modify it.

```php
public function fetch(ImportConnector $connector)
{
    $baseConnector = $connector->findBaseConnector();

    if ($baseConnector instanceof HttpConnector) {
        $baseConnector->getOptions()
            ->setMethod('POST')
            ->setContent(http_build_query(['foo' => 'bar']))
        ;
    }

    // ...
}
```

#### Exception handling

Unrecoverable exceptions will be thrown and can be caught as normal, but good connector implementations will wrap their connection attempts in a retry block and throw a `RecoverableConnectorException`. The only way to intercept a recoverable exception is by attaching a `FetchExceptionHandler` to the `ImportConnector` by calling its `setExceptionHandler()` method. Exception handlers cannot be used for flow control because their return values are ignored, so the main application of such handlers is to re-throw recoverable exceptions as non-recoverable exceptions.

Connectors
----------

Connectors fetch remote data from a source specified at fetch time. Connectors for popular protocols are available from [Porter connectors][Porter connectors]. It might be necessary to write a new connector if dealing with uncommon or currently unsupported protocols.

### Writing a connector

Writing providers and resources is a common task that should be fairly easy but writing a connector is slightly less common and has some specific technical considerations that must be carefully considered. A connector implements the `Connector` interface that defines one method with the following signature.

```php
public function fetch(ConnectionContext $context, $source) : mixed;
```

When `fetch()` is called the connector fetches data from the specified source. Connectors may return data in any format that's convenient for resources to consume, but in general, such data should be as raw as possible and without modification. If multiple pieces of information are returned it is recommended to use a specialized response class, like the HTTP connector that returns the response body and headers together in an `HttpResponse`.

#### Options

If a connector has configurable options it must implement `ConnectorOptions` so that other parts of Porter, such as `CachingConnector`, are aware and work correctly. Any connector implementing `ConnectorOptions` must also implement a `__clone()` method to ensure all of its objects are cloned, including the `EncapsulatedOptions` instance. A minimal implementation follows.

```php
class MyConnector implements Connector, ConnectorOptions
{
    private $options;

    public function getOptions()
    {
        return $this->options;
    }

    public function __clone()
    {
        $this->options = clone $this->options;
    }

    // ...
}
```

#### Durability

To support Porter's durability features a connector may throw a subclass of `RecoverableConnectorException` to signal that the fetch operation can be retried. Execution will halt as normal if any other exception type is thrown. It is recommended to always throw a recoverable exception type unless it is certain that any number of subsequent attempts will always fail.

Recoverable exceptions must be wrapped in a `ConnectionContext::retry()` closure, wherever thrown, to ensure the connection is retried up to the number of times the user requested, calling any exception handlers set by the user or resource. If the underlying client or driver does not throw exceptions, ensure error conditions are trapped and converted to exceptions.

To promote ordinary exceptions to recoverable exceptions, wrap the fetch code in a try-catch block and pass the original exception into `RecoverableConnectorException` as its inner exception, as shown in the following example.

```php
public function fetch(ConnectionContext $context, $source)
{
    return $context->retry(function () use ($source) {
        try {
            return $this->client->fetch($source);
        } catch (Exception $e) {
            throw new RecoverableConnectorException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
```

Requirements
------------

 - [PHP 5.5](http://php.net/)
 - [Composer](https://getcomposer.org/)

Limitations
-----------

 - Imports must complete synchronously. That is, calls to `import()` are blocking. This will be addressed in v5 and is tracked by #15.
 - [Sub-imports][Sub-imports] must complete synchronously. That is, the previous sub-import must finish before the next starts.

Testing
-------

Porter is fully unit tested. Run the tests with the `composer test` command.

Contributing
------------

Everyone is welcome to contribute anything, from [ideas and issues][Issues] to [documentation and code][PRs]! For inspiration, consider the list of open [issues][Issues].

License
-------

Porter is published under the open source GNU Lesser General Public License v3.0. However, the original Porter character and artwork is copyright &copy; 2018 [Bilge](https://github.com/Bilge) and may not be reproduced or modified without express written permission.

[![][Porter icon]][Provider]
[![][Porter transformers icon]][Porter transformers]
[![][Porter connectors icon]][Porter connectors]


  [Releases]: https://github.com/ScriptFUSION/Porter/releases
  [Version image]: https://poser.pugx.org/scriptfusion/porter/version "Latest version"
  [Downloads]: https://packagist.org/packages/scriptfusion/porter
  [Downloads image]: https://poser.pugx.org/scriptfusion/porter/downloads "Total downloads"
  [Build]: http://travis-ci.org/ScriptFUSION/Porter
  [Build image]: https://travis-ci.org/ScriptFUSION/Porter.svg?branch=master "Build status"
  [Coverage]: https://codecov.io/gh/ScriptFUSION/Porter
  [Coverage image]: https://codecov.io/gh/ScriptFUSION/Porter/branch/master/graphs/badge.svg "Test coverage"
  [Style]: https://styleci.io/repos/49824895
  [Style image]: https://styleci.io/repos/49824895/shield?style=flat "Code style"
  
  [Issues]: https://github.com/ScriptFUSION/Porter/issues
  [PRs]: https://github.com/ScriptFUSION/Porter/pulls
  [Provider]: https://github.com/provider
  [Porter transformers]: https://github.com/Porter-transformers
  [Porter connectors]: https://github.com/Porter-connectors
  [Stripe provider]: https://github.com/Provider/Stripe
  [ECB provider]: https://github.com/Provider/European-Central-Bank
  [Steam provider]: https://github.com/Provider/Steam
  [MappingTransformer]: https://github.com/Porter-transformers/MappingTransformer
  [Sub-imports]: https://github.com/Porter-transformers/MappingTransformer#sub-imports
  [Mapper]: https://github.com/ScriptFUSION/Mapper
  [PSR-6]: https://www.php-fig.org/psr/psr-6
  [PSR-11]: https://www.php-fig.org/psr/psr-11
  [PSR-11 search]: https://packagist.org/explore/?dFR[tags][0]=psr-11&hFR[type][0]=library
  [Porter icon]: https://avatars3.githubusercontent.com/u/16755913?v=3&s=35 "Porter providers"
  [Porter transformers icon]: https://avatars2.githubusercontent.com/u/24607042?v=3&s=35 "Porter transformers"
  [Porter connectors icon]: https://avatars3.githubusercontent.com/u/25672142?v=3&s=35 "Porter connectors"
  [Class diagram]: https://github.com/ScriptFUSION/Porter/wiki/images/diagrams/Porter%20UML%20class%20diagram%204.0.png
  [Data flow diagram]: https://github.com/ScriptFUSION/Porter/wiki/images/diagrams/Porter%20data%20flow%20diagram%204.0.png
  [ECB]: https://github.com/Provider/European-Central-Bank
  [CurrencyRecords]: https://github.com/Provider/European-Central-Bank/blob/master/src/Records/CurrencyRecords.php
  [ECB test]: https://github.com/Provider/European-Central-Bank/blob/master/test/DailyForexRatesTest.php
