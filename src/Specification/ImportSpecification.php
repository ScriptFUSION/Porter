<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Specification;

use ScriptFUSION\Porter\Connector\Recoverable\ExponentialSleepRecoverableExceptionHandler;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Transform\Transformer;

/**
 * Specifies which resource to import and how the data should be transformed.
 */
class ImportSpecification
{
    public const DEFAULT_FETCH_ATTEMPTS = 5;

    /**
     * @var ProviderResource
     */
    private $resource;

    /**
     * @var string|null
     */
    private $providerName;

    /**
     * @var Transformer[]
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

    public function __construct(ProviderResource $resource)
    {
        $this->resource = $resource;

        $this->clearTransformers();
    }

    public function __clone()
    {
        $this->resource = clone $this->resource;

        $transformers = $this->transformers;
        $this->clearTransformers()->addTransformers(array_map(
            static function (Transformer $transformer): Transformer {
                return clone $transformer;
            },
            $transformers
        ));

        \is_object($this->context) && $this->context = clone $this->context;
        $this->recoverableExceptionHandler &&
            $this->recoverableExceptionHandler = clone $this->recoverableExceptionHandler;
    }

    /**
     * @return ProviderResource
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
     * Adds the specified transformer.
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

        $this->transformers[spl_object_hash($transformer)] = $transformer;

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
     * @return $this
     */
    final public function clearTransformers(): self
    {
        $this->transformers = [];

        return $this;
    }

    private function hasTransformer(Transformer $transformer): bool
    {
        return isset($this->transformers[spl_object_hash($transformer)]);
    }

    /**
     * @return mixed
     */
    final public function getContext()
    {
        return $this->context;
    }

    /**
     * @param mixed $context
     *
     * @return $this
     */
    final public function setContext($context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return bool
     */
    final public function mustCache(): bool
    {
        return $this->mustCache;
    }

    /**
     * @return $this
     */
    final public function enableCache(): self
    {
        $this->mustCache = true;

        return $this;
    }

    /**
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
            $this->recoverableExceptionHandler = new ExponentialSleepRecoverableExceptionHandler;
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
}
