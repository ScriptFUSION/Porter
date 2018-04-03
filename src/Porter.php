<?php
namespace ScriptFUSION\Porter;

use Psr\Container\ContainerInterface;
use ScriptFUSION\Porter\Collection\CountablePorterRecords;
use ScriptFUSION\Porter\Collection\CountableProviderRecords;
use ScriptFUSION\Porter\Collection\PorterRecords;
use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Connector\ConnectionContext;
use ScriptFUSION\Porter\Connector\ConnectionContextFactory;
use ScriptFUSION\Porter\Connector\ConnectorOptions;
use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSION\Porter\Provider\ForeignResourceException;
use ScriptFUSION\Porter\Provider\ObjectNotCreatedException;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\ProviderFactory;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\Porter\Transform\Transformer;

/**
 * Imports data from a provider defined in the providers container or internal factory.
 */
class Porter
{
    /**
     * @var ContainerInterface Container of user-defined providers.
     */
    private $providers;

    /**
     * @var ProviderFactory Internal factory of first-party providers.
     */
    private $providerFactory;

    /**
     * Initializes this instance with the specified container of providers.
     *
     * @param ContainerInterface $providers Container of providers.
     */
    public function __construct(ContainerInterface $providers)
    {
        $this->providers = $providers;
    }

    /**
     * Imports data according to the design of the specified import specification.
     *
     * @param ImportSpecification $specification Import specification.
     *
     * @return PorterRecords|CountablePorterRecords
     *
     * @throws ImportException Provider failed to return an iterator.
     */
    public function import(ImportSpecification $specification)
    {
        $specification = clone $specification;

        $records = $this->fetch(
            $specification->getResource(),
            $specification->getProviderName(),
            ConnectionContextFactory::create($specification)
        );

        if (!$records instanceof ProviderRecords) {
            $records = $this->createProviderRecords($records, $specification->getResource());
        }

        $records = $this->transformRecords($records, $specification->getTransformers(), $specification->getContext());

        return $this->createPorterRecords($records, $specification);
    }

    /**
     * Imports one record according to the design of the specified import specification.
     *
     * @param ImportSpecification $specification Import specification.
     *
     * @return array|null Record.
     *
     * @throws ImportException More than one record was imported.
     */
    public function importOne(ImportSpecification $specification)
    {
        $results = $this->import($specification);

        if (!$results->valid()) {
            return null;
        }

        $one = $results->current();

        if ($results->next() || $results->valid()) {
            throw new ImportException('Cannot import one: more than one record imported.');
        }

        return $one;
    }

    private function fetch(ProviderResource $resource, $providerName, ConnectionContext $context)
    {
        $provider = $this->getProvider($providerName ?: $resource->getProviderClassName());

        if ($resource->getProviderClassName() !== get_class($provider)) {
            throw new ForeignResourceException(sprintf(
                'Cannot fetch data from foreign resource: "%s".',
                get_class($resource)
            ));
        }

        $connector = $provider->getConnector();

        /* __clone method cannot be specified in interface due to Mockery limitation.
           See https://github.com/mockery/mockery/issues/669 */
        if ($connector instanceof ConnectorOptions && !method_exists($connector, '__clone')) {
            throw new \LogicException(
                'Connector with options must implement __clone() method to deep clone options.'
            );
        }

        $records = $resource->fetch(new ImportConnector(clone $connector, $context));

        if (!$records instanceof \Iterator) {
            throw new ImportException(get_class($resource) . '::fetch() did not return an Iterator.');
        }

        return $records;
    }

    /**
     * @param RecordCollection $records
     * @param Transformer[] $transformers
     * @param mixed $context
     *
     * @return RecordCollection
     */
    private function transformRecords(RecordCollection $records, array $transformers, $context)
    {
        foreach ($transformers as $transformer) {
            if ($transformer instanceof PorterAware) {
                $transformer->setPorter($this);
            }

            $records = $transformer->transform($records, $context);
        }

        return $records;
    }

    private function createProviderRecords(\Iterator $records, ProviderResource $resource)
    {
        if ($records instanceof \Countable) {
            return new CountableProviderRecords($records, count($records), $resource);
        }

        return new ProviderRecords($records, $resource);
    }

    private function createPorterRecords(RecordCollection $records, ImportSpecification $specification)
    {
        if ($records instanceof \Countable) {
            return new CountablePorterRecords($records, count($records), $specification);
        }

        return new PorterRecords($records, $specification);
    }

    /**
     * Gets the provider matching the specified name.
     *
     * @param string $name Provider name.
     *
     * @return Provider
     *
     * @throws ProviderNotFoundException The specified provider was not found.
     */
    private function getProvider($name)
    {
        if ($this->providers->has($name)) {
            return $this->providers->get($name);
        }

        try {
            return $this->getOrCreateProviderFactory()->createProvider("$name");
        } catch (ObjectNotCreatedException $exception) {
            throw new ProviderNotFoundException("No such provider registered: \"$name\".", $exception);
        }
    }

    private function getOrCreateProviderFactory()
    {
        return $this->providerFactory ?: $this->providerFactory = new ProviderFactory;
    }
}
