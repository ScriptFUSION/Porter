<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter;

use Amp\Iterator;
use Amp\Promise;
use Psr\Container\ContainerInterface;
use ScriptFUSION\Porter\Collection\AsyncPorterRecords;
use ScriptFUSION\Porter\Collection\AsyncProviderRecords;
use ScriptFUSION\Porter\Collection\AsyncRecordCollection;
use ScriptFUSION\Porter\Collection\CountableAsyncPorterRecords;
use ScriptFUSION\Porter\Collection\CountablePorterRecords;
use ScriptFUSION\Porter\Collection\CountableProviderRecords;
use ScriptFUSION\Porter\Collection\PorterRecords;
use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Connector\ImportConnectorFactory;
use ScriptFUSION\Porter\Provider\AsyncProvider;
use ScriptFUSION\Porter\Provider\ObjectNotCreatedException;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\ProviderFactory;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Specification\AsyncImportSpecification;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\Porter\Transform\AsyncTransformer;
use ScriptFUSION\Porter\Transform\Transformer;
use function Amp\call;

/**
 * Imports data from a provider defined in the container of providers or the internal factory.
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
     * Imports one or more records from the resource contained in the specified import specification.
     *
     * @param ImportSpecification $specification Import specification.
     *
     * @return PorterRecords|CountablePorterRecords Collection of records. If the total size of the collection is known,
     *     the collection may implement Countable, otherwise PorterRecords is returned.
     */
    public function import(ImportSpecification $specification): PorterRecords
    {
        $specification = clone $specification;

        $records = $this->fetch($specification);

        if (!$records instanceof ProviderRecords) {
            $records = $this->createProviderRecords($records, $specification->getResource());
        }

        $records = $this->transformRecords($records, $specification->getTransformers(), $specification->getContext());

        return $this->createPorterRecords($records, $specification);
    }

    /**
     * Imports one record from the resource contained in the specified import specification.
     *
     * @param ImportSpecification $specification Import specification.
     *
     * @return array|null Record.
     *
     * @throws ImportException More than one record was imported.
     */
    public function importOne(ImportSpecification $specification): ?array
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

    private function fetch(ImportSpecification $specification): \Iterator
    {
        $resource = $specification->getResource();
        $provider = $this->getProvider($specification->getProviderName() ?? $resource->getProviderClassName());

        if (!$provider instanceof Provider) {
            throw new IncompatibleProviderException('Provider');
        }

        if ($resource->getProviderClassName() !== \get_class($provider)) {
            throw new ForeignResourceException(sprintf(
                'Cannot fetch data from foreign resource: "%s".',
                \get_class($resource)
            ));
        }

        $connector = $provider->getConnector();

        return $resource->fetch(ImportConnectorFactory::create($connector, $specification));
    }

    /**
     * Imports one or more records asynchronously from the resource contained in the specified asynchronous import
     * specification.
     *
     * @param AsyncImportSpecification $specification Asynchronous import specification.
     *
     * @return AsyncPorterRecords|CountableAsyncPorterRecords Collection of records. If the total size of the
     *     collection is known, the collection may implement Countable, otherwise AsyncPorterRecords is returned.
     */
    public function importAsync(AsyncImportSpecification $specification): AsyncRecordCollection
    {
        $specification = clone $specification;

        $records = $this->fetchAsync($specification);

        if (!$records instanceof AsyncProviderRecords) {
            $records = new AsyncProviderRecords($records, $specification->getAsyncResource());
        }

        $records = $this->transformRecordsAsync(
            $records,
            $specification->getTransformers(),
            $specification->getContext()
        );

        return $this->createAsyncPorterRecords($records, $specification);
    }

    /**
     * Imports one record from the resource contained in the specified asynchronous import specification.
     *
     * @param AsyncImportSpecification $specification Asynchronous import specification.
     *
     * @return Promise<array|null> Record.
     *
     * @throws ImportException More than one record was imported.
     */
    public function importOneAsync(AsyncImportSpecification $specification): Promise
    {
        return call(function () use ($specification) {
            $results = $this->importAsync($specification);

            yield $results->advance();

            $one = $results->getCurrent();

            if (yield $results->advance()) {
                throw new ImportException('Cannot import one: more than one record imported.');
            }

            return $one;
        });
    }

    private function fetchAsync(AsyncImportSpecification $specification): Iterator
    {
        $resource = $specification->getAsyncResource();
        $provider = $this->getProvider($specification->getProviderName() ?? $resource->getProviderClassName());

        if (!$provider instanceof AsyncProvider) {
            throw new IncompatibleProviderException('AsyncProvider');
        }

        if ($resource->getProviderClassName() !== \get_class($provider)) {
            throw new ForeignResourceException(sprintf(
                'Cannot fetch data from foreign resource: "%s".',
                \get_class($resource)
            ));
        }

        $connector = $provider->getAsyncConnector();

        return $resource->fetchAsync(ImportConnectorFactory::create($connector, $specification));
    }

    /**
     * @param RecordCollection $records
     * @param Transformer[] $transformers
     * @param mixed $context
     */
    private function transformRecords(RecordCollection $records, array $transformers, $context): RecordCollection
    {
        foreach ($transformers as $transformer) {
            if ($transformer instanceof PorterAware) {
                $transformer->setPorter($this);
            }

            $records = $transformer->transform($records, $context);
        }

        return $records;
    }

    /**
     * @param AsyncRecordCollection $records
     * @param AsyncTransformer[] $transformers
     * @param mixed $context
     */
    private function transformRecordsAsync(
        AsyncRecordCollection $records,
        array $transformers,
        $context
    ): AsyncRecordCollection {
        foreach ($transformers as $transformer) {
            if ($transformer instanceof PorterAware) {
                $transformer->setPorter($this);
            }

            $records = $transformer->transformAsync($records, $context);
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

    private function createPorterRecords(RecordCollection $records, ImportSpecification $specification): PorterRecords
    {
        if ($records instanceof \Countable) {
            return new CountablePorterRecords($records, \count($records), $specification);
        }

        return new PorterRecords($records, $specification);
    }

    private function createAsyncPorterRecords(
        AsyncRecordCollection $records,
        AsyncImportSpecification $specification
    ): AsyncPorterRecords {
        if ($records instanceof \Countable) {
            return new CountableAsyncPorterRecords($records, \count($records), $specification);
        }

        return new AsyncPorterRecords($records, $specification);
    }

    /**
     * Gets the provider matching the specified name.
     *
     * @param string $name Provider name.
     *
     * @return Provider|AsyncProvider Provider.
     *
     * @throws ProviderNotFoundException The specified provider was not found.
     */
    private function getProvider(string $name)
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
        return $this->providerFactory ?: $this->providerFactory = new ProviderFactory;
    }
}
