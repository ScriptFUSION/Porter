<?php
namespace ScriptFUSION\Porter;

use Psr\Container\ContainerInterface;
use ScriptFUSION\Porter\Cache\CacheAdvice;
use ScriptFUSION\Porter\Cache\CacheToggle;
use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Collection\CountablePorterRecords;
use ScriptFUSION\Porter\Collection\CountableProviderRecords;
use ScriptFUSION\Porter\Collection\PorterRecords;
use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\RecoverableConnectorException;
use ScriptFUSION\Porter\Provider\ForeignResourceException;
use ScriptFUSION\Porter\Provider\ObjectNotCreatedException;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\ProviderFactory;
use ScriptFUSION\Porter\Provider\ProviderOptions;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\Porter\Transform\Transformer;

/**
 * Imports data according to an ImportSpecification.
 */
class Porter
{
    /**
     * @var ContainerInterface
     */
    private $providers;

    /**
     * @var ProviderFactory
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
     * @return PorterRecords
     *
     * @throws ImportException Provider failed to return an iterator.
     */
    public function import(ImportSpecification $specification)
    {
        $specification = clone $specification;

        $records = $this->fetch(
            $specification->getResource(),
            $specification->getProviderName(),
            $specification->getCacheAdvice(),
            $specification->getMaxFetchAttempts(),
            $specification->getFetchExceptionHandler()
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

    private function fetch(
        ProviderResource $resource,
        $providerName,
        CacheAdvice $cacheAdvice,
        $fetchAttempts,
        $fetchExceptionHandler
    ) {
        $provider = $this->getProvider($providerName ?: $resource->getProviderClassName());

        if ($resource->getProviderClassName() !== get_class($provider)) {
            throw new ForeignResourceException(sprintf(
                'Cannot fetch data from foreign resource: "%s".',
                get_class($resource)
            ));
        }

        $this->applyCacheAdvice($provider->getConnector(), $cacheAdvice);

        if (($records = \ScriptFUSION\Retry\retry(
            $fetchAttempts,
            function () use ($provider, $resource) {
                $records = $resource->fetch(
                    $provider->getConnector(),
                    $provider instanceof ProviderOptions
                        ? clone $provider->getOptions()
                        : null
                );

                if ($records instanceof \Iterator) {
                    // Force generator to run until first yield to provoke an exception.
                    $records->valid();
                }

                return $records;
            },
            function (\Exception $exception) use ($fetchExceptionHandler) {
                // Throw exception if unrecoverable.
                if (!$exception instanceof RecoverableConnectorException) {
                    throw $exception;
                }

                $fetchExceptionHandler($exception);
            }
        )) instanceof \Iterator) {
            return $records;
        }

        throw new ImportException(get_class($provider) . '::fetch() did not return an Iterator.');
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

    private function applyCacheAdvice(Connector $connector, CacheAdvice $cacheAdvice)
    {
        try {
            if (!$connector instanceof CacheToggle) {
                throw CacheUnavailableException::modify();
            }

            switch ("$cacheAdvice") {
                case CacheAdvice::MUST_CACHE:
                case CacheAdvice::SHOULD_CACHE:
                    $connector->enableCache();
                    break;

                case CacheAdvice::MUST_NOT_CACHE:
                case CacheAdvice::SHOULD_NOT_CACHE:
                    $connector->disableCache();
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
