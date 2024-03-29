<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Import;

use ScriptFUSION\Async\Throttle\NullThrottle;
use ScriptFUSION\Async\Throttle\Throttle;
use ScriptFUSION\Porter\Connector\Recoverable\ExponentialAsyncDelayRecoverableExceptionHandler;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Transform\Transformer;

class Import
{
    public const DEFAULT_FETCH_ATTEMPTS = 5;

    private ?string $providerName = null;

    /** @var Transformer[] */
    private array $transformers = [];

    private mixed $context = null;

    private bool $mustCache = false;

    private int $maxFetchAttempts = self::DEFAULT_FETCH_ATTEMPTS;

    private RecoverableExceptionHandler $recoverableExceptionHandler;

    private Throttle $throttle;

    /**
     * Initializes this instance with the specified resource.
     *
     * @param ProviderResource $resource Resource.
     */
    public function __construct(private ProviderResource $resource)
    {
    }

    public function __clone()
    {
        $this->resource = clone $this->resource;

        $transformers = $this->transformers;
        $this->clearTransformers()->addTransformers(array_map(
            static fn ($transformer) => clone $transformer,
            $transformers
        ));

        \is_object($this->context) && $this->context = clone $this->context;

        isset($this->recoverableExceptionHandler) &&
            $this->recoverableExceptionHandler = clone $this->recoverableExceptionHandler;

        // Throttle is not cloned because it most likely wants to be shared between imports.
    }

    /**
     * Gets the resource to import.
     *
     * @return ProviderResource Resource.
     */
    final public function getResource(): ProviderResource
    {
        return $this->resource;
    }

    /**
     * Gets the provider service name.
     *
     * @return string|null Provider name.
     */
    final public function getProviderName(): ?string
    {
        return $this->providerName;
    }

    /**
     * Sets the provider service name.
     *
     * @param string|null $providerName Provider name.
     *
     * @return $this
     */
    final public function setProviderName(?string $providerName): self
    {
        $this->providerName = $providerName;

        return $this;
    }

    /**
     * Gets the ordered list of transformers.
     *
     * @return Transformer[]
     */
    final public function getTransformers(): array
    {
        return $this->transformers;
    }

    /**
     * Adds the specified transformer to the end of the transformers queue.
     *
     * @param Transformer $transformer Transformer.
     *
     * @return $this
     */
    final public function addTransformer(Transformer $transformer): self
    {
        if ($this->hasTransformer($transformer)) {
            throw new DuplicateTransformerException('Transformer already added.');
        }

        $this->transformers[spl_object_id($transformer)] = $transformer;

        return $this;
    }

    /**
     * Adds one or more transformers.
     *
     * @param Transformer[] $transformers Transformers.
     *
     * @return $this
     */
    final public function addTransformers(array $transformers): self
    {
        foreach ($transformers as $transformer) {
            $this->addTransformer($transformer);
        }

        return $this;
    }

    /**
     * Clears the transformers queue, leaving it empty.
     *
     * @return $this
     */
    final public function clearTransformers(): self
    {
        $this->transformers = [];

        return $this;
    }

    private function hasTransformer(Transformer $transformer): bool
    {
        return isset($this->transformers[spl_object_id($transformer)]);
    }

    /**
     * Gets the context to be passed to transformers.
     *
     * @return mixed Context.
     *
     * @deprecated TODO: Evaluate whether context can be removed.
     */
    final public function getContext(): mixed
    {
        return $this->context;
    }

    /**
     * Sets the context to be passed transformers.
     *
     * @param mixed $context Context.
     *
     * @return $this
     *
     * @deprecated TODO: Evaluate whether context can be removed.
     */
    final public function setContext(mixed $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Gets a value indicating whether raw data caching is enabled for this import.
     *
     * @return bool True if caching is enabled, otherwise false.
     */
    final public function mustCache(): bool
    {
        return $this->mustCache;
    }

    /**
     * Enables raw data caching fetched for this import.
     *
     * @return $this
     */
    final public function enableCache(): self
    {
        $this->mustCache = true;

        return $this;
    }

    /**
     * Disables raw data caching.
     *
     * @return $this
     */
    final public function disableCache(): self
    {
        $this->mustCache = false;

        return $this;
    }

    /**
     * Gets the maximum number of fetch attempts per connection.
     *
     * @return int Maximum fetch attempts.
     */
    final public function getMaxFetchAttempts(): int
    {
        return $this->maxFetchAttempts;
    }

    /**
     * Sets the maximum number of fetch attempts per connection before failure is considered permanent.
     *
     * @param int $attempts Maximum fetch attempts.
     *
     * @return $this
     */
    final public function setMaxFetchAttempts(int $attempts): self
    {
        if ($attempts < 1) {
            throw new \InvalidArgumentException('Fetch attempts must be greater than or equal to 1.');
        }

        $this->maxFetchAttempts = $attempts;

        return $this;
    }

    /**
     * Gets the exception handler invoked each time a fetch attempt fails.
     *
     * @return RecoverableExceptionHandler Fetch exception handler.
     */
    final public function getRecoverableExceptionHandler(): RecoverableExceptionHandler
    {
        return $this->recoverableExceptionHandler ??
            $this->recoverableExceptionHandler = new ExponentialAsyncDelayRecoverableExceptionHandler();
    }

    /**
     * Sets the exception handler invoked each time a fetch attempt fails.
     *
     * @param RecoverableExceptionHandler $recoverableExceptionHandler Fetch exception handler.
     *
     * @return $this
     */
    final public function setRecoverableExceptionHandler(RecoverableExceptionHandler $recoverableExceptionHandler): self
    {
        $this->recoverableExceptionHandler = $recoverableExceptionHandler;

        return $this;
    }

    /**
     * Gets the connection throttle, invoked each time a connector fetches data.
     *
     * @return Throttle Connection throttle.
     */
    final public function getThrottle(): Throttle
    {
        return $this->throttle ??= new NullThrottle;
    }

    /**
     * Sets the connection throttle, invoked each time a connector fetches data.
     *
     * @param Throttle $throttle Connection throttle.
     *
     * @return $this
     */
    final public function setThrottle(Throttle $throttle): self
    {
        $this->throttle = $throttle;

        return $this;
    }
}
