<?php
namespace ScriptFUSION\Porter;

use ScriptFUSION\Porter\Cache\CacheAdvice;
use ScriptFUSION\Porter\Cache\CacheToggle;
use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Collection\CountablePorterRecords;
use ScriptFUSION\Porter\Collection\CountableProviderRecords;
use ScriptFUSION\Porter\Collection\PorterRecords;
use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Connector\RecoverableConnectorException;
use ScriptFUSION\Porter\Provider\ObjectNotCreatedException;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\ProviderFactory;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\Retry\ExceptionHandler\ExponentialBackoffExceptionHandler;

/**
 * Imports data according to an ImportSpecification.
 */
class Porter
{
    const DEFAULT_FETCH_ATTEMPTS = 5;

    /**
     * @var Provider[]
     */
    private $providers;

    /**
     * @var ProviderFactory
     */
    private $providerFactory;

    /**
     * @var CacheAdvice
     */
    private $defaultCacheAdvice;

    /**
     * @var int
     */
    private $maxFetchAttempts = self::DEFAULT_FETCH_ATTEMPTS;

    /**
     * @var callable
     */
    private $fetchExceptionHandler;

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
     *
     * @throws ImportException Provider failed to return an iterator.
     */
    public function import(ImportSpecification $specification)
    {
        $specification = clone $specification;
        $records = $this->fetch($specification->getResource(), $specification->getCacheAdvice());

        if (!$records instanceof ProviderRecords) {
            $records = $this->createProviderRecords($records, $specification->getResource());
        }

        foreach ($specification->getTransformers() as $transformer) {
            if ($transformer instanceof PorterAware) {
                $transformer->setPorter($this);
            }

            $records = $transformer->transform($records, $specification->getContext());
        }

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

    private function fetch(ProviderResource $resource, CacheAdvice $cacheAdvice = null)
    {
        $provider = $this->getProvider($resource->getProviderClassName(), $resource->getProviderTag());
        $this->applyCacheAdvice($provider, $cacheAdvice ?: $this->defaultCacheAdvice);

        if (($records = \ScriptFUSION\Retry\retry(
            $this->getMaxFetchAttempts(),
            function () use ($provider, $resource) {
                return $provider->fetch($resource);
            },
            function (\Exception $exception) {
                // Throw exception if unrecoverable.
                if (!$exception instanceof RecoverableConnectorException) {
                    throw $exception;
                }

                call_user_func($this->getFetchExceptionHandler(), $exception);
            }
        )) instanceof \Iterator) {
            return $records;
        }

        throw new ImportException(get_class($provider) . '::fetch() did not return an Iterator.');
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
     * Registers the specified provider optionally identified by the specified tag.
     *
     * @param Provider $provider Provider.
     * @param string|null $tag Optional. Provider tag.
     *
     * @return $this
     *
     * @throws ProviderAlreadyRegisteredException The specified provider is already registered.
     */
    public function registerProvider(Provider $provider, $tag = null)
    {
        if ($this->hasProvider($name = get_class($provider), $tag)) {
            throw new ProviderAlreadyRegisteredException("Provider already registered: \"$name\" with tag \"$tag\".");
        }

        $this->providers[$this->hashProviderName($name, $tag)] = $provider;

        return $this;
    }

    /**
     * Gets the provider matching the specified class name and optionally a tag.
     *
     * @param string $name Provider class name.
     * @param string|null $tag Optional. Provider tag.
     *
     * @return Provider
     *
     * @throws ProviderNotFoundException The specified provider was not found.
     */
    public function getProvider($name, $tag = null)
    {
        if ($this->hasProvider($name, $tag)) {
            return $this->providers[$this->hashProviderName($name, $tag)];
        }

        try {
            // Tags are not supported for lazy-loaded providers because every instance would be the same.
            if ($tag === null) {
                $this->registerProvider($provider = $this->getOrCreateProviderFactory()->createProvider("$name"));

                return $provider;
            }
        } catch (ObjectNotCreatedException $exception) {
            // Intentionally empty.
        }

        throw new ProviderNotFoundException(
            "No such provider registered: \"$name\" with tag \"$tag\".",
            isset($exception) ? $exception : null
        );
    }

    /**
     * Gets a value indicating whether the specified provider is registered.
     *
     * @param string $name Provider class name.
     * @param string|null $tag Optional. Provider tag.
     *
     * @return bool True if the specified provider is registered, otherwise false.
     */
    public function hasProvider($name, $tag = null)
    {
        return isset($this->providers[$this->hashProviderName($name, $tag)]);
    }

    /**
     * @param string $name Provider class name.
     * @param string|null $tag Provider tag.
     *
     * @return string Provider identifier hash.
     */
    private function hashProviderName($name, $tag)
    {
        return "$name#$tag";
    }

    private function getOrCreateProviderFactory()
    {
        return $this->providerFactory ?: $this->providerFactory = new ProviderFactory;
    }

    /**
     * Gets the maximum number of fetch attempts per import.
     *
     * @return int
     */
    public function getMaxFetchAttempts()
    {
        return $this->maxFetchAttempts;
    }

    /**
     * Sets the maximum number of fetch attempts per import.
     *
     * @param int $attempts Maximum fetch attempts.
     *
     * @return $this
     */
    public function setMaxFetchAttempts($attempts)
    {
        $this->maxFetchAttempts = max(1, $attempts|0);

        return $this;
    }

    /**
     * @return callable
     */
    private function getFetchExceptionHandler()
    {
        return $this->fetchExceptionHandler ?: $this->fetchExceptionHandler = new ExponentialBackoffExceptionHandler;
    }

    /**
     * @param callable $fetchExceptionHandler
     */
    public function setFetchExceptionHandler(callable $fetchExceptionHandler)
    {
        $this->fetchExceptionHandler = $fetchExceptionHandler;
    }
}
