<?php
namespace ScriptFUSION\Porter;

use ScriptFUSION\Mapper\CollectionMapper;
use ScriptFUSION\Mapper\Mapping;
use ScriptFUSION\Porter\Cache\CacheAdvice;
use ScriptFUSION\Porter\Cache\CacheToggle;
use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Collection\CountableMappedRecords;
use ScriptFUSION\Porter\Collection\CountablePorterRecords;
use ScriptFUSION\Porter\Collection\FilteredRecords;
use ScriptFUSION\Porter\Collection\MappedRecords;
use ScriptFUSION\Porter\Collection\PorterRecords;
use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Mapper\PorterMapper;
use ScriptFUSION\Porter\Provider\DataSource\ProviderDataSource;
use ScriptFUSION\Porter\Provider\ObjectNotCreatedException;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\ProviderFactory;
use ScriptFUSION\Porter\Specification\ImportSpecification;

class Porter
{
    private $providers;

    private $providerFactory;

    private $mapper;

    private $defaultCacheAdvice;

    public function __construct()
    {
        $this->defaultCacheAdvice = CacheAdvice::SHOULD_NOT_CACHE();
    }

    /**
     * Imports data according to the design of the specified import specification.
     *
     * @param ImportSpecification $specification Import specification.
     *
     * @return PorterRecords
     */
    public function import(ImportSpecification $specification)
    {
        $records = $this->fetch($specification->getDataSource(), $specification->getCacheAdvice());

        if (!$records instanceof ProviderRecords) {
            // Compose records iterator.
            $records = new ProviderRecords($records, $specification->getDataSource());
        }

        if ($specification->getFilter()) {
            $records = $this->filter($records, $specification->getFilter(), $specification->getContext());
        }

        if ($specification->getMapping()) {
            $records = $this->map($records, $specification->getMapping(), $specification->getContext());
        }

        return $this->createPorterRecords($records, clone $specification);
    }

    private function createPorterRecords(RecordCollection $records, ImportSpecification $specification)
    {
        if ($records instanceof \Countable) {
            return new CountablePorterRecords($records, count($records), $specification);
        }

        return new PorterRecords($records, $specification);
    }

    private function fetch(ProviderDataSource $dataSource, CacheAdvice $cacheAdvice = null)
    {
        $provider = $this->getProvider($dataSource->getProviderClassName());
        $this->applyCacheAdvice($provider, $cacheAdvice ?: $this->defaultCacheAdvice);

        return $provider->fetch($dataSource);
    }

    private function filter(ProviderRecords $records, callable $predicate, $context)
    {
        $filter = function () use ($records, $predicate, $context) {
            foreach ($records as $record) {
                if ($predicate($record, $context)) {
                    yield $record;
                }
            }
        };

        return new FilteredRecords($filter(), $records);
    }

    private function map(RecordCollection $records, Mapping $mapping, $context)
    {
        return $this->createMappedRecords(
            $this->getOrCreateMapper()->mapCollection($records, $mapping, $context),
            $records
        );
    }

    private function createMappedRecords(\Iterator $records, RecordCollection $previous)
    {
        if ($previous instanceof \Countable) {
            return new CountableMappedRecords($records, count($previous), $previous);
        }

        return new MappedRecords($records, $previous);
    }

    private function applyCacheAdvice(Provider $provider, CacheAdvice $cacheAdvice)
    {
        try {
            if (!$provider instanceof CacheToggle) {
                throw CacheUnavailableException::modify();
            }

            switch ("$cacheAdvice") {
                case CacheAdvice::MUST_CACHE:
                case CacheAdvice::SHOULD_CACHE:
                    $provider->enableCache();
                    break;

                case CacheAdvice::MUST_NOT_CACHE:
                case CacheAdvice::SHOULD_NOT_CACHE:
                    $provider->disableCache();
            }
        } catch (CacheUnavailableException $exception) {
            if ($cacheAdvice === CacheAdvice::MUST_NOT_CACHE() ||
                $cacheAdvice === CacheAdvice::MUST_CACHE()
            ) {
                throw $exception;
            }
        }
    }

    /**
     * @param string $name
     *
     * @return Provider
     *
     * @throws ProviderNotFoundException The requested provider was not found.
     */
    public function getProvider($name)
    {
        if (isset($this->providers["$name"])) {
            return $this->providers["$name"];
        }

        try {
            $this->addProvider($provider = $this->getOrCreateProviderFactory()->createProvider("$name"));
        } catch (ObjectNotCreatedException $e) {
            throw new ProviderNotFoundException("No such provider registered: \"$name\".", $e);
        }

        return $provider;
    }

    public function addProvider(Provider $provider)
    {
        $this->providers[get_class($provider)] = $provider;

        return $this;
    }

    public function addProviders(array $providers)
    {
        foreach ($providers as $provider) {
            $this->addProvider($provider);
        }

        return $this;
    }

    private function getOrCreateProviderFactory()
    {
        return $this->providerFactory ?: $this->providerFactory = new ProviderFactory;
    }

    /**
     * @return CollectionMapper
     */
    private function getOrCreateMapper()
    {
        return $this->mapper ?: $this->mapper = new PorterMapper($this);
    }

    /**
     * @param CollectionMapper $mapper
     *
     * @return $this
     */
    public function setMapper(CollectionMapper $mapper)
    {
        $this->mapper = $mapper;

        return $this;
    }
}
