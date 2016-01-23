<?php
namespace ScriptFUSION\Porter;

use ScriptFUSION\Porter\Collection\FilteredRecords;
use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Mapping\Mapper;
use ScriptFUSION\Porter\Mapping\Mapping;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\ProviderData;
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
        $providerData = $specification->finalize()->getProviderData();

        if (!($documents = $this->fetch($providerData)) instanceof ProviderRecords) {
            // Compose records iterator.
            $documents = new ProviderRecords($documents, $providerData);
        }

        if ($specification->getFilter()) {
            $documents = $this->filter($documents, $specification->getFilter(), $specification->getContext());
        }

        if ($specification->getMapping()) {
            $documents = $this->map($documents, $specification->getMapping(), $specification->getContext());
        }

        return $documents;
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
     * @return Mapper
     */
    private function getOrCreateMapper()
    {
        return $this->mapper ?: $this->mapper = new Mapper($this);
    }

    /**
     * @param Mapper $mapper
     *
     * @return $this
     */
    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;

        return $this;
    }

    private function fetch(ProviderData $dataType)
    {
        if ($provider = $this->getProvider($dataType->getProviderName())) {
            return $provider->fetch($dataType);
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

    private function map(RecordCollection $documents, Mapping $mapping, $context)
    {
        return $this->getOrCreateMapper()->map($documents, $mapping, $context, $this);
    }
}
