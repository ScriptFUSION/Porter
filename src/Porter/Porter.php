<?php
namespace ScriptFUSION\Porter;

use ScriptFUSION\Mapper\Mapping;
use ScriptFUSION\Porter\Collection\FilteredRecords;
use ScriptFUSION\Porter\Collection\PorterRecords;
use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Mapper\PorterMapper;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\ProviderDataType;
use ScriptFUSION\Porter\Provider\ProviderFactory;
use ScriptFUSION\Porter\Specification\ImportSpecification;

class Porter
{
    private $providers;

    private $providerFactory;

    private $mapper;

    /**
     * @param ImportSpecification $specification
     *
     * @return RecordCollection
     */
    public function import(ImportSpecification $specification)
    {
        $providerDataType = $specification->finalize()->getProviderDataType();

        if (!($records = $this->fetch($providerDataType)) instanceof ProviderRecords) {
            // Compose records iterator.
            $records = new ProviderRecords($records, $providerDataType);
        }

        if ($specification->getFilter()) {
            $records = $this->filter($records, $specification->getFilter(), $specification->getContext());
        }

        if ($specification->getMapping()) {
            $records = $this->map($records, $specification->getMapping(), $specification->getContext());
        }

        return new PorterRecords($records, $specification);
    }

    /**
     * @param string $name
     *
     * @return Provider
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

    public function setProviderFactory(ProviderFactory $factory)
    {
        $this->providerFactory = $factory;

        return $this;
    }

    /**
     * @return PorterMapper
     */
    private function getOrCreateMapper()
    {
        return $this->mapper ?: $this->mapper = new PorterMapper($this);
    }

    /**
     * @param PorterMapper $mapper
     *
     * @return $this
     */
    public function setMapper(PorterMapper $mapper)
    {
        $this->mapper = $mapper;

        return $this;
    }

    private function fetch(ProviderDataType $providerDataType)
    {
        if ($provider = $this->getProvider($providerDataType->getProviderName())) {
            return $provider->fetch($providerDataType);
        }
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
        return $this->getOrCreateMapper()->mapRecords($records, $mapping, $context);
    }
}
