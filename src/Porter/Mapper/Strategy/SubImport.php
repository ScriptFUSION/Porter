<?php
namespace ScriptFUSION\Porter\Mapper\Strategy;

use ScriptFUSION\Mapper\Strategy\Strategy;
use ScriptFUSION\Porter\PorterAware;
use ScriptFUSION\Porter\PorterAwareTrait;
use ScriptFUSION\Porter\Specification\ImportSpecification;

class SubImport implements Strategy, PorterAware
{
    use PorterAwareTrait;

    private $specificationOrCallback;

    /**
     * Initializes this instance with the specified import specification or
     * the specification callback.
     *
     * @param ImportSpecification|callable $specificationOrCallback Import specification
     *     or specification callback.
     *
     * @throws \InvalidArgumentException Specification is not an ImportSpecification or callable.
     */
    public function __construct($specificationOrCallback)
    {
        if (!$specificationOrCallback instanceof ImportSpecification && !is_callable($specificationOrCallback)) {
            throw new \InvalidArgumentException('Argument one must be an instance of ImportSpecification or callable.');
        }

        $this->specificationOrCallback = $specificationOrCallback;
    }

    public function __invoke($data, $context = null)
    {
        $specification = ImportSpecification::createFrom($this->getOrCreateImportSpecification($data, $context))
            ->setContext($context);

        $generator = $this->getPorter()->import($specification);

        if ($generator->valid()) {
            return iterator_to_array($generator);
        }
    }

    private function getOrCreateImportSpecification($data, $context = null)
    {
        if ($this->specificationOrCallback instanceof ImportSpecification) {
            return $this->specificationOrCallback;
        }

        if (($specification = call_user_func($this->specificationOrCallback, $data, $context))
            instanceof ImportSpecification
        ) {
            return $specification;
        }

        throw new InvalidCallbackResultException('Callback failed to create an instance of ImportSpecification.');
    }
}
