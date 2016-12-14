Porter <img src="https://github.com/ScriptFUSION/Porter/wiki/images/porter%20222x.png" align="right">
======

[![Latest version][Version image]][Releases]
[![Total downloads][Downloads image]][Downloads]
[![Build status][Build image]][Build]
[![Test coverage][Coverage image]][Coverage]
[![Code style][Style image]][Style]

Porter is a data import abstraction library to import any data from anywhere. To achieve this she must be able to generalize about the structure of data. Porter believes all data sets are either a single record, repeating collection of records with consistent structure, or both, where *record* is either a list or tree of name and value pairs.

Porter must be able to abstract data importing requirements so that she can import any data format, similar to how a database must be able to abstract data storage requirements such that it can store any type of data. But unlike a database, Porter is only interested in data import, not storage. To facilitate this, Porter's interfaces use arrays, also known as *records*, and array iterators, also known as [*record collections*](#record-collections). Arrays allow us to store any data type and iterators allow us to iterate over an unlimited number of records, thus allowing Porter to stream any data format of any size.

The [Provider organization][Provider] hosts projects using Porter to provide useful data. These repositories are ready-to-use Porter providers granting access to popular third-party APIs and data services. Check it out before writing a new provider to see if it has already been written. Anyone writing new providers is encouraged to contribute them to the organization.

