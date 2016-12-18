<?php
namespace ScriptFUSION\Porter\Specification;

use ScriptFUSION\Porter\Cache\CacheAdvice;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Transform\Transformer;

/**
 * Specifies which resource to import and how the data should be transformed.
 */
class ImportSpecification
{
    /**
     * @var ProviderResource
     */
    private $resource;

    /**
     * @var Transformer[]
     */
    private $transformers;

    /**
     * @var mixed
     */
    private $context;

    /**
     * @var CacheAdvice
     */
    private $cacheAdvice;

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
            function (Transformer $transformer) {
                return clone $transformer;
            },
            $transformers
        ));

        is_object($this->context) && $this->context = clone $this->context;
    }

    /**
     * @return ProviderResource
     */
    final public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return Transformer[]
     */
    final public function getTransformers()
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
    final public function addTransformer(Transformer $transformer)
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
    final public function addTransformers(array $transformers)
    {
        foreach ($transformers as $transformer) {
            $this->addTransformer($transformer);
        }

        return $this;
    }

    /**
     * @return $this
     */
    final public function clearTransformers()
    {
        $this->transformers = [];

        return $this;
    }

    private function hasTransformer(Transformer $transformer)
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
    final public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return CacheAdvice
     */
    final public function getCacheAdvice()
    {
        return $this->cacheAdvice;
    }

    /**
     * @param CacheAdvice $cacheAdvice
     *
     * @return $this
     */
    final public function setCacheAdvice(CacheAdvice $cacheAdvice)
    {
        $this->cacheAdvice = $cacheAdvice;

        return $this;
    }
}
