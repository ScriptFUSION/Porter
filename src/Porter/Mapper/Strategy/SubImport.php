<?php
namespace ScriptFUSION\Porter\Mapper\Strategy;

use ScriptFUSION\Mapper\Strategy\Strategy;
use ScriptFUSION\Porter\InvalidCallbackException;
use ScriptFUSION\Porter\PorterAware;
use ScriptFUSION\Porter\PorterAwareTrait;
use ScriptFUSION\Porter\Specification\ImportSpecification;

class SubImport implements Strategy, PorterAware
{
    use PorterAwareTrait;

    private $specification;

    /**
     * Initializes this instance with the specified import specification or
     * the specification callback.
     *
     * @param ImportSpecification|callable $specification Import specification
     *     or specification callback.
     *
     * @throws \InvalidArgumentException Specification is not an ImportSpecification or callable.
     */
    public function __construct($specification)
    {
        if (!$specification instanceof ImportSpecification && !is_callable($specification)) {
            throw new \InvalidArgumentException('Argument one must be an instance of ImportSpecification or callable.');
        }

        $this->specification = $specification;
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
        if ($this->specification instanceof ImportSpecification) {
            return $this->specification;
        }

        if (($specification = call_user_func($this->specification, $data, $context)) instanceof ImportSpecification) {
            return $specification;
        }

        throw new InvalidCallbackException('Callback failed to create an instance of ImportSpecification.');
    }
}