Contents
--------

  1. [Audience](#audience)
  1. [Usage](#usage)
  1. [Import specifications](#import-specifications)
  1. [Record collections](#record-collections)
  1. [Durability](#durability)
  1. [Filtering](#filtering)
  1. [Mapping](#mapping)
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

Audience
--------

Porter is useful for anyone importing data into PHP applications. Data typically comes from third-party APIs, but it could come from any source, including web scraping or even first-party APIs, using Porter to consume our own data services. Porter is simply a uniform way to abstract the task of importing data with the following benefits.

 * Provides a [framework](#architecture) for structuring data import concepts, such a [providers](#providers) offering data via one or more [resources](#resources).
 * Offers useful post-import data augmentation operations such as [filtering](#filtering) and [mapping](#mapping).
 * Protects against intermittent network failure with [durability](#durability) features.
 * Supports raw data [caching](#caching), at the connector level, for each import.
 * Joins many data sets together using [sub-imports](#sub-imports).

### How to start using Porter

If we already have a provider that's ready to use, start reading from [usage](#usage) and continue until [architecture](#architecture). If we do not have a provider yet we will need to write one.

We typically start by writing the [provider](#providers) that defines how to connect to a data service using one of the supplied [connectors](#list-of-connectors). We then define one or more [resources](#resources) that fetch data using the connector and yield it as a [record collection](#record-collections). For more information, see [writing a provider](#writing-a-provider) and [writing a resource](#writing-a-resource).

Usage
-----

Porter's `import` method accepts an `ImportSpecification` that describes which data should be imported and how the data should be transformed. To import `MyResource` we might write the following.

```php
$records = $porter->import(new ImportSpecification(new MyResource));
```

Provider resources, such as `MyResource`, specify the `Provider` class name they work with. Imports will only work when a resource's provider has been added to Porter, otherwise `ProviderNotFoundException` is thrown. To find which provider `MyResource` requires we examine its `getProviderClassName` method, which returns `MyProvider::class`, in this case. In the following example we register `MyProvider` with Porter.

```php
$porter = (new Porter)->registerProvider(new MyProvider);
```

Calling `import()` returns an instance of `PorterRecords`, which implements `Iterator`, allowing us to enumerate each record in the collection using `foreach` as in the following example.

```php
foreach ($records as $record) {
    // Insert breakpoint or var_dump() here to examine each $record.
}
```

Import specifications
---------------------

Import specifications specify *what* to import, and optionally, *how* it should be transformed thereafter and whether to use caching. The only mandatory parameter, passed to the constructor, is a `ProviderResource` that specifies the data we want to import.

Options may be configured by the setters listed below.

 - `setFilter(callable)` &ndash; Specifies a predicate that may remove records; see [filtering](#filtering) for more.
 - `setMapping(Mapping)` &ndash; Specifies a mapping to transform each record; see [mapping](#mapping) for more.
 - `setContext(mixed)` &ndash; Specifies user-defined data to be passed to Mapper and filters.
 - `setCacheAdvice(CacheAdvice)` &ndash; Specifies a caching strategy; see [caching](#caching) for more.

The order of operations is fixed and occur in the following order.

 1. Fetch records from `ProviderResource`.
 2. Filtering.
 3. Mapping.

Since the order is fixed, it is not currently possible to exclude records based on data that only exists after mapping.

Record collections
------------------

Record collections are a type of `Iterator` whose values are always arrays. The result of a successful `Porter::import` call is an instance of `PorterRecords` or one of its specialisations, which implement `Iterator`, guaranteeing the collection is enumerable using `foreach`.

Record collections are composed by Porter using the decorator pattern. If provider data is not modified, `PorterRecords` will decorate the `ProviderRecords` returned from a `ProviderResource`. That is, `PorterRecords` has a pointer back to the previous collection, which could be written as: `PorterRecords` → `ProviderRecords`. If a mapping was applied, the collection stack would be `PorterRecords` → `MappedRecords` → `ProviderRecords`. In general this is an unimportant detail for most users but it can be useful for debugging. The stack of record collection types informs us of the transformations a collection has undergone and each type holds a pointer to relevant objects that participated in the transformation, for example, `PorterRecords` holds a reference to the `ImportSpecification` that was used to create it and can be accessed using `PorterRecords::getSpecification`.

Record collections may be `Countable`, depending on whether the imported data was countable and whether any destructive operations were performed after import. Filtering is a destructive operation since it may remove records and therefore the count reported by a `ProviderResource` would no longer be accurate. It is the responsibility of the resource to supply the number of records in its collection by returning an iterator that implements `Countable`, such as `ArrayIterator` or `CountableProviderRecords`. When a countable iterator is detected, Porter returns `CountablePorterRecords` as long as no destructive operations were performed, which is possible because all non-destructive operation's collection types have a countable analogue.

Durability
----------

Porter automatically retries connections when an exception occurs during `Connector::fetch`. This helps mitigate intermittent network conditions that can cause data fetches to fail temporarily. The number of retry attempts can be configured by calling `Porter::setMaxFetchAttempts`.

The default exception handler, `ExponentialBackoffExceptionHandler`, causes the program to pause for an exponentially increasing series of delays. Given that the default number of retry attempts is *five*, the exception handler may be called up to *four* times, delaying each retry attempt for ~0.1, ~0.2, ~0.4, and finally, ~0.8 seconds.

The exception handler can be changed by calling `Porter::setFetchExceptionHandler`. For example, the following code changes the initial retry delay to one second.

```php
$porter->setFetchExceptionHandler(new ExponentialBackoffExceptionHandler(1000000));
```

Durability only applies when connectors throw a recoverable exception type. If an unexpected exception occurs the fetch attempt will be aborted. For more information, see [implementing connector durability](#durability-1). Exception handlers receive the exception type as their first argument. An exception handler can inspect the recoverable exception and throw its own exception if it decides the exception should be treated as fatal.

Filtering
---------

Filtering provides a way to remove some records. For each record, if the specified predicate function returns `false` or a falsy value, the record will be removed, otherwise the record will be kept. The predicate receives the current record as an array as its first parameter and context as its second parameter.

In general we would like to avoid filtering because it is inefficient to import data and then immediately remove some of it, but some immature APIs do not provide a way to reduce the data set on the server, so filtering on the client is the only alternative. Filtering also invalidates the record count reported by some resources, meaning we no longer know how many records are in the collection before iteration.

#### Example

The following example filters out any records that do not have an *id* field present.

```php
$records = $porter->import(
    (new ImportSpecification(new MyResource))
        ->setFilter(function (array $record) {
            return isset($record['id']);
        })
);
```

Mapping
-------

Porter integrates [Mapper][Mapper] to support data transformations using `Mapping` objects. A full discussion of Mapper is beyond the scope of this document but the linked repository contains comprehensive documentation. Porter builds on Mapper by providing a powerful mapping strategy called `SubImport`.

### Sub-imports

Porter's `SubImport` strategy provides a way to join data sets together. A mapping may contain any number of sub-imports, each of which may receive a different `ImportSpecification`. A sub-import causes Porter to begin a new import operation and thus supports all import options without limitation, including importing from different providers and applying a separate mapping to each sub-import.

#### Signature

```php
SubImport(ImportSpecification|callable $specificationOrCallback)
```

 1. `$specificationOrCallback` &ndash; Either an `ImportSpecification` instance or `callable` that returns such an instance.

#### ImportSpecification Example

The following example imports `MyImportSpecification` and copies the *foo* field from the input data into the output mapping. Next it performs a sub-import using `MyDetailsSpecification` and stores the result in the *details* key of the output mapping.

```php
$records = $porter->import(
    (new MyImportSpecification)
        ->setMapping(new AnonymousMapping([
            'foo' => new Copy('foo'),
            'details' => new SubImport(MyDetailsSpecification),
        ]))
);
```

#### Callback example

The following example is the same as the previous except `MyDetailsSpecification` now requires an identifier that is copied from *details_id* present in the input data. This is only possible using a callback since we cannot inject strategies inside specifications.

```php
$records = $porter->import(
    (new MyImportSpecification)
        ->setMapping(new AnonymousMapping([
            'foo' => new Copy('foo'),
            'details' => new SubImport(
                function (array $record) {
                    return new MyDetailsSpecification($record['details_id']);
                }
            ),
        ]))
);
```

Caching
-------

Caching is available at the connector level if the connector implements `CacheToggle`. Connectors typically extend `CachingConnector` which implements [PSR-6][PSR-6]-compatible caching. Porter ships with just one cache implementation, `MemoryCache`, which stores data in memory but this can be substituted for any PSR-6 cache if the connector permits it.

When available, the connector caches raw responses for each unique cache key. The cache key is comprised of the source and options parameters passed to `Connector::fetch`. Options are sorted before the cache key is created so the order of options are insignificant.

Caching behaviour is specified by one of the `CacheAdvice` enumeration constants listed below.

* `SHOULD_CACHE` &ndash; Response should be cached if a cache is available.
* `SHOULD_NOT_CACHE` &ndash; Response should not be cached even if a cache is available.
* `MUST_CACHE` &ndash; Response must be cached otherwise an exception may be thrown.
* `MUST_NOT_CACHE` &ndash; Response must not be cached otherwise an exception may be thrown.

The default cache advice is `SHOULD_NOT_CACHE`, meaning connectors supporting caching will not cache responses and connectors not supporting caching will not throw any exceptions.

#### Example

The follow example enables connector-level response caching, if available.

```php
$records = $porter->import(
    (new ImportSpecification(new MyResource))
        ->setCacheAdvice(CacheAdvice:SHOULD_CACHE())
);
```

Architecture
------------

Porter talks to *providers* to fetch data. Providers represent one or more *resources* from which data can be fetched. Providers pass a *connector* needed by their resources to fetch data. Resources define the provider they are compatible with and receive the provider's connector when fetching data. Resources must transform their data into one or more *records*, collectively known as *record collections*, which present data sets as an enumeration of array values.

The following UML class diagram shows a partial architectural overview illustrating Porter's main components. Note that [Mapper][Mapper] is a separate project with optional integration into Porter but is included for completeness.

[![Class diagram][Class diagram]][Class diagram]

Providers
---------

Providers fetch data from their `ProviderResource` objects by supplying them with a valid `Connector`. A provider implements `Provider` that defines one method with the following signature.

```php
public function fetch(ProviderResource $resource) : Iterator;
```

When `fetch()` is called it is passed the resource from which data must be fetched. The provider must supply the resource with its connector which it typically does by calling `$resource->fetch($connector)`.

A provider knows whether a given resource belongs to it by calling `ProviderResource::getProviderClassName()` and checking for equality, but a provider does not know how many resources it has nor maintains a list of such resources and neither does any other part of the application. That is, a resource class can be created at any time and claim to belong to a given provider without any formal registration, and the provider must accept all such objects.

### Writing a provider

Note: before writing a provider be sure to check out the [Provider organization][Provider] to see if it has already been written!

Providers must implement the `Provider` interface, however it is common to extend `AbstractProvider` instead. The abstract class provides a `fetch()` implementation, forwards options, stores a connector and proxies cache methods for the connector. A typical `AbstractProvider` implementation only needs to override the constructor with a specialized type hint for the connector it requires.

Providers may also store common state applicable to their resources, such as authentication data, that is passed to a resource's second `fetch()` parameter when the provider's `fetch()` method is called. The recommended way to pass state to resources is calling `AbstractProvider::setOptions()` in the provider's constructor, which causes the options to be forwarded automatically during `fetch()`.

#### Implementation example

In the following example we create a provider that only accepts `HttpConnector` instances. We also create a default connector in case one is not supplied. Note it is not always possible to create a default connector and it is perfectly valid to insist the caller supplies a connector.

```php
final class MyProvider extends AbstractProvider
{
    public function __construct(HttpConnector $connector = null)
    {
        parent::__construct($connector ?: new HttpConnector);
    }
}
```

Resources
---------

Resources fetch data using the supplied connector and format it as a collection of arrays. A resource implements `ProviderResource` that defines the following three methods.

```php
public function getProviderClassName() : string;
public function getProviderTag() : string;
public function fetch(Connector $connector, EncapsulatedOptions $options = null) : Iterator;
```

A resource supplies the class name of the provider it expects a connector from when `getProviderClassName()` is called. A used-defined tag can be specified to identify a particular Provider instance when `getProviderTag()` is called.

When `fetch()` is called it is passed the connector from which data must be fetched. The resource must ensure data is formatted as an iterator of array values whilst remaining as true to the original format as possible; that is, we must avoid renaming or restructuring data because it is the caller's prerogative to perform data customization if desired.

Providers may also supply options to `fetch()`. Such options are typically used to convey API keys or other options common to all of a provider's resources. When specified, a resource must ensure the options are transmitted to the connector.

### Writing a resource

Resources must implement the `ProviderResource` interface, however it is common to extend `AbstractResource` instead because it provides a working implementation for provider tagging. A typical `AbstractResource` implementation implements `getProviderClassName()` with a hard-coded provider class name and a valid `fetch()` implementation.

It is important to understand `fetch()` must always return an iterator of array values. Suppose we want to return the numeric series one to three. The following implementation would be invalid because it returns an iterator of integer values instead of an iterator of array values.

```php
public function fetch(Connector $connector)
{
    return new ArrayIterator(range(1, 3)); // Invalid return type.
}
```

Either of the following examples would be valid `fetch()` implementations.

```php
public function fetch(Connector $connector)
{
    foreach (range(1, 3) as $number) {
        yield [$number];
    }
}
```

Since the total number of records is known, the iterator can be wrapped in `CountableProviderRecords` to enrich the caller with this information.

```php
public function fetch(Connector $connector)
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
    const URL = 'https://example.com';

    public function getProviderClassName()
    {
        return MyProvider::class;
    }

    public function fetch(Connector $connector)
    {
        $data = $connector->fetch(self::URL);

        yield json_decode($data, true);
    }
}
```

Connectors
----------

Connectors fetch remote data from the specified source. Connectors for some popular protocols are included but it may be necessary to write a new connector when dealing with unsupported protocols.

### List of connectors

The following connectors are provided with Porter.

 - `HttpConnector` &ndash; Fetches data from an HTTP server via the PHP wrapper.
 - `SoapConnector` &ndash; Fetches data from a SOAP service.

### Writing a connector

A connector implements the `Connector` interface that defines one method with the following signature.

```php
public function fetch(string $source, EncapsulatedOptions $options = null);
```

When `fetch()` is called the connector fetches data from the specified source whilst applying any options specified. If a connector accepts options it must define its own options class and ensure that type is passed. Connectors may return data in any format that's convenient for resources to consume, but in general, such data should be as raw as possible and without modification.

#### Durability

To support Porter's durability features a connector may throw `RecoverableConnectorException` to signal that the fetch operation can be retried. Execution will halt as normal if any other exception type is thrown. It is recommended to always throw a recoverable exception type unless it is certain that any number of subsequent attempts will always fail.

To promote an ordinary exception to a recoverable exception, wrap the fetch code in a try-catch block and pass the original exception into `RecoverableConnectorException` as its inner exception, as shown in the following example.

```php
try {
    $response = $client->fetch();
} catch (Exception $e) {
    throw new RecoverableConnectorException($e->getMessage(), $e->getCode(), $e);
}
```

When dealing with clients that do not throw exceptions, or when writing low-level socket code, it is recommended to throw custom exceptions that extend `RecoverableConnectorException`.

Requirements
------------

 - [PHP 5.5](http://php.net/)
 - [Composer](https://getcomposer.org/)

Limitations
-----------

 - Filtering always occurs before mapping.
 - Imports must complete synchronously. That is, calls to `import()` are blocking.
 - Sub-imports must complete synchronously. That is, the previous sub-import must finish before the next starts.

Testing
-------

Porter is almost fully unit tested. Run the tests with `bin/test` from a shell.

Contributing
------------

Everyone is welcome to contribute anything, from [ideas and issues][Issues] to [documentation and code][PRs]! For inspiration, consider the listen of open [issues][Issues].

License
-------

Porter is published under the open source GNU Lesser General Public License v3.0. However, the original Porter character and artwork is copyright &copy; 2016 [Bilge](https://github.com/Bilge) and may not be reproduced or modified without express written permission.

[![][Porter icon]][Provider]


  [Releases]: https://github.com/ScriptFUSION/Porter/releases
  [Version image]: https://poser.pugx.org/scriptfusion/porter/version "Latest version"
  [Downloads]: https://packagist.org/packages/scriptfusion/porter
  [Downloads image]: https://poser.pugx.org/scriptfusion/porter/downloads "Total downloads"
  [Build]: http://travis-ci.org/ScriptFUSION/Porter
  [Build image]: https://travis-ci.org/ScriptFUSION/Porter.svg?branch=master "Build status"
  [Coverage]: https://coveralls.io/github/ScriptFUSION/Porter
  [Coverage image]: https://coveralls.io/repos/ScriptFUSION/Porter/badge.svg "Test coverage"
  [Style]: https://styleci.io/repos/49824895
  [Style image]: https://styleci.io/repos/49824895/shield?style=flat "Code style"
  
  [Issues]: https://github.com/ScriptFUSION/Porter/issues
  [PRs]: https://github.com/ScriptFUSION/Porter/pulls
  [Provider]: https://github.com/provider
  [Mapper]: https://github.com/ScriptFUSION/Mapper
  [PSR-6]: http://www.php-fig.org/psr/psr-6
  [Porter icon]: https://github.com/ScriptFUSION/Porter/wiki/images/porter%20head%2032x.png
  [Class diagram]: https://github.com/ScriptFUSION/Porter/wiki/images/diagrams/Porter%20UML%20class%20diagram%201.0.png
