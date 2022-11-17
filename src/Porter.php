<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter;

use Psr\Container\ContainerInterface;
use ScriptFUSION\Porter\Collection\CountablePorterRecords;
use ScriptFUSION\Porter\Collection\CountableProviderRecords;
use ScriptFUSION\Porter\Collection\PorterRecords;
use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Connector\ImportConnectorFactory;
use ScriptFUSION\Porter\Provider\ObjectNotCreatedException;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\ProviderFactory;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Provider\Resource\SingleRecordResource;
use ScriptFUSION\Porter\Import\Import;
use ScriptFUSION\Porter\Transform\Transformer;

/**
 * Imports data from a provider defined in the container of providers or the internal factory.
 */
final class Porter
{
    /**
     * @var ProviderFactory Internal factory of first-party providers.
     */
    private ProviderFactory $providerFactory;

    /**
     * Initializes this instance with the specified container of providers.
     *
     * @param ContainerInterface $providers Container of providers.
     */
    public function __construct(private readonly ContainerInterface $providers)
    {
    }

    /**
     * Imports one or more records from the resource contained in the specified import specification.
     *
     * @param Import $import Import specification.
     *
     * @return PorterRecords|CountablePorterRecords Collection of records. If the total size of the collection is known,
     *     the collection may implement Countable, otherwise PorterRecords is returned.
     *
     * @throws IncompatibleResourceException Resource emits a single record and must be imported with
     *     importOne() instead.
     */
    public function import(Import $import): PorterRecords|CountablePorterRecords
    {
        if ($import->getResource() instanceof SingleRecordResource) {
            throw IncompatibleResourceException::createMustNotImplementInterface();
        }

        return $this->fetch($import);
    }

    /**
     * Imports one record from the resource contained in the specified import specification.
     *
     * @param Import $import Import specification.
     *
     * @return array|null Record.
     *
     * @throws IncompatibleResourceException Resource does not implement required interface.
     * @throws ImportException More than one record was imported.
     */
    public function importOne(Import $import): ?array
    {
        if (!$import->getResource() instanceof SingleRecordResource) {
            throw IncompatibleResourceException::createMustImplementInterface();
        }

        $results = $this->fetch($import);

        if (!$results->valid()) {
            return null;
        }

        $one = $results->current();

        if ($results->next() || $results->valid()) {
            throw new ImportException('Cannot import one: more than one record imported.');
        }

        return $one;
    }

    private function fetch(Import $import): PorterRecords
    {
        $import = clone $import;
        $resource = $import->getResource();
        $provider = $this->getProvider($import->getProviderName() ?? $resource->getProviderClassName());

        if ($resource->getProviderClassName() !== \get_class($provider)) {
            throw new ForeignResourceException(sprintf(
                'Cannot fetch data from foreign resource: "%s".',
                \get_class($resource)
            ));
        }

        $records = $resource->fetch(
            ImportConnectorFactory::create($provider, $provider->getConnector(), $import)
        );

        if (!$records instanceof ProviderRecords) {
            $records = $this->createProviderRecords($records, $import->getResource());
        }

        $records = $this->transformRecords($records, $import->getTransformers(), $import->getContext());

        return $this->createPorterRecords($records, $import);
    }

    /**
     * @param Transformer[] $transformers
     */
    private function transformRecords(RecordCollection $records, array $transformers, mixed $context): RecordCollection
    {
        foreach ($transformers as $transformer) {
            if ($transformer instanceof PorterAware) {
                $transformer->setPorter($this);
            }

            $records = $transformer->transform($records, $context);
        }

        return $records;
    }

    private function createProviderRecords(\Iterator $records, ProviderResource $resource): ProviderRecords
    {
        if ($records instanceof \Countable) {
            return new CountableProviderRecords($records, \count($records), $resource);
        }

        return new ProviderRecords($records, $resource);
    }

    private function createPorterRecords(RecordCollection $records, Import $import): PorterRecords
    {
        if ($records instanceof \Countable) {
            return new CountablePorterRecords($records, \count($records), $import);
        }

        return new PorterRecords($records, $import);
    }

    /**
     * Gets the provider matching the specified name.
     *
     * @param string $name Provider name.
     *
     * @return Provider Provider.
     *
     * @throws ProviderNotFoundException The specified provider was not found.
     */
    private function getProvider(string $name): Provider
    {
        if ($this->providers->has($name)) {
            return $this->providers->get($name);
        }

        try {
            return $this->getOrCreateProviderFactory()->createProvider($name);
        } catch (ObjectNotCreatedException $exception) {
            throw new ProviderNotFoundException("No such provider registered: \"$name\".", $exception);
        }
    }

    private function getOrCreateProviderFactory(): ProviderFactory
    {
        return $this->providerFactory ??= new ProviderFactory;
    }
}
