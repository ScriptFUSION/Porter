Porter <img src="https://github.com/ScriptFUSION/Porter/blob/master/docs/images/porter%20222x.png?raw=true" align="right">
======

[![Version image]][Releases]
[![Downloads image]][Downloads]
[![Build image]][Build]
[![Quickstart image]][Quickstart build]
[![Quickstart Symfony image]][Quickstart Symfony build]
[![Coverage image]][Coverage]
[![Mutation score image][MSI image]][MSI report]

### Durable and asynchronous data imports for consuming data at scale and publishing testable SDKs

Porter is the all-purpose PHP data importer. She fetches data from APIs, web scraping or anywhere and serves it as an iterable [record collection](#record-collections), encouraging processing one record at a time instead of loading full data sets into memory. [Durability](#durability) features provide automatic, transparent recovery from intermittent network errors by default.

Porter's [interface triad](#overview) of [providers](#providers), [resources](#resources) and [connectors](#connectors) allows us to publish testable SDKs and maps well to APIs and HTTP endpoints. For example, a typical API such as GitHub would define the provider as `GitHubProvider`, a resource as `GetUser` or `ListRepositories` and the connector could be [HttpConnector][].

Porter supports [asynchronous](#asynchronous) imports via [fibers][]<sup>(PHP 8.1)</sup> allowing multiple imports to be started, paused and resumed concurrently. Async allows us to import data as fast as possible, transforming applications from network-bound (slow) to CPU-bound (optimal). [Throttle](#throttling) support ensures we do not exceed peer connection or throughput limits.

###### Porter network quick links

[![][Porter icon]][Provider]
[![][Porter transformers icon]][Porter transformers]
[![][Porter connectors icon]][Porter connectors]

Contents
--------

  1. [Benefits](#benefits)
  2. [Quick start](#quick-start)
  3. [About this manual](#about-this-manual)
  4. [Usage](#usage)
  5. [Porter's API](#porters-api)
  6. [Overview](#overview)
  7. [Import specifications](#import-specifications)
  8. [Record collections](#record-collections)
  9. [Asynchronous](#asynchronous)
  10. [Transformers](#transformers)
  11. [Filtering](#filtering)
  12. [Durability](#durability)
  13. [Caching](#caching)
  14. [Architecture](#architecture)
  15. [Providers](#providers)
  16. [Resources](#resources)
  17. [Connectors](#connectors)
  18. [Limitations](#limitations)
  19. [Testing](#testing)
  20. [Contributing](#contributing)
  21. [License](#license)

Benefits
--------

 * Defines an easily testable **interface triad** for data imports: [providers](#providers) represent one or more [resources](#resources) that fetch data from [connectors](#connectors). These interfaces make it very easy to **test and mock** specific parts of the import lifecycle using industry standard tools, whether we want to mock at the connector level and feed in raw responses or mock at the resource level and supply ready-hydrated objects.
 * Provides **memory-efficient data processing** interfaces that handle large data sets one record at a time, via iterators, which can be implemented using deferred execution with generators.
 * [Asynchronous](#asynchronous) imports offer highly efficient **CPU-bound data processing** for large scale imports across multiple connections concurrently, eliminating network latency performance bottlenecks. Concurrency can be **rate-limited** using [throttling](#throttling).
 * Protects against intermittent network failures with [durability](#durability) features that transparently and **automatically retry failed data fetches**.
 * Offers post-import [transformations](#transformers), such as [filtering](#filtering) and [mapping][MappingTransformer], to transform third-party data into useful data for our applications.
 * Supports PSR-6 [caching](#caching), at the connector level, for each fetch operation.
 * Joins two or more linked data sets together using [sub-imports][Sub-imports] automatically.

Quick start
-----------

To get started quickly, consuming an existing Porter provider, try one of our quick start guides:

* [General quickstart][Quickstart] &ndash; Get started using Porter with vanilla PHP (no framework) and the [European Central Bank][ECB provider] provider.
* [Symfony quickstart][] &ndash; Get started by integrating Porter into a new Symfony project with the [Steam provider][].

For a more thorough introduction continue reading.

About this manual
-----------------

Those **consuming** a Porter provider create one instance of `Porter` for their application and an instance of `Import` for each data import they wish to perform. Those **publishing** providers must implement `Provider` and `ProviderResource`.

The first half of this manual covers Porter's main API for *consuming* data services. The second half covers architecture, interface and implementation details for *publishing* data services. There's an intermission in-between, so you'll know where the separation is!

Text marked as `inline code` denotes literal code, as it would appear in a PHP file. For example, `Porter` refers specifically to the class of the same name within this library, whereas *Porter* refers to this project as a whole.

Usage
-----

### Creating the container

Create a `new Porter` instance—we'll usually only need one per application. Porter's constructor requires a [PSR-11][] compatible `ContainerInterface` that acts as a repository of [providers](#providers).

When integrating Porter into a typical MVC framework application, we'll usually have a service locator or DI container implementing this interface already. We can simply inject the entire container into Porter, although it's best practice to create a separate container just for Porter's providers. For an example of doing this correctly in Symfony, see the [Symfony quickstart][].

Without a framework, pick any [PSR-11 compatible library][PSR-11 search] and inject an instance of its container class. We could even write our own container since the interface is easy to implement, but using an existing library is beneficial, particularly since most support lazy-loading of services. If you're not sure which to use, [Joomla DI](https://github.com/joomla-framework/di) seems fairly simple and light.

### Registering providers

Configure the container by registering one or more Porter [providers][Provider]. In this example we'll add the [ECB provider][] for foreign exchange rates. Most provider libraries will export just one provider class; in this case it's `EuropeanCentralBankProvider`. We could add the provider to the container by writing something similar to `$container->set(EuropeanCentralBankProvider::class, new EuropeanCentralBankProvider)`, but consult the manual for your particular container implementation for the exact syntax.

It is recommended to use the provider's class name as the container service name, as in the example in the previous paragraph. Porter will retrieve the service matching the provider's class name by default, so this reduces friction when getting started. If we use a different service name, it will need to be configured on the `Import` by calling `setProviderName()`.

### Importing data

Porter's `import` method accepts an `Import` that describes which data should be imported and how the data should be transformed. To import `DailyForexRates` without applying any transformations we can write the following.

```php
$records = $porter->import(new Import(new DailyForexRates));
```

Calling `import()` returns an instance of `PorterRecords` or `CountablePorterRecords`, which both implement `Iterator`, allowing each record in the collection to be enumerated using `foreach` as in the following example.

```php
foreach ($records as $record) {
    var_dump($record);
}
```

Porter's API
------------

`Porter`'s simple API comprises data import methods that must always be used to begin imports, instead of calling methods directly on providers or resources, in order to take advantage of Porter's features correctly.

`Porter` provides just two public methods for importing data. These are the methods to be most familiar with, where the life of a data import operation begins.

* `import(Import): PorterRecords|CountablePorterRecords` &ndash; Imports one or more records from the resource contained in the specified import specification. If the total size of the collection is known, the record collection may implement `Countable`, otherwise `PorterRecords` is returned.
* `importOne(Import): mixed` &ndash; Imports one record from the resource contained in the specified import specification. If more than one record is imported, `ImportException` is thrown. Use this when a provider implements `SingleRecordResource`, returning just a single record.

Overview
--------

The following data flow diagram gives a high level overview of Porter's main interfaces and the data flows between them when importing data. Note that we use the term *resource* for brevity, but the interface is actually called `ProviderResource`, because *resource* is a reserved word in PHP.

<div align="center">

![Data flow diagram][]

</div>

Our application calls `Porter::import()` with an `Import` and receives `PorterRecords` back. Everything else happens internally, so we don't need to worry about it unless writing custom providers, resources or connectors.

Import specifications
---------------------

Import specifications specify *what* to import, *how* it should be [transformed](#transformers) and whether to use [caching](#caching). Create a new instance of `Import` and pass a `ProviderResource` that specifies the resource we want to import.

Options may be configured using the methods below.

 - `setProviderName(string)` &ndash; Sets the provider service name.
 - `addTransformer(Transformer)` &ndash; Adds a transformer to the end of the transformation queue.
 - `addTransformers(Transformer[])` &ndash; Adds one or more transformers to the end of the transformation queue.
 - `setContext(mixed)` &ndash; Specifies user-defined data to be passed to transformers.
 - `enableCache()` &ndash; Enables caching. Requires a `CachingConnector`.
 - `setMaxFetchAttempts(int)` &ndash; Sets the maximum number of fetch attempts per connection before failure is considered permanent.
 - `setFetchExceptionHandler(FetchExceptionHandler)` &ndash; Sets the exception handler invoked each time a fetch attempt fails.
 - `setThrottle(Throttle)` &ndash; Sets the connection throttle, invoked each time a connector fetches data.

Record collections
------------------

Record collections are `Iterator`s, guaranteeing imported data is enumerable using `foreach`. Each *record* of the collection is the `mixed` type, offering the flexibility to present data series in whatever format is most useful for the user, such as an array for JSON data or an object that bundles data with functionality that the user can directly invoke.

### Details

Record collections may be `Countable`, depending on whether the imported data was countable and whether any destructive operations were performed after import. Filtering is a destructive operation since it may remove records and therefore the count reported by a `ProviderResource` would no longer be accurate. It is the responsibility of the resource to supply the total number of records in its collection by returning an iterator that implements `Countable`, such as `ArrayIterator`, or more commonly, `CountableProviderRecords`. When a countable iterator is used, Porter returns `CountablePorterRecords`, provided no destructive operations were performed.

Record collections are composed by Porter using the decorator pattern. If provider data is not modified, `PorterRecords` will decorate the `ProviderRecords` returned from a `ProviderResource`. That is, `PorterRecords` has a pointer back to the previous collection, which could be written as: `PorterRecords` → `ProviderRecords`. If a [filter](#filtering) was applied, the collection stack would be `PorterRecords` → `FilteredRecords` → `ProviderRecords`. Normally, this is an unimportant detail but can sometimes be useful for debugging.

The stack of record collection types informs us of the transformations a collection has undergone and each type holds a pointer to relevant objects that participated in the transformation. For example, `PorterRecords` holds a reference to the `Import` that was used to create it and can be accessed using `PorterRecords::getImport`.

### Metadata

Since record collections are just objects, it is possible to define derived types that implement custom fields to expose additional *metadata* in addition to the iterated data. Collections are very good at representing a repeating series of data but some APIs send additional non-repeating data which we can expose as metadata. However, if the data is not repeating at all, it should be treated as a single record rather than metadata.

The result of a successful `Porter::import` call is always an instance of `PorterRecords` or `CountablePorterRecords`, depending on whether the number of records is known. If we need to access methods of the original collection, returned by the provider, we can call `findFirstCollection()` on the collection. For an example, see [CurrencyRecords][] of the [European Central Bank Provider][ECB provider] and its associated [test case][ECB test].

Asynchronous
------------

Porter has had asynchronous support since version 5 (2019) thanks to [Amp][] integration. In v5, async was implemented with coroutines, but from version 6 onwards, Porter uses the simpler [fibers][] model. Fiber support is included in PHP 8.1 and can be added to PHP 8.0 using [ext-fiber][]. PHP 7 does not support fibers, so if you are stuck with that version of PHP, coroutines are the only option. It is strongly recommended to upgrade to PHP 8.1 to use async, to avoid unnecessary bugs leading to segfaults and to avoid getting trapped in the coroutine architecture that is cumbersome to upgrade, difficult to debug and harder to reason about.

In version 5, Porter offered a dual API to support the asynchronous code path. That is, `Porter::import` had the async analogue: `Porter::importAsync` and `Porter::importOne` had `Porter::importOneAsync`. In version 6 we switched to fibers but kept the dual API to making migrating from coroutines to fibers slightly easier. Since version 7, we unified the dual API because async with fibers can be almost entirely transparent: the synchronous and asynchronous code paths are identical, so we don't even have to think about async unless and until we want to start leveraging its benefits in our application.

To use async in Porter v7 onwards, simply wrap an `import()` or `importOne()` call in an `async()` call using one of the following two methods.

```php
use function Amp\async;

async(
    $this->porter->import(...),
    new Import(new MyResource())
);

// -OR-

async(fn () => $this->porter->import(new Import(new MyResource()));
```

In order for this to work, the only requirement is that the underlying [connector][Porter connectors] supports fibers. To know whether a particular connector supports fibers, consult its documentation. The most common connector, [HttpConnector][], already has fiber support.

Calling `async()` returns a `Future` representing the eventual result of an asynchronous operation. To understand how futures are composed and abstracted, or how to await and iterate collections of futures, is beyond the scope of this document. Full details about async programming can be found in the official [Amp documentation][].

>Note: At the time of writing, Amp v3 is still in beta, so you may find it necessary to lower a project's minimum stability to include Amp packages, via `composer.json`:
> ```json
> "minimum-stability": "beta"
> ````
> To avoid pulling in any betas other than those absolutely necessary for the dependency solver to be satisfied, it is recommended to also set stable packages as the preferred stability when using the above setting.
> ```json
> "prefer-stable": true
> ```


### Throttling

The asynchronous import model is very powerful because it changes our application's performance model from I/O-bound, limited by the speed of the network, to CPU-bound, limited by the speed of the CPU. In the traditional synchronous model, each import operation must wait for the previous to complete before the next begins, meaning the total import time depends on how long it takes each import's network I/O to finish. In the async model, since we send many requests concurrently without waiting for the previous to complete. On average, each import operation only takes as long as our CPU takes to process it, since we are busy processing another import during network latency (except during the initial "spin-up").

Synchronously, we seldom trip protection measures even for high volume imports, however the naïve approach to asynchronous imports is often fraught with perils. If we import 10,000 HTTP resources at once, one of two things usually happens: either we run out of PHP memory and the process terminates prematurely or the HTTP server rejects us after sending too many requests in a short period. The solution is throttling.

[Async Throttle][] is included with Porter to throttle asynchronous imports. The throttle works by preventing additional operations starting when too many are executing concurrently, based on user-defined limits. By default, `NullThrottle` is assigned, which does not throttle connections. `DualThrottle` can be used to set two independent connection rate limits: the maximum number of connections per second and the maximum number of concurrent connections.

A  `DualThrottle` can be assigned by modifying the import specification as follows.

```php
(new Import)->setThrottle(new DualThrottle)
```

#### ThrottledConnector

A throttle can be assigned to a connector implementing the `ThrottledConnector` interface. This allows a provider to apply a throttle to all its resources by default. When a throttle is assigned to both a connector and an import specification, the specification's throttle takes priority. If the connector we want to use does not implement `ThrottledConnector`, simply extend the connector and implement the interface.

Implementing `ThrottledConnector` is likely to be preferable when we want many resources to share the same throttle or when we want to inject the throttle using dependency injection, since specifications are typically instantiated inline whereas connectors are not. That is, we would usually declare connectors in our application framework's service configuration.

Transformers
------------

Transformers manipulate imported data. Transforming data is useful because third-party data seldom arrives in a format that looks exactly as we want. Transformers are added to the transformation queue of an `Import` by calling its `addTransformer` method and are executed in the order they are added.

Porter includes one transformer, `FilterTransformer`, that removes records from the collection based on a predicate. For more information, see [filtering](#filtering). More powerful data transformations can be designed with [MappingTransformer][]. More transformers may be available from [Porter transformers][].

### Writing a transformer

Transformers implement the `Transformer` and/or `AsyncTransformer` interfaces that define one or more of the following methods.

```php
public function transform(RecordCollection $records, mixed $context): RecordCollection;

public function transformAsync(AsyncRecordCollection $records, mixed $context): AsyncRecordCollection;
```

When `transform()` or `transformAsync()` is called the transformer may iterate each record and change it in any way, including removing or inserting additional records. The record collection must be returned by the method, whether or not changes were made.

Transformers should also implement the `__clone` magic method if they store any object state, in order to facilitate deep copy when Porter clones the owning `Import` during import.

Filtering
---------

Filtering provides a way to remove some records. For each record, if the specified predicate function returns `false` (or a falsy value), the record will be removed, otherwise the record will be kept. The predicate receives the current record as its first parameter and context as its second parameter.

In general, we would like to avoid filtering because it is inefficient to import data and then immediately remove some of it, but some immature APIs do not provide a way to reduce the data set on the server, so filtering on the client is the only alternative. Filtering also invalidates the record count reported by some resources, meaning we no longer know how many records are in the collection before iteration.

### Example

The following example filters out any records that do not have an *id* field present.

```php
$records = $porter->import(
    (new Import(new MyResource))
        ->addTransformer(
            new FilterTransformer(static function (array $record) {
                return array_key_exists('id', $record);
            })
        )
);
```

Durability
----------

Porter automatically retries connections when an exception occurs during `Connector::fetch`. This helps mitigate intermittent network conditions that cause temporary data fetch failures. The number of retry attempts can be configured by calling the `setMaxFetchAttempts` method of an [`Import`](#import-specifications).

The default exception handler, `ExponentialSleepFetchExceptionHandler`, causes a failed fetch to pause the entire program for a series of increasing delays, doubling each time. Given that the default number of retry attempts is *five*, the exception handler may be called up to *four* times, delaying each retry attempt for ~0.1, ~0.2, ~0.4, and finally, ~0.8 seconds. After the fifth and final failure, `FailingTooHardException` is thrown.

The exception handler can be changed by calling `setFetchExceptionHandler`. For example, the following code changes the initial retry delay to one second.

```php
$specification->setFetchExceptionHandler(new ExponentialSleepFetchExceptionHandler(1000000));
```

Durability only applies when connectors throw a recoverable exception type derived from `RecoverableConnectorException`. If an unexpected exception occurs, the fetch attempt will be aborted. For more information, see [implementing connector durability](#durability-1). Exception handlers receive the thrown exception as their first argument. An exception handler can inspect the recoverable exception and throw its own exception if it decides the exception should be treated as fatal instead of recoverable.

Caching
-------

Any connector can be wrapped in a `CachingConnector` to provide [PSR-6][] caching facilities to the base connector. Porter ships with one cache implementation, `MemoryCache`, which caches fetched data in memory, but this can be substituted for any other PSR-6 cache implementation. The `CachingConnector` caches raw responses for each unique request, where uniqueness is determined by `DataSource::computeHash`.

Remember that whilst using a `CachingConnector` enables caching, caching must also be enabled on a per-import basis by calling `Import::enableCache()`.

### Example

The follow example enables connector caching.

```php
$records = $porter->import(
    (new Import(new MyResource))
        ->enableCache()
);
```

---

<div align="center">

INTERMISSION ☕️
--------------

Congratulations! We have covered everything needed to use Porter.

The rest of this readme is for those wishing to go deeper. Continue when you're ready to learn how to write [providers](#providers), [resources](#resources) and [connectors](#connectors).

</div>

---

Architecture
------------

The following UML class diagram shows a partial architectural overview illustrating Porter's main components and how they are related. Asynchronous implementation details are mostly omitted since they mirror the synchronous system. [[enlarge][Class diagram]]

[![Class diagram][]][Class diagram]

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

In the following example we create a provider that only accepts `HttpConnector` instances. We also create a default connector in case one is not supplied. Note it is not always possible to create a default connector, and it is perfectly valid to insist the caller supplies a connector.

```php
final class MyProvider implements Provider
{
    private $connector;

    public function __construct(Connector $connector = null)
    {
        $this->connector = $connector ?: new HttpConnector;
    }

    public function getConnector(): Connector
    {
        return $this->connector;
    }
}
```

Resources
---------

Resources fetch data using the supplied connector and format it as an iterator. A resource implements `ProviderResource` that defines the following three methods.

```php
public function getProviderClassName(): string;
public function fetch(ImportConnector $connector): \Iterator;
```

A resource supplies the class name of the provider it expects a connector from when `getProviderClassName()` is called.

When `fetch()` is called it is passed the connector from which data must be fetched. The resource must ensure data is formatted as an iterator of values whilst remaining as true to the original format as possible; that is, we must avoid renaming or restructuring data because it is the caller's prerogative to perform data customization if desired. The recommended way to return an iterator is to use `yield` to implicitly return a `Generator`, which has the added benefit of processing one record at a time.

The fetch method receives an `ImportConnector`, which is a runtime wrapper for the underlying connector supplied by the provider. This wrapper is used to isolate the connector's state from the rest of the application. Since PHP doesn't have native immutability support, working with cloned state is the only way we can guarantee unexpected changes do not occur once an import has started. This means it's safe to import one resource, make changes to the connector's settings and then start another import before the first has completed. Providers can also safely make changes to the underlying connector by calling `getWrappedConnector()`, because the wrapped connector is cloned as soon as `ImportConnector` is constructed.

Providing immutability via cloning is an important concept because resources are often implemented using generators, which implies delayed code execution. Multiple fetches can be started with different settings, but execute in a different order some time later when they're finally enumerated. This issue will become even more pertinent when Porter supports asynchronous fetches, enabling multiple fetches to execute concurrently. However, we don't need to worry about this implementation detail unless writing a connector ourselves.

### Writing a resource

Resources must implement the `ProviderResource` interface. `getProviderClassName()` usually returns a hard-coded provider class name and `fetch()` must always return an iterator.

Either of the following `fetch()` implementations would be valid.

```php
public function fetch(ImportConnector $connector): \Iterator
{
    return new ArrayIterator(range(1, 3)); // Invalid return type.
}
```

Since the total number of records is known, the iterator can be wrapped in `CountableProviderRecords` to enrich the caller with this information.

```php
public function fetch(ImportConnector $connector): \Iterator
{
    return new CountableProviderRecords(new ArrayIterator(range(1, $count = 3)), $count, $this);
}
```

#### Implementation example

In the following example we create a resource that receives a connector from `MyProvider` and uses it to retrieve data from a hard-coded URL. We expect the data to be JSON encoded so we decode it into an array and use `yield` to return it as a single-item iterator.

```php
class MyResource implements ProviderResource, SingleRecordResource
{
    private const URL = 'https://example.com';

    public function getProviderClassName(): string
    {
        return MyProvider::class;
    }

    public function fetch(ImportConnector $connector): \Iterator
    {
        $data = $connector->fetch(self::URL);

        yield json_decode($data, true);
    }
}
```

If the data represents a repeating series, `yield` each record separately instead, as in the following example, and remove the `SingleRecordResource` marker interface.

```php
public function fetch(ImportConnector $connector): \Iterator
{
    $data = $connector->fetch(self::URL);

    foreach (json_decode($data, true) as $datum) {
        yield $datum;
    }
}
```

#### Exception handling

Unrecoverable exceptions will be thrown and can be caught as normal, but good connector implementations will wrap their connection attempts in a retry block and throw a `RecoverableConnectorException`. The only way to intercept a recoverable exception is by attaching a `FetchExceptionHandler` to the `ImportConnector` by calling its `setExceptionHandler()` method. Exception handlers cannot be used for flow control because their return values are ignored, so the main application of such handlers is to re-throw recoverable exceptions as non-recoverable exceptions.

Connectors
----------

Connectors fetch remote data from a source specified at fetch time. Connectors for popular protocols are available from [Porter connectors][Porter connectors]. It might be necessary to write a new connector if dealing with uncommon or currently unsupported protocols. Writing providers and resources is a common task that should be fairly easy but writing a connector is less common.

### Writing a connector

A connector implements the `Connector` interface that defines one method with the following signature.

```php
public function fetch(DataSource $source): mixed;
```

When `fetch()` is called the connector fetches data from the specified data source. Connectors may return data in any format that's convenient for resources to consume, but in general, such data should be as raw as possible and without modification. If multiple pieces of information are returned it is recommended to use a specialized object, like the `HttpResponse` returned by the HTTP connector that contains the response headers and body together.

#### Data sources

The `DataSource` interface must be implemented to supply the necessary parameters for a connector to locate a data source. For an HTTP connector, this might include URL, method, body and headers. For a database connector, this might be a SQL query.

`DataSource` specifies one method with the following signature.

```php
public function computeHash(): string;
```

Data sources are required to return a unique hash for their state. If the state changes, the hash must change. If states are effectively equivalent, the hash must be the same. This is used by the cache system to determine whether the fetch operation has been seen before and thus can be served from the cache rather than fetching fresh data again.

It is important to define a canonical order for hashed inputs such that identical state presented in different orders does not create different hash values. For example, we might sort HTTP headers alphabetically before hashing because header order is not significant and reordering headers should not produce different output.

#### Durability

To support Porter's durability features a connector may throw a subclass of `RecoverableConnectorException` to signal that the fetch operation can be retried. Execution will halt as normal if any other exception type is thrown. It is recommended to throw a recoverable exception type when the fetch operation is idempotent.

Limitations
-----------

Current limitations that may affect some users and should be addressed in the near future.

 - No end-to-end data steaming interface.

Testing
-------

Porter is fully unit and mutation tested.

* Run unit tests with the `composer test` command.
* Run mutation tests with the `composer mutation` command.

Contributing
------------

Everyone is welcome to contribute anything, from [ideas and issues][Issues] to [code and documentation][PRs]!

License
-------

Porter is published under the open source GNU Lesser General Public License v3.0. However, the original Porter character and artwork is copyright &copy; 2022 [Bilge](https://github.com/Bilge) and may not be reproduced or modified without express written permission.

Support
-------

Porter is supported by [JetBrains for Open Source][] products.

[![][JetBrains logo]][JetBrains for Open Source]

###### Quick links

[![][Porter icon]][Provider]
[![][Porter transformers icon]][Porter transformers]
[![][Porter connectors icon]][Porter connectors]


  [Releases]: https://github.com/ScriptFUSION/Porter/releases
  [Version image]: https://poser.pugx.org/scriptfusion/porter/version "Latest version"
  [Downloads]: https://packagist.org/packages/scriptfusion/porter
  [Downloads image]: https://poser.pugx.org/scriptfusion/porter/downloads "Total downloads"
  [Build]: https://github.com/ScriptFUSION/Porter/actions/workflows/Tests.yaml
  [Build image]: https://github.com/ScriptFUSION/Porter/actions/workflows/Tests.yaml/badge.svg "Build status"
  [Quickstart build]: https://github.com/ScriptFUSION/Porter/actions/workflows/Quickstart.yaml
  [Quickstart image]: https://github.com/ScriptFUSION/Porter/actions/workflows/Quickstart.yaml/badge.svg "Quick start build status"
  [Quickstart Symfony build]: https://github.com/ScriptFUSION/Porter/actions/workflows/Quickstart%20Symfony.yaml
  [Quickstart Symfony image]: https://github.com/ScriptFUSION/Porter/actions/workflows/Quickstart%20Symfony.yaml/badge.svg "Symfony quick start build status"
  [MSI report]: https://dashboard.stryker-mutator.io/reports/github.com/ScriptFUSION/Porter/master
  [MSI image]: https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FScriptFUSION%2FPorter%2Fmaster "Mutation score"
  [Coverage]: https://codecov.io/gh/ScriptFUSION/Porter
  [Coverage image]: https://codecov.io/gh/ScriptFUSION/Porter/branch/master/graphs/badge.svg "Test coverage"
  
  [Issues]: https://github.com/ScriptFUSION/Porter/issues
  [PRs]: https://github.com/ScriptFUSION/Porter/pulls
  [Quickstart]: https://github.com/ScriptFUSION/Porter/tree/master/docs/Quickstart.md
  [Symfony quickstart]: https://github.com/ScriptFUSION/Porter/tree/master/docs/Quickstart%20Symfony.md 
  [Provider]: https://github.com/provider
  [Porter transformers]: https://github.com/Porter-transformers
  [Porter connectors]: https://github.com/Porter-connectors
  [Stripe provider]: https://github.com/Provider/Stripe
  [ECB provider]: https://github.com/Provider/European-Central-Bank
  [Steam provider]: https://github.com/Provider/Steam
  [HttpConnector]: https://github.com/Porter-connectors/HttpConnector
  [MappingTransformer]: https://github.com/Porter-transformers/MappingTransformer
  [Sub-imports]: https://github.com/Porter-transformers/MappingTransformer#sub-imports
  [Mapper]: https://github.com/ScriptFUSION/Mapper
  [PSR-6]: https://www.php-fig.org/psr/psr-6
  [PSR-11]: https://www.php-fig.org/psr/psr-11
  [PSR-11 search]: https://packagist.org/explore/?type=library&tags=psr-11
  [Porter icon]: https://avatars3.githubusercontent.com/u/16755913?v=3&s=35 "Porter providers"
  [Porter transformers icon]: https://avatars2.githubusercontent.com/u/24607042?v=3&s=35 "Porter transformers"
  [Porter connectors icon]: https://avatars3.githubusercontent.com/u/25672142?v=3&s=35 "Porter connectors"
  [Class diagram]: https://github.com/ScriptFUSION/Porter/blob/master/docs/images/diagrams/Porter%20UML%20class%20diagram%207.0.png?raw=true
  [Data flow diagram]: https://github.com/ScriptFUSION/Porter/blob/master/docs/images/diagrams/Porter%20data%20flow%20diagram%208.0.webp?raw=true
  [CurrencyRecords]: https://github.com/Provider/European-Central-Bank/blob/master/src/Records/CurrencyRecords.php
  [ECB test]: https://github.com/Provider/European-Central-Bank/blob/master/test/DailyForexRatesTest.php
  [Amp]: https://amphp.org
  [Amp documentation]: https://v3.amphp.org/amp
  [Async Throttle]: https://github.com/ScriptFUSION/Async-Throttle
  [JetBrains for Open Source]: https://jb.gg/OpenSource
  [JetBrains logo]: https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.svg "JetBrains logo"
  [Fibers]: https://www.php.net/manual/en/language.fibers.php
  [ext-fiber]: https://github.com/amphp/ext-fiber
