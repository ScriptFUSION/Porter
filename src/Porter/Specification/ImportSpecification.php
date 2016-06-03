<?php
namespace ScriptFUSION\Porter\Specification;

use ScriptFUSION\Mapper\Mapping;
use ScriptFUSION\Porter\ObjectFinalizedException;
use ScriptFUSION\Porter\Provider\ProviderDataType;

class ImportSpecification
{
    private $finalized = false;

    /** @var ProviderDataType */
    private $providerDataType;

    /** @var Mapping */
    private $mapping;

    /** @var mixed */
    private $context;

    /** @var callable */
    private $filter;

    public function __construct(ProviderDataType $providerDataType)
    {
        $this->providerDataType = $providerDataType;
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

        return (new static($specification->getProviderDataType()))
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
     * @return ProviderDataType
     */
    final public function getProviderDataType()
    {
        return $this->isFinalized() ? clone $this->providerDataType : $this->providerDataType;
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
}
