<?php
namespace ScriptFUSION\Porter\Mapping;

use ScriptFUSION\Porter\Porter;

class Resolver
{
    private $porter;

    public function __construct(Porter $porter)
    {
        $this->porter = $porter;
    }

    public function resolve($strategyOrMapping)
    {

    }

    public function resolveMapping(Mapping $mapping, array $document, $context = null)
    {

    }
}
