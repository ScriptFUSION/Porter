<?php
namespace ScriptFUSION\Porter;

use ScriptFUSION\Porter\Collection\CountableProviderRecords;
use ScriptFUSION\Porter\Collection\FilteredRecords;
use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Mapping\Mapper;
use ScriptFUSION\Porter\Mapping\Mapping;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\ProviderData;

class Porter
{
    private $providers;

    private $mapper;

    /**
     * @param ImportSpecification $specification
     *
     * @return RecordCollection
     */
    public function import(ImportSpecification $specification)
    {
        $providerData = $specification->finalize()->getProviderData();

        if (!$documents = $this->fetch($providerData)) {
            return new CountableProviderRecords(new \EmptyIterator, 0, $providerData);
        }

        if (!$documents instanceof ProviderRecords) {
            // Compose documents iterator.
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

        throw new ProviderNotFoundException("No such provider registered: \"$name\".");
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

    private function filter(ProviderRecords $documents, callable $predicate, $context)
    {
        return new FilteredRecords($documents, $documents);
    }

    private function map(RecordCollection $documents, Mapping $mapping, $context)
    {
        return $this->getOrCreateMapper()->map($documents, $mapping, $context, $this);
    }
}
