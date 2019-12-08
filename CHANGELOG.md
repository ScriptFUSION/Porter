# Porter change log

## 5.0.0 – Async

Porter v5 introduces asynchronous imports and complete strict type safety (excluding union types and generics).

### Breaking changes

* Removed support for PHP 5.5, 5.6 and 7.0.
* Every interface has been updated to include return types which means all consuming projects must also add the same return type.
* Replaced `Connector::fetch` string source parameter with new `DataSource` interface.
* Removed `ConnectionContext` from `Connector` interface.
* Added `SingleRecordResource` interface that resources must implement to be used with `Porter::importOne()`.
* Prevented single record resources being imported with multi-record import methods.
* Replaced `RecoverableConnectorException` with `RecoverableException` interface.
* Removed failed abstractions: `ConnectorOptions` and `EncapsulatedOptions`.
* Removed abstraction: `CacheKeyGenerator`.
* Moved `ForeignResourceException` to Porter's namespace.

## 4.0.0 – Rewrite

Porter v4 fixes all known design flaws (#31, #43) and critically re-evaluates every part of Porter's design. All base classes have been discarded (`AbstractProvider`, `AbstractResource`), moving their code within Porter, relying solely on interfaces instead. This frees up the inheritance chain for applications to use as they wish, making it much easier to integrate Porter into existing projects.

The new design is much simpler, removing the redundant `fetch()` method from `Provider` and removing the redundant and confusing `EncapsulatedOptions` parameters from all `fetch()` methods. There is no longer any need to figure out how to merge different sets of options coming from different parts of the application because there is only one source of truth for connector options now, and they live within the connector itself, because it has a 1:1 relationship with its options.

Porter v4 is super slim; we no longer bundle any unnecessary dependencies such as connectors you don't need. `connectors/http` has also dropped URL building support and all the associated dependencies because it is not the job of the connector to build URLs; do this in providers if needed, by whatever mechanism best suits its needs.

In development, on and off, for a little over a year, I sincerely hope you find this new version of Porter useful and easier to use than ever before.

### Breaking changes

* Removed `AbstractResource`. (#35)
* Changed Porter to no longer act as a repository of providers directly. Porter now requires a PSR-11 `ContainerInterface` which must contain the providers. (#38)
* Porter is no longer bundled with any connectors. `connectors/http` and `connectors/soap` must be required manually if needed. (#39)
* Changed `Connector` to receive `ConnectionContext` as its first parameter. Context includes a `retry()` method to provide preconfigured, immutable durability features to the connector. (#42)
* `Connector` implementations no longer have to extend `CachingConnector` to provide caching facilities: all connectors can be decorated with `CachingConnector` with no prior knowledge of the existence of such facility. This completely removes the burden on implementations to be aware of caching concerns. (#44)
* Removed `AbstractProvider`. (#41)
* Removed `EncapsulatedOptions` parameter from `Connector::fetch()` method. (#48)
* Changed fetch exception handler from `callable` to `FetchExceptionHandler` to fix #43. (#50)
* Forced `RecordCollections` to return `arrays`. Previously, the documentation claimed collections were iterators of arrays but the software did not enforce this; now it does. (#52)
