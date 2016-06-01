<?php
namespace ScriptFUSION\Porter\Mapper;

use ScriptFUSION\Mapper\Mapping;

class ProviderDataMapping extends Mapping
{
    private $mapping;

    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;

        parent::__construct();
    }

    protected function createMap()
    {
        return $this->mapping;
    }
}
