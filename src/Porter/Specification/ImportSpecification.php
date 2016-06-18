<?php
namespace ScriptFUSION\Porter\Specification;

use ScriptFUSION\Mapper\Mapping;
use ScriptFUSION\Porter\Cache\CacheAdvice;
use ScriptFUSION\Porter\ObjectFinalizedException;
use ScriptFUSION\Porter\Provider\ProviderDataFetcher;

class ImportSpecification
{
    private $finalized = false;

    /** @var ProviderDataFetcher */
    private $dataFetcher;

    /** @var Mapping */
    private $mapping;

    /** @var mixed */
    private $context;

    /** @var callable */
    private $filter;

    /** @var CacheAdvice */
    private $cacheAdvice;

    public function __construct(ProviderDataFetcher $dataFetcher)
    {
        $this->dataFetcher = $dataFetcher;
    }

    /**
     * @param ImportSpecification $specification
     *
     * @return static
     */
    public static function createFrom(ImportSpecification $specification)
    {
        // Finalize specification to avoid sharing objects.
        if (!$specification->isFinalized()) {
            $specification = clone $specification;
            $specification->finalize();
        }

        return (new static($specification->getDataFetcher()))
            ->setMapping($specification->getMapping())
            ->setContext($specification->getContext())
            ->setFilter($specification->getFilter());
    }

    /**
     * Finalizes this specification so no more changes can be written to it.
     *
     * @return $this
     */
    final public function finalize()
    {
        $this->finalized = true;

        return $this;
    }

    /**
     * @return bool
     */
    final public function isFinalized()
    {
        return $this->finalized;
    }

    private function failIfFinalized($function)
    {
        if ($this->isFinalized()) {
            throw new ObjectFinalizedException("Cannot \"$function\": specification is final.");
        }
    }

    /**
     * @return ProviderDataFetcher
     */
    final public function getDataFetcher()
    {
        return $this->isFinalized() ? clone $this->dataFetcher : $this->dataFetcher;
    }

    /**
     * @return Mapping
     */
    final public function getMapping()
    {
        return $this->isFinalized() && $this->mapping ? clone $this->mapping : $this->mapping;
    }

    /**
     * @param Mapping $mapping
     *
     * @return $this
     */
    final public function setMapping(Mapping $mapping = null)
    {
        $this->failIfFinalized(__FUNCTION__);

        $this->mapping = $mapping;

        return $this;
    }

    /**
     * @return mixed
     */
    final public function getContext()
    {
        return $this->isFinalized() && is_object($this->context) ? clone $this->context : $this->context;
    }

    /**
     * @param mixed $context
     *
     * @return $this
     */
    final public function setContext($context)
    {
        $this->failIfFinalized(__FUNCTION__);

        $this->context = $context;

        return $this;
    }

    /**
     * @return callable
     */
    final public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param callable $filter
     *
     * @return $this
     */
    final public function setFilter(callable $filter = null)
    {
        $this->failIfFinalized(__FUNCTION__);

        $this->filter = $filter;

        return $this;
    }

    /**
     * @return CacheAdvice
     */
    public function getCacheAdvice()
    {
        return $this->cacheAdvice;
    }

    /**
     * @param CacheAdvice $cacheAdvice
     *
     * @return $this
     */
    public function setCacheAdvice(CacheAdvice $cacheAdvice)
    {
        $this->failIfFinalized(__FUNCTION__);

        $this->cacheAdvice = $cacheAdvice;

        return $this;
    }
}
