<?php
namespace ScriptFUSION\Porter;

use ScriptFUSION\Porter\Collection\CountableProviderRecords;
use ScriptFUSION\Porter\Collection\FilteredRecords;
use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Mapping\Mapper;
use ScriptFUSION\Porter\Mapping\Mapping;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\ProviderDataType;

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
        $dataType = $specification->finalize()->getProviderDataType();

        if (!$documents = $this->fetch($dataType, $specification->getParameters())) {
            return new CountableProviderRecords(new \EmptyIterator, 0, $dataType);
        }

        if (!$documents instanceof ProviderRecords) {
            // Compose documents iterator.
            $documents = new ProviderRecords($documents, $dataType);
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
        return $this->providers["$name"];
    }

    public function addProvider(Provider $provider)
    {
        $this->providers["{$provider->getName()}"] = $provider;

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

    private function fetch(ProviderDataType $dataType, array $params)
    {
        if ($provider = $this->getProvider($dataType->getName())) {
            return $provider->fetch($dataType, $params);
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
