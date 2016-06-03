<?php
namespace ScriptFUSION\Porter\Mapper\Strategy;

use ScriptFUSION\Mapper\Strategy\Strategy;
use ScriptFUSION\Porter\PorterAware;
use ScriptFUSION\Porter\PorterAwareTrait;
use ScriptFUSION\Porter\Specification\ImportSpecification;

class SubImport implements Strategy, PorterAware
{
    use PorterAwareTrait;

    private $specification;

    private $specificationCallbacks = [];

    /**
     * Initializes this instance with the specified import specification or
     * the specified specification callback or both.
     *
     * @param ImportSpecification|null $specification Import specification.
     * @param callable|null $specificationCallback Specification callback.
     *
     * @throws \LogicException Import specification and specification callback
     *     are both null.
     */
    public function __construct(ImportSpecification $specification = null, callable $specificationCallback = null)
    {
        if (!$specification && !$specificationCallback) {
            // TODO: Proper exception type.
            throw new \LogicException('Import specification and specification callback cannot both be null.');
        }

        $this->specification = $specification;
        $specificationCallback && $this->addSpecificationCallback($specificationCallback);
    }

    public function __invoke($data, $context = null)
    {
        foreach ($this->specificationCallbacks as $callback) {
            if (($specification = $callback($data, $context, $this->specification)) instanceof ImportSpecification) {
                $this->specification = $specification;
            }
        }

        if (!$this->specification) {
            // TODO: Proper exception type.
            throw new \RuntimeException('Specification callbacks failed to create an instance of ImportSpecification.');
        }

        $specification = ImportSpecification::createFrom($this->specification)->setContext($context);

        $generator = $this->getPorter()->import($specification);

        if ($generator->valid()) {
            return iterator_to_array($generator);
        }
    }

    public function addSpecificationCallback(callable $callback)
    {
        $this->specificationCallbacks[] = $callback;

        return $this;
    }
}
