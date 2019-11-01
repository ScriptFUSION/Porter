<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Specification;

use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;
use ScriptFUSION\Porter\Transform\AnysyncTransformer;
use ScriptFUSION\Porter\Transform\Transformer;

abstract class Specification
{
    public const DEFAULT_FETCH_ATTEMPTS = 5;

    /**
     * @var string|null
     */
    private $providerName;

    /**
     * @var AnysyncTransformer[]
     */
    private $transformers;

    /**
     * @var mixed
     */
    private $context;

    /**
     * @var bool
     */
    private $mustCache = false;

    /**
     * @var int
     */
    private $maxFetchAttempts = self::DEFAULT_FETCH_ATTEMPTS;

    /**
     * @var RecoverableExceptionHandler
     */
    private $recoverableExceptionHandler;

    public function __construct()
    {
        $this->clearTransformers();
    }

    public function __clone()
    {
        $transformers = $this->transformers;
        $this->clearTransformers()->addTransformers(array_map(
            static function (AnysyncTransformer $transformer): AnysyncTransformer {
                return clone $transformer;
            },
            $transformers
        ));

        \is_object($this->context) && $this->context = clone $this->context;
        $this->recoverableExceptionHandler &&
        $this->recoverableExceptionHandler = clone $this->recoverableExceptionHandler;
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
     * @return AnysyncTransformer[]
     */
    final public function getTransformers(): array
    {
        return $this->transformers;
    }

    /**
     * Adds the specified transformer.
     *
     * @param AnysyncTransformer $transformer Transformer.
     *
     * @return $this
     */
    protected function addTransformer(AnysyncTransformer $transformer): self
    {
        if ($this->hasTransformer($transformer)) {
            throw new DuplicateTransformerException('Transformer already added.');
        }

        $this->transformers[spl_object_hash($transformer)] = $transformer;

        return $this;
    }

    /**
     * Adds one or more transformers.
     *
     * @param AnysyncTransformer[] $transformers Transformers.
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

    private function hasTransformer(AnysyncTransformer $transformer): bool
    {
        return isset($this->transformers[spl_object_hash($transformer)]);
    }

    /**
     * Gets the context to be passed to transformers.
     *
     * @return mixed Context.
     *
     * @deprecated TODO: Evaluate whether context can be removed.
     */
    final public function getContext()
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
    final public function setContext($context): self
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
        return $this->recoverableExceptionHandler ?:
            $this->recoverableExceptionHandler = static::createDefaultRecoverableExceptionHandler();
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

    abstract protected static function createDefaultRecoverableExceptionHandler(): RecoverableExceptionHandler;
}
